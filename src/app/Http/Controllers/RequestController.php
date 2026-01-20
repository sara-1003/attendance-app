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
}
