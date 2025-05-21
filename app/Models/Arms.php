<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arms extends Model
{
    use HasFactory;

    protected $fillable = ['arm_name','classes_id'];

    public function Classes(){
        return $this->belongsTo(Classes::class);
    }

    /**
     * The subject that belong to the Arms
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subjects()
    {
        return $this->belongsToMany(Subjects::class, 'class_subject', 'arms_id', 'subject_id');
    }
}
