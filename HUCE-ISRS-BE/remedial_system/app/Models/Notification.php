<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng Notification (Thông báo).
 * Lưu trữ lịch sử thông báo cho người dùng (sinh viên, giảng viên).
 * </summary>
 */
class Notification extends Model
{
    protected $table = 'Notification';
    public $timestamps = false; 

    protected $fillable = [
        'UserId',
        'Title',
        'Content',
        'IsRead',
        'CreatedAt',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserId');
    }
}
