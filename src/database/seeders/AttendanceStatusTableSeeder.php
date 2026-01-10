<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['name' => '勤務外'],
            ['name' => '出勤中'],
            ['name' => '休憩中'],
            ['name' => '退勤済'],
        ];

        DB::table('attendance_statuses')->insert($statuses);
    }
}
