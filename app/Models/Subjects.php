<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subjects extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['subject','holiday','subject_teacher','category','section'];

   // protected $hidden = ['created_at', 'updated_at'];

    /**
     * Get all of the Student for the Subjects
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Student()
    {
        return $this->belongsToMany(Student::class,'student_subjects', 'subjects_id','student_id')->withTimestamps();
    }

    /**
     * The classes that belong to the Subjects
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'class_subject', 'subject_id', 'classes_id');
    }

    /**
     * The arms that belong to the Subjects
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function arms()
    {
        return $this->belongsToMany(Arms::class, 'class_subject', 'subject_id', 'arms_id');
    }

}

