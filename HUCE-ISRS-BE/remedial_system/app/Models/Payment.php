<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng Payment (Thanh toán).
 * Quản lý thông tin tổng hợp thanh toán thù lao cho giảng viên.
 * </summary>
 */
class Payment extends Model
{
    protected $table = 'Payment';
    public $timestamps = false; 

    protected $fillable = [
        'TeacherId',
        'TutoringTermId',
        'TotalHours',
        'UnitPrice',
        'Coefficient',
        'Amount',
        'Status',
        'CreatedAt',
    ];

    public function tutoringTerm()
    {
        return $this->belongsTo(TutoringTerm::class, 'TutoringTermId');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'TeacherId');
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class, 'PaymentId');
    }
}
