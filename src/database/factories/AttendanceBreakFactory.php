<?php

namespace Database\Factories;

use App\Models\AttendanceBreak;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        return [
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ];
    }
}
