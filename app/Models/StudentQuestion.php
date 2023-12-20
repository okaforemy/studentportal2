<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentQuestion extends Model
{
    use HasFactory;

    protected $table = 'student_questions';

    /**
     * Get the cbtstudents that owns the StudentQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cbtstudents()
    {
        return $this->belongsTo(CBTStudents::class, 'student_id', 'student_id');
    }
}
