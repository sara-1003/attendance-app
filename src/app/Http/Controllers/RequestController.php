<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\ApprovalHistory;
use Illuminate\Support\Facades\DB;


class RequestController extends Controller
{
    // 申請一覧画面の表示
    public function list(Request $request)
    {
        $status=$request->query('status','pending');

        $query=AttendanceRequest::with(['attendance.user','approvalHistories']);

    // 一般ユーザーなら自分の分だけ
        if (auth()->user()->role !== 'admin') {
            $query->whereHas('attendance', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        if($status === 'pending'){
            $query->whereDoesntHave('approvalHistories');
        }elseif($status === 'approved'){
            $query->whereHas('approvalHistories');
        }

        $requests = $query
            ->join('attendances', 'attendance_requests.attendance_id', '=', 'attendances.id')
            ->orderBy('attendances.date', 'asc')
            ->select('attendance_requests.*')
            ->get();

        return view('request.list',compact('requests','status'));
    }

    // 修正申請承認画面の表示
    public function approval($attendance_correct_request_id)
    {
        $request=AttendanceRequest::with([
            'attendance.user',
            'attendance.attendanceBreaks',
            'attendanceRequestBreaks',
        ])->findOrFail($attendance_correct_request_id);

        $attendance=$request->attendance;

        $approved=ApprovalHistory::where('attendance_request_id',$request->id)->exists();

        return view('request.approve',compact('request','attendance','approved'));
    }

    // 承認ボタンの実装
    public function approve($attendance_request_id)
    {
        DB::transaction(function () use ($attendance_request_id) {

        // 修正申請を取得（休憩も）
        $attendanceRequest = AttendanceRequest::with('attendanceRequestBreaks')
            ->findOrFail($attendance_request_id);

        $attendance = Attendance::findOrFail($attendanceRequest->attendance_id);

        // 出退勤を更新
        $attendance->update([
            'clock_in'  => $attendanceRequest->new_clock_in,
            'clock_out' => $attendanceRequest->new_clock_out,
        ]);

        // 既存の休憩を削除
        $attendance->attendanceBreaks()->delete();

        // 申請された休憩を登録
        foreach ($attendanceRequest->attendanceRequestBreaks as $break) {
            if (!empty($break->break_start) || !empty($break->break_end)) {
                $attendance->attendanceBreaks()->create([
                    'break_start' => $break->break_start,
                    'break_end'   => $break->break_end,
                ]);
            }
        }

        ApprovalHistory::create([
            'attendance_request_id'=>$attendanceRequest->id,
            'admin_user_id'=>auth()->id(),
        ]);
    });

    return redirect()->route('request.approval', $attendance_request_id);

    }
}