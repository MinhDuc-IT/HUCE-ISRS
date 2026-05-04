<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model read-only – ánh xạ bảng HT_EmailNguoiDung (đã có sẵn trong DB trường)
 *
 * KHÔNG ghi dữ liệu. Chỉ dùng để đọc email trường của sinh viên.
 *
 * @property int    $Id         Khóa chính
 * @property int    $IDSinhVien Khóa ngoại tới DT_SinhVien
 * @property string $EMail01    Địa chỉ email trường cấp cho sinh viên
 */
class EmailNguoiDung extends Model
{
    protected $connection = 'sqlsrv';
    protected $table      = 'HT_EmailNguoiDung';
    protected $primaryKey = 'Id';
    public    $timestamps = false;

    // Read-only: không cho phép mass assignment
    protected $guarded = ['*'];
}
