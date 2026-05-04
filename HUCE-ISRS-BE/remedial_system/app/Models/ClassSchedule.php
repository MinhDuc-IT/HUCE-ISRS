<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng ClassSchedule (Lịch học).
 * Quản lý thời khóa biểu của các lớp phụ đạo.
 * </summary>
 */
class ClassSchedule extends Model
{
    protected $table = 'ClassSchedule';
    public $timestamps = false; 

    protected $fillable = [
        'TutoringClassId',
        'DayOfWeek',
        'StartTime',
        'EndTime',
        'Room',
        'Status',
    ];

    public function tutoringClass()
    {
        return $this->belongsTo(TutoringClass::class, 'TutoringClassId');
    }
}
