<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $response=$this->post('/admin/login',[
            'email'=>'',
            'password'=>'12345678',
        ]);

        $response->assertSessionHasErrors(['email'=>'メールアドレスを入力してください']);
    }

    public function test_パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $response=$this->post('/admin/login',[
            'email'=>'test@example.com',
            'password'=>'',
        ]);

        $response->assertSessionHasErrors(['password'=>'パスワードを入力してください']);
    }

    public function test_登録内容と一致しない場合、バリデーションメッセージが表示される()
    {
        User::factory()->create([
            'email'=>'test@example.com',
            'password'=>bcrypt('12345678'),
            'role' => 'admin',
        ]);

        $response=$this->post('/admin/login',[
            'email'=>'jest@example.com',
            'password'=>'11111111',
        ]);


        $response->assertSessionHasErrors(['email'=>'ログイン情報が登録されていません']);
    }
}
