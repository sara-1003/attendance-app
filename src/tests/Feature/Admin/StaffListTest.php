<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;

class StaffListTest extends TestCase
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

    public function test_管理者ユーザーが全一般ユーザーの「氏名」と「メールアドレス」を確認できる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'role' => 'user',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'hanako@example.com',
            'role' => 'user',
        ]);

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('taro@example.com');

        $response->assertSee('佐藤花子');
        $response->assertSee('hanako@example.com');
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
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

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user1->id}");

        $response->assertStatus(200);

        $response->assertSee('01/28');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 15));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(['name' => '山田太郎']);

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

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}?month=2025-12");

        $response->assertStatus(200);

        $response->assertSee('12/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertDontSee('01/10');
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    public function test_「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 15));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(['name' => '山田太郎']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-02-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}?month=2026-02");

        $response->assertStatus(200);

        $response->assertSee('02/10');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertDontSee('01/10');
        $response->assertDontSee('09:00');
        $response->assertDontSee('18:00');
    }

    public function test_「詳細」を押下するとその日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 15));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);

        $response->assertSee('2026年');
        $response->assertSee('1月10日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
