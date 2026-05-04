<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model read-only – ánh xạ bảng DT_SinhVien (đã có sẵn trong DB trường)
 *
 * KHÔNG ghi dữ liệu. Chỉ dùng để đọc thông tin sinh viên.
 *
 * @property int    $Id          Khóa chính
 * @property string $MaSinhVien  Mã sinh viên (VD: SV001)
 * @property string $HoDem       Họ và tên đệm
 * @property string $Ten         Tên
 * @property string $GioiTinh    Giới tính (Nam/Nữ)
 * @property string $NgaySinh2   Ngày sinh (dạng chuỗi)
 * @property int    $NoiSinh     ID nơi sinh (FK)
 * @property string $Email       Email cá nhân
 * @property string $SoTaiKhoan  Số tài khoản ngân hàng
 * @property string $NoiSinh_Text Nơi sinh (tên tỉnh/thành)
 */
class SinhVien extends Model
{
    protected $connection = 'sqlsrv';
    protected $table      = 'DT_SinhVien';
    protected $primaryKey = 'Id';
    public    $timestamps = false;

    // Read-only: không cho phép mass assignment
    protected $guarded = ['*'];

    /**
     * Email trường đại học của sinh viên.
     */
    public function emailNguoiDung(): HasOne
    {
        return $this->hasOne(EmailNguoiDung::class, 'IDSinhVien', 'Id');
    }

    /**
     * Kết quả học tập các môn của sinh viên.
     */
    public function ketQuaHocTap(): HasMany
    {
        return $this->hasMany(KetQuaHocTap::class, 'IDSinhVien', 'Id');
    }

    /**
     * Tổng kết học kỳ của sinh viên.
     */
    public function tongKetDot(): HasMany
    {
        return $this->hasMany(TongKetDot::class, 'IDSinhVien', 'Id');
    }
}
