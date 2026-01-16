<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function login(AdminLoginRequest $request, LoginResponse $response)
    {
        $request->authenticate();
        $request->session()->regenerate();

        return $response->toResponse($request);
    }

    // 勤怠一覧画面の表示
    public function index(Request $request)
    {
        $displayDate = $request->input('date')
        ? Carbon::parse($request->input('date'))
        : Carbon::today();

        $prevDate = $displayDate->copy()->subDay()->toDateString();
        $nextDate = $displayDate->copy()->addDay()->toDateString();

        $attendances = Attendance::with('attendanceBreaks', 'user')
            ->whereDate('date', $displayDate)
            ->orderBy('user_id', 'asc')
            ->get();

        // 休憩時間を計算
        foreach ($attendances as $attendance) {$totalSeconds = 0;

        foreach ($attendance->attendanceBreaks as $break) {
            if ($break->break_start && $break->break_end) {
                $start = Carbon::parse($break->break_start);
                $end = Carbon::parse($break->break_end);

                $totalSeconds += $start->diffInSeconds($end);
            }
        }

        $h = floor($totalSeconds / 3600);
        $m = floor(($totalSeconds % 3600) / 60);

        $attendance->total_break_time = sprintf('%d:%02d', $h, $m);
        }

        return view('admin.index', compact('attendances', 'displayDate','prevDate','nextDate'));
    }
}
