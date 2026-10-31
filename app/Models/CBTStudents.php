<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class CBTStudents extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'cbtstudents';

    protected $fillable = [
        'firstname',
        'lastname',
        'student_id',
        'term',
        'session',
        'grade',
        'device_token',
        'timer',
    ];

    public function studentquestion()
    {
        return $this->hasMany(StudentQuestion::class, 'student_id', 'student_id');
    }
}
