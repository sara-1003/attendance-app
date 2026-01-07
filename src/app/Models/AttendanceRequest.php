<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    //users 多対1
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //attendances 多対1
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }


    // attendance_request_breaks 1対多
    public function attendanceRequestBreaks()
    {
        return $this->hasMany(AttendanceRequestBreak::class);
    }


    // approval_histories 1対多
    public function approvalHistories()
    {
        return $this->hasMany(ApprovalHistory::class);
    }
}
