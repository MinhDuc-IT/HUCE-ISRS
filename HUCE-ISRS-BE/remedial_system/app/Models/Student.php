<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng Student (Sinh viên).
 * Quản lý thông tin sinh viên tham gia hệ thống phụ đạo.
 * </summary>
 */
class Student extends Model
{
    protected $table = 'Student';
    protected $primaryKey = 'Id';
    public $timestamps = false; 

    protected $fillable = [
        'StudentCode',
        'FullName',
        'Email',
        'CreatedAt',
        'UpdatedAt',
    ];

    public function tutoringRequests()
    {
        return $this->hasMany(TutoringRequest::class, 'StudentId');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'StudentId');
    }
}
