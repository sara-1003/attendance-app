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

    //user 多対1
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // breaks 1対多
    public function breaks()
    {
        return $this->hasMany(Break::class);
    }

    
}
