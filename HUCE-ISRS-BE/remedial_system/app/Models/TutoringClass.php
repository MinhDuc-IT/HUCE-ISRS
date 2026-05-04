<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng TutoringClass (Lớp phụ đạo).
 * Quản lý thông tin đợt phụ đạo/lớp phụ đạo được mở.
 * </summary>
 */
class TutoringClass extends Model
{
    protected $table = 'TutoringClass';
    protected $primaryKey = 'Id';
    public $timestamps = false; 

    protected $fillable = [
        'CourseId',
        'TutoringTermId',
        'TeacherId',
        'MaxStudents',
        'Status',
        'CreatedAt',
        'UpdatedAt',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'CourseId');
    }

    public function tutoringTerm()
    {
        return $this->belongsTo(TutoringTerm::class, 'TutoringTermId');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'TeacherId');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'TutoringClassId');
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'TutoringClassId');
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class, 'TutoringClassId');
    }
}
