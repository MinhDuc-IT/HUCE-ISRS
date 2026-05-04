<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng SystemConfig (Cấu hình hệ thống).
 * Lưu trữ các thiết lập chung cho toàn hệ thống (sĩ số, số tiết,...).
 * </summary>
 */
class SystemConfig extends Model
{
    protected $table = 'SystemConfig';
    public $timestamps = false; 

    protected $fillable = [
        'MinStudentsPerClass',
        'MaxStudentsPerClass',
        'DefaultPeriods',
    ];
}
