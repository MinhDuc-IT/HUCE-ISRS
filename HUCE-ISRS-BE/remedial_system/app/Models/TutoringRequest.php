<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng TutoringRequest (Yêu cầu phụ đạo).
 * Quản lý các đơn đăng ký học bổ sung của sinh viên.
 * </summary>
 */
class TutoringRequest extends Model
{
    protected $table = 'TutoringRequest';
    protected $primaryKey = 'Id';
    public $timestamps = false; 

    protected $fillable = [
        'StudentId',
        'CourseId',
        'TutoringTermId',
        'RequestedPeriods',
        'Status',
        'Note',
        'CreatedAt',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'StudentId');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'CourseId');
    }

    public function tutoringTerm()
    {
        return $this->belongsTo(TutoringTerm::class, 'TutoringTermId');
    }
}
