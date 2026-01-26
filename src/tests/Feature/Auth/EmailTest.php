<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class EmailTest extends TestCase
{

    use RefreshDatabase;

    public function test_会員登録後、認証メールが送信される()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => '太郎',
            'email' => 'test@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );

        $this->assertFalse($user->hasVerifiedEmail());
    }

    public function test_認証はこちらからボタンでメール認証サイトに遷移する()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/email/verify/link');

        $response->assertRedirect('http://localhost:8025');
    }

    public function test_メール認証を完了すると、勤怠画面に遷移する()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/attendance');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
