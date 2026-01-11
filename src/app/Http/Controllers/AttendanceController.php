<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

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

        $status = $attendance?->attendanceStatus?->name ?? '勤務外';

        return view('attendance.store',compact('attendance','status'));
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
    public function attendanceIndex()
    {
        $attendances=Attendance::with('attendanceBreaks')
            ->where('user_id',auth()->id())
            ->orderBy('date','desc')
            ->get();

        foreach($attendances as $attendance){
            $totalSeconds=0;

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
        }

        return view('attendance.index',compact('attendances'));
    }
}
