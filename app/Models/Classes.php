<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classes extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['class_name',  'section'];

    public function Arms(){
        return $this->hasMany(Arms::class, 'classes_id','id');
    }

    /**
     * The subjects that belong to the Classes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subjects()
    {
        return $this->belongsToMany(Subjects::class, 'class_subject', 'classes_id', 'subject_id');
    }

}
