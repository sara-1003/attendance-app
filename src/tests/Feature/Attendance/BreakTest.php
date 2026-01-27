<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTest extends TestCase
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

    public function test_休憩ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 10, 0, 0));

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
        $response->assertSee('休憩入');

        $this->actingAs($user)->post('/attendance/break');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status_id' => 3,
        ]);
    }

    public function test_休憩は一日に何回でもできる()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 11, 0, 0));

        $user = User::factory()->create(['email_verified_at' => now()]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'status_id' => 2,
        ]);

        $this->actingAs($user)->post('/attendance/break');


        $this->actingAs($user)->post('/attendance/resume');


        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩入');
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 12, 0, 0));

        $user = User::factory()->create(['email_verified_at' => now()]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'status_id' => 2,
        ]);

        $this->actingAs($user)->post('/attendance/break');


        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
        $response->assertSee('休憩中');


        $this->actingAs($user)->post('/attendance/resume');


        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');


        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status_id' => 2,
        ]);
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 15, 0, 0));

        $user = User::factory()->create(['email_verified_at' => now()]);


        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'status_id' => 2,
        ]);


        $this->actingAs($user)->post('/attendance/break');


        $this->actingAs($user)->post('/attendance/resume');


        $this->actingAs($user)->post('/attendance/break');


        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩戻');
        $response->assertSee('休憩中');
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 27, 12, 0, 0));

        $user = User::factory()->create(['email_verified_at' => now()]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'status_id' => 2,
        ]);

        $this->actingAs($user)->post('/attendance/break');

        Carbon::setTestNow(Carbon::create(2026, 1, 27, 12, 30, 0));

        $this->actingAs($user)->post('/attendance/resume');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('0:30');
    }
}