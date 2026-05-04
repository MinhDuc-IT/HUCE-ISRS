<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model read-only – ánh xạ bảng DT_KetQuaHocTapMonHoc (đã có sẵn trong DB trường)
 *
 * KHÔNG ghi dữ liệu. Chỉ dùng để đọc kết quả học tập của sinh viên.
 *
 * @property int    $Id              Khóa chính
 * @property int    $IDSinhVien      Khóa ngoại tới DT_SinhVien
 * @property int    $IDLopHoc       Khóa ngoại tới TKB_LopHocPhan
 * @property float  $DiemChuyenCan1  Điểm chuyên cần / quá trình
 * @property float  $DiemThi         Điểm thi cuối kỳ
 * @property float  $DiemTongKet     Điểm tổng kết (hệ 10)
 * @property float  $DiemTinChi      Điểm tín chỉ (hệ 4)
 * @property string $DiemChu         Điểm chữ (A, B+, B, C+, ...)
 */
class KetQuaHocTap extends Model
{
    protected $connection = 'sqlsrv';
    protected $table      = 'DT_KetQuaHocTapMonHoc';
    protected $primaryKey = 'Id';
    public    $timestamps = false;

    // Read-only: không cho phép mass assignment
    protected $guarded = ['*'];

    /**
     * Lớp học phần tương ứng với kết quả này.
     */
    public function lopHocPhan(): BelongsTo
    {
        return $this->belongsTo(LopHocPhan::class, 'IDLopHoc', 'Id');
    }
}
