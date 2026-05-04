<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model read-only – ánh xạ bảng DM_Dot (đã có sẵn trong DB trường)
 *
 * KHÔNG ghi dữ liệu. Chỉ dùng để đọc thông tin học kỳ.
 *
 * @property int  $Id        Khóa chính
 * @property int  $SoThuTU   Thứ tự học kỳ trong năm (1 = HK1, 2 = HK2, 3 = hè)
 * @property int  $IdNamHoc  Năm học (VD: 2024)
 * @property bool $IsActive  Học kỳ hiện tại đang hoạt động
 * @property bool $IsVisible Hiển thị trong giao diện hay không
 */
class Dot extends Model
{
    protected $connection = 'sqlsrv';
    protected $table      = 'DM_Dot';
    protected $primaryKey = 'Id';
    public    $timestamps = false;

    // Read-only: không cho phép mass assignment
    protected $guarded = ['*'];

    protected $casts = [
        'IsActive'  => 'boolean',
        'IsVisible' => 'boolean',
    ];
}
