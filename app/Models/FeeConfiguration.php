<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeConfiguration extends Model
{
    use HasFactory;

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
