<?php

namespace Tests\Feature\Request;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceStatus;
use Carbon\Carbon;

class UserRequestTest extends TestCase
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

    public function test_出勤時間が退勤時間より後の場合、エラーメッセージが表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

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

        $response = $this->actingAs($user)->post(
            route('attendance.request.store', $attendance->id),
            [
                'clock_in'  => '18:00',
                'clock_out' => '09:00',
            ]
        );

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後の場合、エラーメッセージが表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

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

        $response = $this->actingAs($user)->post(
            route('attendance.request.store', $attendance->id),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => 'テスト修正',
                'breaks' => [
                    [
                        'break_start' => '19:00',
                        'break_end' => '',
                    ]
                ]
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        $this->assertStringContainsString(
            '休憩時間が不適切な値です',
            session('errors')->get('breaks.0.break_start')[0]
        );
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

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

        $response = $this->actingAs($user)->post(
            route('attendance.request.store', $attendance->id),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => 'テスト修正',
                'breaks' => [
                    [
                        'break_start' => '17:00',
                        'break_end' => '18:30',
                    ]
                ]
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        $this->assertStringContainsString(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->get('breaks.0.break_end')[0]
        );
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

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

        $response = $this->actingAs($user)->post(
            route('attendance.request.store', $attendance->id),
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
            session('errors')->get('reason')[0]
        );
    }

    public function test_修正申請処理が実行される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

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

        $response = $this->actingAs($user)->post(
            route('attendance.request.store', $attendance->id),
            [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'reason' => '勤務時間調整',
                'breaks' => [
                    [
                        'break_start' => '12:30',
                        'break_end' => '13:00',
                    ]
                ],
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'reason' => '勤務時間調整',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $adminResponse = $this->actingAs($admin)->get(route('request.list', ['status' => 'pending']));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('勤務時間調整');
        $adminResponse->assertSee($user->name);

        $userResponse = $this->actingAs($user)->get(route('request.list', ['status' => 'pending']));
        $userResponse->assertStatus(200);
        $userResponse->assertSee('勤務時間調整');
    }

    public function test_「承認待ち」にログインユーザーの申請が全て表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

        $user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'date' => '2026-01-10',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status_id' => 4,
        ]);

        $this->actingAs($user)->post(
            route('attendance.request.store', $attendance1->id),
            [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'reason' => '勤務時間調整1',
                'breaks' => [
                    ['break_start' => '12:30', 'break_end' => '13:00']
                ],
            ]
        );

        $this->actingAs($otherUser)->post(
            route('attendance.request.store', $attendance2->id),
            [
                'clock_in' => '10:30',
                'clock_out' => '19:30',
                'reason' => '勤務時間調整2',
                'breaks' => [
                    ['break_start' => '13:00', 'break_end' => '13:30']
                ],
            ]
        );

        $response = $this->actingAs($user)->get(route('request.list', ['status' => 'pending']));

        $response->assertStatus(200);

        $response->assertSee('勤務時間調整1');

        $response->assertDontSee('勤務時間調整2');
    }

    public function test_「承認済み」に管理者が承認した修正申請が全て表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

        $user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 4,
        ]);

        $this->actingAs($user)->post(
            route('attendance.request.store', $attendance->id),
            [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'reason' => '勤務時間調整',
                'breaks' => [
                    ['break_start' => '12:30', 'break_end' => '13:00']
                ],
            ]
        );

        $requestId = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)->first()->id;

        $this->actingAs($admin)->post(route('request.approve', $requestId));

        $response = $this->actingAs($user)->get(route('request.list', ['status' => 'approved']));

        $response->assertStatus(200);

        $response->assertSee('勤務時間調整');
    }

    public function test_各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

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

        $this->actingAs($user)->post(
            route('attendance.request.store', $attendance->id),
            [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'reason' => '勤怠修正',
                'breaks' => [
                    ['break_start' => '12:30', 'break_end' => '13:00']
                ],
            ]
        );

        $requestId = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)->first()->id;

        $listResponse = $this->actingAs($user)->get(route('request.list', ['status' => 'pending']));
        $listResponse->assertStatus(200);

        $detailResponse = $this->actingAs($user)->get(route('attendance.show', $attendance->id));
        $detailResponse->assertStatus(200);

        $detailResponse->assertSee('09:30');
        $detailResponse->assertSee('18:30');
        $detailResponse->assertSee('12:30');
        $detailResponse->assertSee('13:00');
        $detailResponse->assertSee('勤怠修正');
        $detailResponse->assertSee('2026年');
        $detailResponse->assertSee('1月10日');
    }
}