<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng TutoringTerm (Đợt phụ đạo).
 * Lưu trữ cấu hình thời gian và đơn giá cho một đợt đăng ký.
 * </summary>
 */
class TutoringTerm extends Model
{
    protected $table = 'TutoringTerm';
    protected $primaryKey = 'Id';
    public $timestamps = false; // Tùy chọn, dùng CreatedAt thủ công nếu cần

    protected $fillable = [
        'SemesterId',
        'Name',
        'StartDate',
        'EndDate',
        'HeSoPD',
        'DonGia1Tiet',
        'HeSoDonGia',
        'IsDefault',
        'CreatedAt',
    ];

    protected $casts = [
        'StartDate'   => 'date',
        'EndDate'     => 'date',
        'HeSoPD'      => 'integer',
        'DonGia1Tiet' => 'integer',
        'HeSoDonGia'  => 'float',
        'IsDefault'   => 'boolean',
        'CreatedAt'   => 'datetime',
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'SemesterId');
    }

    public function tutoringClasses()
    {
        return $this->hasMany(TutoringClass::class, 'TutoringTermId');
    }

    public function tutoringRequests()
    {
        return $this->hasMany(TutoringRequest::class, 'TutoringTermId');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'TutoringTermId');
    }
}
