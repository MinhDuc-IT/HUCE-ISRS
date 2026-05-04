<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng Department (Khoa).
 * Quản lý thông tin các Khoa trong trường.
 * </summary>
 */
class Department extends Model
{
    protected $table = 'Department';
    protected $primaryKey = 'Id';
    public $timestamps = false; 

    protected $fillable = [
        'DepartmentCode',
        'Name',
        'Email',
        'Phone',
        'CreatedAt',
        'UpdatedAt',
    ];

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'DepartmentId');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'DepartmentId');
    }
}
