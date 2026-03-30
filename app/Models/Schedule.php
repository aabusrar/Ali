<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'user_id',
        'day',
        'time_from',
        'time_to',
        'course_name',
        'course_code',
        'room',
        'instructor',
        'section',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'user_id', 'user_id');
    }
}