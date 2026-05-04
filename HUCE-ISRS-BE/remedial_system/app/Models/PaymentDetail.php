<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * <summary>
 * Model đại diện cho bảng PaymentDetail (Chi tiết thanh toán).
 * Lưu chi tiết số giờ dạy và số tiền cho từng lớp của một giảng viên.
 * </summary>
 */
class PaymentDetail extends Model
{
    protected $table = 'PaymentDetail';
    public $timestamps = false; 

    protected $fillable = [
        'PaymentId',
        'TutoringClassId',
        'Hours',
        'Amount',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'PaymentId');
    }

    public function tutoringClass()
    {
        return $this->belongsTo(TutoringClass::class, 'TutoringClassId');
    }
}
