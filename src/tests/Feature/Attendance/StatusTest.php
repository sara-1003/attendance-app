<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceStatus;
use Carbon\Carbon;

class StatusTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\AttendanceStatus::insert([
            ['id' => 1, 'name' => '勤務外'],
            ['id' => 2, 'name' => '出勤中'],
            ['id' => 3, 'name' => '休憩中'],
            ['id' => 4, 'name' => '退勤済'],
        ]);
    }

    public function test_現現在の日時情報がUIと同じ形式で出力されている()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 8, 0, 0));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $expectedDate = '2026年1月27日(火)';

        $response->assertSee($expectedDate);
    }

    public function test_勤務外の場合、勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 8, 0, 0));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合、勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 9, 0, 0));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status_id' => 2,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_休憩中の場合、勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 12, 0, 0));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status_id' => 3,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_退勤済の場合、勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 18, 0, 0));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
