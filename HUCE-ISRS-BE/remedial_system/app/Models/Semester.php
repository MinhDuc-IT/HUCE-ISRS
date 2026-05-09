<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng Semester (Học kỳ).
 * Quản lý các học kỳ trong hệ thống.
 * </summary>
 */
class Semester extends Model
{
    protected $table = 'Semester';
    public $timestamps = false; 

    protected $fillable = [
        'Name',
        'Year',
        'TermNumber',
        'IsActive',
    ];

    public function tutoringClasses()
    {
        return $this->hasMany(TutoringClass::class, 'SemesterId');
    }

    public function tutoringRequests()
    {
        return $this->hasMany(TutoringRequest::class, 'SemesterId');
    }
}
