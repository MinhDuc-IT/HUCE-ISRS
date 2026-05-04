<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng Attendance (Điểm danh).
 * Quản lý thông tin điểm danh của sinh viên trong lớp phụ đạo.
 * </summary>
 */
class Attendance extends Model
{
    protected $table = 'Attendance';
    public $timestamps = false; 

    protected $fillable = [
        'EnrollmentId',
        'StudyDate',
        'IsPresent',
        'Note',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'EnrollmentId');
    }
}
