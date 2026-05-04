<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng Teacher (Giảng viên).
 * Quản lý thông tin giảng viên dạy các lớp phụ đạo.
 * </summary>
 */
class Teacher extends Model
{
    protected $table = 'Teacher';
    protected $primaryKey = 'Id';
    public $timestamps = false; 

    protected $fillable = [
        'TeacherCode',
        'FullName',
        'Email',
        'DepartmentId',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentId');
    }

    public function tutoringClasses()
    {
        return $this->hasMany(TutoringClass::class, 'TeacherId');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'TeacherId');
    }
}
