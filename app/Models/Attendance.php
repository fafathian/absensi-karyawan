<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in_time',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_time',
        'clock_out_latitude',
        'clock_out_longitude',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
