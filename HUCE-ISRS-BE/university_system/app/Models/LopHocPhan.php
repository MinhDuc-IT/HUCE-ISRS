<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model read-only – ánh xạ bảng TKB_LopHocPhan (đã có sẵn trong DB trường)
 *
 * KHÔNG ghi dữ liệu. Chỉ dùng để đọc thông tin lớp học phần.
 *
 * @property int    $Id           Khóa chính
 * @property int|null $IDMonHoc   Khóa ngoại tới TKB_MonHoc
 * @property int    $IDDot        Khóa ngoại tới DM_Dot (học kỳ)
 * @property string $MaLopHocPhan Mã lớp học phần
 * @property string $LopDuKien    Lớp dự kiến đăng ký
 */
class LopHocPhan extends Model
{
    protected $connection = 'sqlsrv';
    protected $table      = 'TKB_LopHocPhan';
    protected $primaryKey = 'Id';
    public    $timestamps = false;

    // Read-only: không cho phép mass assignment
    protected $guarded = ['*'];

    /**
     * Môn học thuộc lớp học phần này (TKB_LopHocPhan.IDMonHoc → TKB_MonHoc.Id).
     */
    public function monHoc(): BelongsTo
    {
        return $this->belongsTo(MonHoc::class, 'IDMonHoc', 'Id');
    }

    /**
     * Học kỳ của lớp học phần.
     */
    public function dot(): BelongsTo
    {
        return $this->belongsTo(Dot::class, 'IDDot', 'Id');
    }
}
