<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 一般ユーザー取得
        $users = User::where('role', 'user')->get();

        // 期間：過去3ヶ月〜今月末
        $start = Carbon::now()->subMonths(3)->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        foreach ($users as $user) {
            $date = $start->copy();

            while ($date <= $end) {

                // 土日はスキップ
                if ($date->isWeekend()) {
                    $date->addDay();
                    continue;
                }

                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                ]);

                // 休憩1件
                AttendanceBreak::factory()->create([
                    'attendance_id' => $attendance->id,
                ]);

                $date->addDay();
            }
        }
    }
}