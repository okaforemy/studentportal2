<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CBTSetting extends Model
{
    use HasFactory;

    protected $table = 'cbt_settings';

    protected $fillable = [
        'subject',
        'date',
        'time',
        'duration',
        'is_started',
        'section',
        'subject_id',
    ];
}
