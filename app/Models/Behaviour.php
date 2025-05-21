<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Behaviour extends Model
{
    use HasFactory;

    protected $fillable = [
        'homework',
        'behaviour',
        'student_id',
        'session',
        'term'
    ];
}
