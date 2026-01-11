<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    //user 多対1
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // attendance_breaks 1対多
    public function attendanceBreaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    // attendance_requests 1対多
    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }

    // attendance_status 多対1
    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class, 'status_id');
    }
}
