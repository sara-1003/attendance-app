<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;

class IndexTest extends TestCase
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

    public function test_自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 1, 28),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 1, 29),
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status_id' => 4,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 1, 30),
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {

        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);


        $response->assertSee('2026/01');
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-12-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/attendance/list?month=2025/12');

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');

        $response->assertSee('2025/12');
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 12, 10));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);


        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-12-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status_id' => 4,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026/01');

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');

        $response->assertSee('2026/01');
    }

    public function test_詳細を押下すると、その日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 15));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-28',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('詳細');

        $detailResponse = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $detailResponse->assertStatus(200);

        $detailResponse->assertSee('2026年');
        $detailResponse->assertSee('1月28日');
        $detailResponse->assertSee('09:00');
        $detailResponse->assertSee('18:00');
    }
}
