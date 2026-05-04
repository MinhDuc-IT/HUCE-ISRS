<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model client xác thực API – bảng api_clients (tạo mới, dùng SQLite riêng)
 *
 * Bảng này KHÔNG có trong DB trường. Dùng để quản lý machine-to-machine auth.
 * Kết nối qua 'sqlite' (DB nội bộ của University System).
 *
 * @property int    $id            Khóa chính
 * @property string $client_id     Mã định danh client (duy nhất)
 * @property string $client_secret Mật khẩu bí mật đã được hash bcrypt
 * @property string $name          Tên hệ thống client
 * @property bool   $is_active     Trạng thái kích hoạt
 */
class ApiClient extends Model
{
    // Dùng SQLite nội bộ, không phải SQL Server của trường
    protected $connection = 'sqlite';
    protected $table      = 'api_clients';
    public    $timestamps = true;

    protected $fillable = ['client_id', 'client_secret', 'name', 'is_active'];

    protected $hidden = ['client_secret'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
