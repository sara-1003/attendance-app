<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_名前が未入力の場合、バリデーションメッセージが表示される()
    {
        $response=$this->post('/register',[
            'name'=>'',
            'email'=>'test@example.com',
            'password'=>'12345678',
            'password_confirmation'=>'12345678',
        ]);

        $response->assertSessionHasErrors(['name'=>'お名前を入力してください']);
    }

    public function test_メールアドレスが未入力場合、バリデーションメッセージが表示される()
    {
        $response=$this->post('/register',[
            'name'=>'太郎',
            'email'=>'',
            'password'=>'12345678',
            'password_confirmation'=>'12345678',
        ]);

        $response->assertSessionHasErrors(['email'=>'メールアドレスを入力してください']);
    }

    public function test_パスワードが8文字未満の場合、バリデーションメッセージが表示される()
    {
        $response=$this->post('/register',[
            'name'=>'太郎',
            'email'=>'test@example.com',
            'password'=>'1234567',
            'password_confirmation'=>'12345678',
        ]);

        $response->assertSessionHasErrors(['password'=>'パスワードは8文字以上で入力してください']);
    }

    public function test_パスワードが一致しない場合、バリデーションメッセージが表示される()
    {
        $response=$this->post('/register',[
            'name'=>'太郎',
            'email'=>'test@example.com',
            'password'=>'12345678',
            'password_confirmation'=>'12345679',
        ]);

        $response->assertSessionHasErrors(['password_confirmation'=>'パスワードと一致しません']);
    }

    public function test_パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $response=$this->post('/register',[
            'name'=>'太郎',
            'email'=>'test@example.com',
            'password'=>'',
            'password_confirmation'=>'12345678',
        ]);

        $response->assertSessionHasErrors(['password'=>'パスワードを入力してください']);
    }

    public function test_フォームに内容が入力されていた場合、データが正常に保存される()
    {
        $response=$this->post('/register',[
            'name'=>'太郎',
            'email'=>'test@example.com',
            'password'=>'12345678',
            'password_confirmation'=>'12345678',
        ]);

        $this->assertDatabaseHas('users',['email'=>'test@example.com']);
    }
}
