<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Responses\LoginResponse;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/admin/login', function () {
    return view('admin.login');
})->middleware('guest');
// 管理者ログイン
Route::post('/admin/login', function (AdminLoginRequest $request, LoginResponse $response) {
    $request->authenticate();
    $request->session()->regenerate();
    return $response->toResponse($request);
})->middleware(['guest', 'throttle:login']);

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function() {
    Route::get('/attendance/list', [AdminController::class, 'index'])->name('admin.attendance.list');
    Route::get('/attendance/{id}', [AdminController::class, 'show'])->name('admin.attendance.show');
    Route::get('/staff/list',[AdminController::class,'staffIndex'])->name('admin.staff.index');
    Route::get('/attendance/staff/{id}',[AdminController::class,'staffAttendance'])->name('admin.attendance.staff');
});


Route::middleware(['auth', 'verified'])->group(function () {
    // 勤怠登録画面（認証済みのみ）
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.store');
    // 勤怠ボタン別
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/break', [AttendanceController::class, 'break'])->name('attendance.break');
    Route::post('/attendance/resume', [AttendanceController::class, 'resume'])->name('attendance.resume');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
    Route::get('/attendance/list',[AttendanceController::class,'attendanceIndex'])->name('attendance.index');
    Route::get('/attendance/detail/{id}',[AttendanceController::class,'show'])->name('attendance.show');
    Route::post('/attendance/{attendance}/request',[AttendanceController::class,"store"])->name('attendance.request.store');
});


// ログイン済み（未認証）
Route::middleware('auth')->group(function () {

    // メール認証
    Route::get('/email/verify', function () {
        return view('auth.email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/attendance');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::get('/email/verify/link', function () {
        return redirect('http://localhost:8025');
    })->name('verification.external');
});

// 一般と管理者の申請一覧（ルートが同じ）
Route::get('/stamp_correction_request/list', [RequestController::class, 'list'])
    ->middleware('auth')
    ->name('request.list');