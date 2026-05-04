<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng SystemConfig (Cấu hình hệ thống).
 * Lưu trữ các thiết lập chung cho toàn hệ thống theo dạng Key-Value.
 * </summary>
 */
class SystemConfig extends Model
{
    protected $table = 'SystemConfig';
    protected $primaryKey = 'Id';
    public $timestamps = false; 

    protected $fillable = [
        'Key',
        'Value',
        'Description',
    ];
}
