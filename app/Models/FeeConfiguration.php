<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The students that belong to the FeeConfiguration
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'Student', 'fee_configuration_id', 'student_id');
    }
}
