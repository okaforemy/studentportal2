<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class CBTStudents extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'cbtstudents';

    protected $fillable = ['device_token'];

    /**
     * Get all of the studentquestion for the CBTStudents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function studentquestion()
    {
        return $this->belongsTo(StudentQuestion::class);
    }
}
