<?php

namespace Tests\Feature\Request;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AdminApproveTest extends TestCase
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

    public function test_承認待ちの修正申請が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user1 = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => '2026-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => '2026-01-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status_id' => 4,
        ]);

        $this->actingAs($user1)->post(route('attendance.request.store', $attendance1->id), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'reason' => 'ユーザー1申請',
            'breaks' => [
                ['break_start' => '12:30', 'break_end' => '13:00']
            ],
        ]);

        $this->actingAs($user2)->post(route('attendance.request.store', $attendance2->id), [
            'clock_in' => '10:30',
            'clock_out' => '19:30',
            'reason' => 'ユーザー2申請',
            'breaks' => [
                ['break_start' => '13:00', 'break_end' => '13:30']
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('request.list', ['status' => 'pending']));

        $response->assertStatus(200);

        $response->assertSee('ユーザー1申請');
        $response->assertSee('ユーザー2申請');
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user1 = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => '2026-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => '2026-01-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status_id' => 4,
        ]);

        $this->actingAs($user1)->post(route('attendance.request.store', $attendance1->id), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'reason' => 'ユーザー1承認済み',
            'breaks' => [
                ['break_start' => '12:30', 'break_end' => '13:00']
            ],
        ]);

        $this->actingAs($user2)->post(route('attendance.request.store', $attendance2->id), [
            'clock_in' => '10:30',
            'clock_out' => '19:30',
            'reason' => 'ユーザー2承認済み',
            'breaks' => [
                ['break_start' => '13:00', 'break_end' => '13:30']
            ],
        ]);

        $request1 = \App\Models\AttendanceRequest::where('attendance_id', $attendance1->id)->first();
        $request2 = \App\Models\AttendanceRequest::where('attendance_id', $attendance2->id)->first();

        $this->actingAs($admin)->post(route('request.approve', $request1->id));
        $this->actingAs($admin)->post(route('request.approve', $request2->id));

        $response = $this->actingAs($admin)->get(route('request.list', ['status' => 'approved']));

        $response->assertStatus(200);

        $response->assertSee('ユーザー1承認済み');
        $response->assertSee('ユーザー2承認済み');
    }

    public function test_修正申請の詳細内容が正しく表示されている()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $this->actingAs($user)->post(route('attendance.request.store', $attendance->id), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'reason' => '管理者確認用テスト',
            'breaks' => [
                ['break_start' => '12:45', 'break_end' => '13:15']
            ],
        ]);

        $request = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)->first();

        $response = $this->actingAs($admin)->get(route('request.approval', $request->id));

        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee('2026年');
        $response->assertSee('1月10日');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertSee('12:45');
        $response->assertSee('13:15');
        $response->assertSee('管理者確認用テスト');
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        \App\Models\AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $this->actingAs($user)->post(route('attendance.request.store', $attendance->id), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'reason' => '承認テスト',
            'breaks' => [
                ['break_start' => '12:30', 'break_end' => '13:30']
            ],
        ]);

        $request = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)->first();

        $response = $this->actingAs($admin)->post(route('request.approve', $request->id));

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '12:30:00',
            'break_end' => '13:30:00',
        ]);

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
        ]);
    }
}
