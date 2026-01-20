<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status_id' => 1,
        ];
    }
}
