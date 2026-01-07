<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalHistory extends Model
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

    //attendance_requests 多対1
    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }
}
