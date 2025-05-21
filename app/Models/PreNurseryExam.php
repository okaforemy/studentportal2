<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreNurseryExam extends Model
{
    use HasFactory;

    public function student(){
        return $this->belongsTo(Student::class);
    }

    protected $fillable = [
        'student_id',
        'subject',
        'category',
        'value',
        'session',
        'term'
    ];
}
