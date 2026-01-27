<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;

class WorkEndTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        AttendanceStatus::insert([
            ['id' => 1, 'name' => '勤務外'],
            ['id' => 2, 'name' => '出勤中'],
            ['id' => 3, 'name' => '休憩中'],
            ['id' => 4, 'name' => '退勤済'],
        ]);
    }

    public function test_退勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 18, 0, 0));

        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'status_id' => 2,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $this->actingAs($user)->post('/attendance/end');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status_id' => 4,
        ]);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 9, 0, 0));

        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $this->actingAs($user)->post('/attendance/start');


        Carbon::setTestNow(Carbon::create(2026, 1, 27, 18, 0, 0));

        $this->actingAs($user)->post('/attendance/end');


        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}