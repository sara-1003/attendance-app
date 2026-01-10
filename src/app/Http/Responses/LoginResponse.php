<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        // メール未認証の場合はメール認証画面へ
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // 管理者の場合は管理者画面へ
        if ($user->role === 'admin') {
            return redirect('/admin/attendance/list');
        }

        // 一般ユーザーの場合は /attendance へ
        return redirect('/attendance');
    }
}
