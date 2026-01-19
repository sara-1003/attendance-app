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

        foreach ($attendances as $attendance) {

        // 休憩時間合計
        $totalBreakSeconds = 0;

        foreach ($attendance->attendanceBreaks as $break) {
            if ($break->break_start && $break->break_end) {
                $start = Carbon::parse($break->break_start);
                $end = Carbon::parse($break->break_end);
                $totalBreakSeconds += $start->diffInSeconds($end);
            }
        }

        // 休憩時間
        $h = floor($totalBreakSeconds / 3600);
        $m = floor(($totalBreakSeconds % 3600) / 60);
        $attendance->total_break_time = sprintf('%d:%02d', $h, $m);

        // 勤務時間
        if ($attendance->clock_in && $attendance->clock_out) {
            $workStart = Carbon::parse($attendance->clock_in);
            $workEnd = Carbon::parse($attendance->clock_out);

            $workSeconds = $workStart->diffInSeconds($workEnd) - $totalBreakSeconds;

            if ($workSeconds < 0) {
                $workSeconds = 0;
            }

            $wh = floor($workSeconds / 3600);
            $wm = floor(($workSeconds % 3600) / 60);

            $attendance->work_time = sprintf('%d:%02d', $wh, $wm);
        } else {
            $attendance->work_time = '';
        }
    }

        return view('admin.index', compact('attendances', 'displayDate','prevDate','nextDate'));
    }

    // 勤怠詳細画面の表示
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'attendanceBreaks'])->findOrFail($id);

        $request = $attendance->attendanceRequests()->latest()->first();

        return view('attendance.show', compact('attendance', 'request'));
    }

    // スタッフ一覧画面の表示
    public function staffIndex()
    {
        $staffs=User::where('role','user')->get();

        return view('admin.staff_index',compact('staffs'));
    }

    // スタッフ別勤怠一覧画面の表示
    public function staffAttendance(Request $request, $id)
    {
        $displayMonth = $request->input('month')
        ? Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()
        : Carbon::now()->startOfMonth();

        $prevMonth = $displayMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $displayMonth->copy()->addMonth()->format('Y-m');

        $staff = User::findOrFail($id);

        $attendances = Attendance::where('user_id', $id)
        ->whereBetween('date', [
        $displayMonth->copy()->startOfMonth(),
        $displayMonth->copy()->endOfMonth(),
        ])
        ->orderBy('date', 'asc')
        ->get();

    // 勤務時間・休憩時間計算
        foreach ($attendances as $attendance) {

        $totalBreakSeconds = 0;

        foreach ($attendance->attendanceBreaks as $break) {
            if ($break->break_start && $break->break_end) {
                $start = Carbon::parse($break->break_start);
                $end = Carbon::parse($break->break_end);
                $totalBreakSeconds += $start->diffInSeconds($end);
            }
        }

        $h = floor($totalBreakSeconds / 3600);
        $m = floor(($totalBreakSeconds % 3600) / 60);
        $attendance->total_break_time = sprintf('%d:%02d', $h, $m);

        if ($attendance->clock_in && $attendance->clock_out) {
            $workSeconds =
                Carbon::parse($attendance->clock_in)
                    ->diffInSeconds(Carbon::parse($attendance->clock_out))
                - $totalBreakSeconds;

            if ($workSeconds < 0) $workSeconds = 0;

            $wh = floor($workSeconds / 3600);
            $wm = floor(($workSeconds % 3600) / 60);

            $attendance->work_time = sprintf('%d:%02d', $wh, $wm);
        } else {
            $attendance->work_time = '';
        }
    }
    return view('admin.staff', compact(
    'staff',
    'attendances',
    'displayMonth',
    'prevMonth',
    'nextMonth'
    ));
    }
}
