<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestBreak extends Model
{
    use HasFactory;


    protected $guarded = [
        'id',
    ];

    //attendance_requests 多対1
    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }
}
