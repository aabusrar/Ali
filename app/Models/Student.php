<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'token',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'user_id', 'user_id');
    }
}