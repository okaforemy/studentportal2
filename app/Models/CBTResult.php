<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CBTResult extends Model
{
    use HasFactory;

    protected $fillable = ['fullname', 'subject_id', 'student_id', 'marks_obtainable', 'marks_obtained', 'grade', 'term', 'session'];

       /**
         * Get the subject that owns the CBTResult
         *
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function subject()
        {
            return $this->belongsTo(Subjects::class, 'subject_id', 'id');
        }
}
