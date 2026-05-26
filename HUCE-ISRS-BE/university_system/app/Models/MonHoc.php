<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model read-only – ánh xạ bảng TKB_MonHoc (đã có sẵn trong DB trường)
 *
 * KHÔNG ghi dữ liệu. Chỉ dùng để đọc thông tin môn học.
 *
 * @property int    $Id        Khóa chính
 * @property int|null $IDLopHoc Tham chiếu TKB_LopHoc (không phải liên kết ngược từ LopHocPhan; lớp–môn qua TKB_LopHocPhan.IDMonHoc)
 * @property string $MaHocPhan Mã học phần
 * @property string $MaMonHoc  Mã môn học
 * @property string $TenMonHoc Tên môn học
 * @property int    $SoTinChi  Số tín chỉ của môn học
 */
class MonHoc extends Model
{
    protected $connection = 'sqlsrv';
    protected $table      = 'TKB_MonHoc';
    protected $primaryKey = 'Id';
    public    $timestamps = false;

    // Read-only: không cho phép mass assignment
    protected $guarded = ['*'];
}
