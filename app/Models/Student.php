<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['surname','othernames','dob','sex','student_id','grade', 'reg_progress','fullname','arm', 'class_id'];

    public function Parents(){
        return $this->belongsToMany(Parents::class,'parents_student','student_id','parents_id');
    }

    
    /**
     * Get all of the subjecs for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Subjects()
    {
        return $this->belongsToMany(Subjects::class, 'student_subjects','student_id','subjects_id')->withTimestamps();
    }

    /**
     * Get all of the primaryAffective for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function primaryAffective()
    {
        return $this->hasMany(primaryAffective::class, 'studentid', 'id');
    }

    /**
     * Get the preNurseryAffective associated with the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function preNurseryAffective()
    {
        return $this->hasOne(preNurseryAffective::class, 'studentid', 'id');
    }

     /**
     * Get all of the secondaryAffective for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function secondaryAffective()
    {
        return $this->hasMany(secondaryAffective::class, 'studentid', 'id');
    }

    /**
     * Get the attendance associated with the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attendance()
    {
        return $this->hasOne(attendance::class, 'studentid', 'id');
    }

    /**
     * Get the physicaldevelopment associated with the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function physicaldevelopment()
    {
        return $this->hasOne(physicaldevelopment::class, 'studentid', 'id');
    }

    /**
     * Get all of the remarks for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function remarks()
    {
        return $this->hasMany(remark::class, 'student_id', 'id');
    }

    /**
     * Get the holidayAssessment associated with the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function holidayAssessment()
    {
        return $this->hasMany(HolidayAssessment::class);
    }

     /**
     * Get all of the primaryExam for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function primaryExam()
    {
        return $this->hasMany(PrimaryExam::class);
    }

    public function prenurseryexam(){
        return $this->hasMany(PreNurseryExam::class,'student_id');
    }

    /**
     * Get all of the secondaryExam for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function secondaryExam()
    {
        return $this->hasMany(secondaryExam::class, 'student_id');
    }

    /**
     * Get all of the seniorSecondaryExam for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function seniorSecondaryExam()
    {
        return $this->hasMany(SeniorSecondaryExam::class, 'student_id');
    }

    /**
     * Get the behaviour associated with the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function behaviour()
    {
        return $this->hasOne(Behaviour::class);
    }

    /**
     * Get the picture associated with the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function picture()
    {
        return $this->hasOne(StudentImage::class);
    }

    /**
     * Get the grade that owns the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function studentGrade()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }
}
