<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration tạo bảng api_clients – lưu thông tin client xác thực hệ thống.
 *
 * Chạy trên connection 'sqlite' (DB nội bộ), không phải DB trường.
 * Bảng này không có trong DB gốc .bak.
 */
return new class extends Migration
{
    // Chạy migration này trên SQLite nội bộ
    protected $connection = 'sqlite';

    public function up(): void
    {
        Schema::connection('sqlite')->create('api_clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_id')->unique()->comment('Mã định danh client (duy nhất)');
            $table->string('client_secret')->comment('Mật khẩu bí mật đã được hash bcrypt');
            $table->string('name')->comment('Tên hệ thống client');
            $table->boolean('is_active')->default(true)->comment('Trạng thái kích hoạt');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite')->dropIfExists('api_clients');
    }
};
