<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;

class RequestController extends Controller
{
    // 申請一覧画面の表示
    public function list(Request $request)
    {
        $status=$request->query('status');

        $query=AttendanceRequest::with(['attendance.user','approvalHistories']);

    // 一般ユーザーなら自分の分だけ
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        if($status === 'pending'){
            $query->whereDoesntHave('approvalHistories');
        }elseif($status === 'approved'){
            $query->whereHas('approvalHistories');
        }

        $requests = $query->latest()->get();

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

        return view('request.approve',compact('request','attendance'));
    }
}
