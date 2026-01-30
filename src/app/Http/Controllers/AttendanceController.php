<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;
use App\Http\Requests\ShowRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $user=Auth::user();

        // 現在の勤怠
        $attendance=Attendance::with('attendanceStatus')
            ->where('user_id',$user->id)
            ->where('date',now()->toDateString())
            ->first();

        return view('attendance.store',compact('attendance'));
    }

    // 出勤
    public function start()
    {
        $user=Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        if ($attendance) {
            return back()->with('error', 'すでに今日の出勤があります');
        }

        $statusId = AttendanceStatus::where('name','出勤中')->first()->id;

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'status_id' => $statusId,
        ]);

        return redirect()->route('attendance.store');
    }

    // 休憩入
    public function break()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance || ($attendance->attendanceStatus?->name ?? '') != '出勤中') {
            return back()->with('error','出勤中でないため休憩に入れません');
        }

        $break = AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        // ステータス変更
        $attendance->update([
            'status_id' => AttendanceStatus::where('name','休憩中')->first()->id
        ]);

        return redirect()->route('attendance.store');
    }

    // 休憩戻
    public function resume()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance || ($attendance->attendanceStatus?->name ?? '') != '休憩中') {
            return back()->with('error','休憩中ではありません');
        }

        // 最新の休憩を取得して終了時間をセット
        $break = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        $break->update(['break_end' => now()]);

        // ステータス戻す
        $attendance->update([
            'status_id' => AttendanceStatus::where('name','出勤中')->first()->id
        ]);

        return redirect()->route('attendance.store');
    }

    // 退勤
    public function end()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance || ($attendance->attendanceStatus?->name ?? '') != '出勤中') {
            return back()->with('error','出勤中でないため退勤できません');
        }

        $attendance->update([
            'clock_out' => now(),
            'status_id' => AttendanceStatus::where('name','退勤済')->first()->id
        ]);

        return redirect()->route('attendance.store')->with('message','お疲れ様でした。');
    }

    // 勤怠一覧画面の表示
    public function attendanceIndex(Request $request)
    {
        $userId=auth()->id();

        // 月の取得
        $month=$request->input('month');

        if ($month) {
            $displayMonth = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        } else {
            $displayMonth = Carbon::today()->startOfMonth();
        }

        $attendances=Attendance::with('attendanceBreaks')
            ->where('user_id',$userId)
            ->whereYear('date', $displayMonth->year)
            ->whereMonth('date', $displayMonth->month)
            ->orderBy('date','asc')
            ->get();

        foreach($attendances as $attendance){
            $totalSeconds=0;

            // 休憩合計
            foreach($attendance->attendanceBreaks as $break){
                if($break->break_start && $break->break_end){
                    $start=Carbon::parse($break->break_start);
                    $end=Carbon::parse($break->break_end);

                    $totalSeconds += $start->diffInSeconds($end);
                }
            }

            $h=floor($totalSeconds/3600);
            $m=floor(($totalSeconds%3600)/60);
            $attendance->total_break_time=sprintf('%d:%02d',$h,$m);

            // 勤怠合計
            if($attendance->clock_in && $attendance->clock_out){

                $workStart=Carbon::parse($attendance->clock_in);
                $workEnd=Carbon::parse($attendance->clock_out);

                $workSeconds=$workStart->diffInSeconds($workEnd)-$totalSeconds;

                if($workSeconds<0){
                    $workSeconds=0;
                }

                $wh=floor($workSeconds/3600);
                $wm=floor(($workSeconds%3600)/60);
                $attendance->work_time=sprintf('%d:%02d', $wh, $wm);


            }else{
                $attendance->work_time='0:00';
            }
        }
        $prevMonth = $displayMonth->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $displayMonth->copy()->addMonthNoOverflow()->format('Y-m');

        return view('attendance.index',compact('attendances','displayMonth', 'prevMonth', 'nextMonth'));
    }

    // 勤怠詳細画面の表示
    public function show($id)
    {
        $attendance=Attendance::with(['user','attendanceBreaks'])->findOrFail($id);

        $request=AttendanceRequest::where('attendance_id',$attendance->id)
            ->with('attendanceRequestBreaks')
            ->latest()
            ->first();

        return view('attendance.show',compact('attendance','request'));
    }

    // 修正ボタンの実装
    public function store(ShowRequest $request,Attendance $attendance)
    {
        $user=Auth::user();

        $attendanceRequest=AttendanceRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'new_clock_in'=>$request->clock_in,
            'new_clock_out'=>$request->clock_out,
            'reason'=>$request->reason,
        ]);

        if($request->has('breaks')){
            foreach($request->breaks as $break){
                if (!empty($break['break_start']) || !empty($break['break_end'])) {
                    AttendanceRequestBreak::create([
                        'attendance_request_id' => $attendanceRequest->id,
                        'break_start' => $break['break_start'] ?? null,
                        'break_end' => $break['break_end'] ?? null,
                    ]);
                }
            }
        }
        return redirect()
        ->route('attendance.show', $attendance->id);
    }
}
