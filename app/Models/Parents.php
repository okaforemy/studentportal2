<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parents extends Authenticatable
{
    use SoftDeletes, Notifiable, HasApiTokens;

    protected $table='parents';

    protected $fillable = ['fullname', 'nationality','occupation','home_address', 'office_address','email','phone','isparent', 'password'];

    public function Students(){
        return $this->belongsToMany(Student::class,'parents_student','parents_id','student_id');
    }
}
