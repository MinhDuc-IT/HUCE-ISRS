<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng Course (Môn học).
 * Quản lý danh mục các môn học trong hệ thống.
 * </summary>
 */
class Course extends Model
{
    protected $table = 'Course';
    protected $primaryKey = 'Id';
    public $timestamps = false; 

    protected $fillable = [
        'CourseCode',
        'CourseName',
        'Credits',
        'DepartmentId',
        'CreatedAt',
        'UpdatedAt',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentId');
    }

    public function tutoringClasses()
    {
        return $this->hasMany(TutoringClass::class, 'CourseId');
    }

    public function tutoringRequests()
    {
        return $this->hasMany(TutoringRequest::class, 'CourseId');
    }
}
