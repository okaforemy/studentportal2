<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class secondaryAffective extends Model
{
    use HasFactory;

    protected $fillable=[
        'alertness',
        'responsibility',
        'appearance',
        'property',
        'independently',
        'work',
        'honesty',
        'politeness',
        'confidence',
        'speaking',
        'studentid',
        'term',
        'cooperation',
        'session',
    ];
}
