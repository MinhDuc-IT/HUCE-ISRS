<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng Enrollment (Ghi danh).
 * Quản lý danh sách sinh viên đã được xếp vào lớp phụ đạo.
 * </summary>
 */
class Enrollment extends Model
{
    protected $table = 'Enrollment';
    protected $primaryKey = 'Id';
    public $timestamps = false; 

    protected $fillable = [
        'StudentId',
        'TutoringClassId',
        'Status',
        'CreatedAt',
        'UpdatedAt',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'StudentId');
    }

    public function tutoringClass()
    {
        return $this->belongsTo(TutoringClass::class, 'TutoringClassId');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'EnrollmentId');
    }
}
