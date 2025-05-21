<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class preNurseryAffective extends Model
{
    use HasFactory;

    protected $fillable=[
        'alertness',
        'appearance',
        'independently',
        'work',
        'honesty',
        'peers',
        'politeness',
        'confidence',
        'studentid',
        'leadership',
        'term',
        'session',
        'id',
    ];
}
