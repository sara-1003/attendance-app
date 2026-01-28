<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;


class StaffAttendanceTest extends TestCase
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

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => '2026-01-28',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => '2026-01-28',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('佐藤花子');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('2026/01/28');
    }

    public function test_「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => '2026-01-27',
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'status_id' => 4,
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => '2026-01-28',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-01-27');

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('08:00');
        $response->assertSee('17:00');

        $response->assertDontSee('佐藤花子');
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');

        $response->assertSee('2026/01/27');
    }

    public function test_「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => '2026-01-28',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => '2026-01-29',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-01-29');

        $response->assertStatus(200);

        $response->assertSee('佐藤花子');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertDontSee('山田太郎');
        $response->assertDontSee('09:00');
        $response->assertDontSee('18:00');

        $response->assertSee('2026/01/29');
    }

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-28',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('2026年');
        $response->assertSee('1月28日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-28',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.attendance.update', $attendance->id), [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors();

        $this->assertStringContainsString(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->first()
        );
    }

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-28',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => 'テスト修正',
                'breaks' => [
                    [
                        'break_start' => '18:30',
                        'break_end' => '',
                    ]
                ],
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        $this->assertStringContainsString(
            '休憩時間が不適切な値です',
            session('errors')->first()
        );
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-28',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => 'テスト修正',
                'breaks' => [
                    [
                        'break_start' => '17:00',
                        'break_end' => '18:30',
                    ]
                ],
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        $this->assertStringContainsString(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first()
        );
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 28));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-28',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => '',
                'breaks' => [
                    [
                        'break_start' => '12:00',
                        'break_end' => '13:00',
                    ]
                ],
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        $this->assertStringContainsString(
            '備考を記入してください',
            session('errors')->first()
        );
    }
}