<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    // attendances 1対多
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
