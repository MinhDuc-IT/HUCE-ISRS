<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'student_code',
        'full_name',
        'email',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];
}
