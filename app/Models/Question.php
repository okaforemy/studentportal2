<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'queston',
        'subject_id',
        'answer_to_question',
        'option_a',
        'option_b', 
        'option_c', 
        'option_d',
        'option_e',
        'grade',
        'teacher_id'
    ];
}
