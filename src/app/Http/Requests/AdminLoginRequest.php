<?php

namespace App\Http\Requests;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Requests\LoginRequest;

class AdminLoginRequest extends LoginRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required'],
        ];
    }

    public function messages()
    {
        return[
            'email.required'=>'メールアドレスを入力してください',
            'email.string'=>'メールアドレスを文字列で入力してください',
            'email.email'=>'有効なメールアドレス形式を入力してください',
            'password.required'=>'パスワードを入力してください',
        ];
    }

    public function authenticate()
    {
        $credentials = $this->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        // 管理者以外はログアウト
        if (Auth::user()->role !== 'admin') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }
    }
}
