<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model read-only – ánh xạ bảng DT_TongKetDot (đã có sẵn trong DB trường)
 *
 * KHÔNG ghi dữ liệu. Chỉ dùng để đọc tổng kết học kỳ của sinh viên.
 *
 * @property int    $Id                   Khóa chính
 * @property int    $IDDot                Khóa ngoại tới DM_Dot
 * @property int    $IDSinhVien           Khóa ngoại tới DT_SinhVien
 * @property float  $DiemTBHocLuc         Điểm trung bình hệ 10 trong học kỳ
 * @property float  $DiemTBTinChi         Điểm trung bình hệ 4 trong học kỳ
 * @property string $DiemChu              Xếp loại học lực (A, B, C, D, F)
 * @property float  $DiemTBHocLucTichLuy  Điểm trung bình tích lũy hệ 10
 * @property float  $DiemTBTinChiTichLuy  Điểm trung bình tích lũy hệ 4
 * @property string $diemChuTichLuy       Xếp loại tích lũy
 * @property int    $NamThu               Năm học thứ mấy
 * @property int    $SoTCTichLuy          Tổng số tín chỉ tích lũy
 * @property int    $SoTCKhongDat         Số tín chỉ chưa đạt
 */
class TongKetDot extends Model
{
    protected $connection = 'sqlsrv';
    protected $table      = 'DT_TongKetDot';
    protected $primaryKey = 'Id';
    public    $timestamps = false;

    // Read-only: không cho phép mass assignment
    protected $guarded = ['*'];
}
