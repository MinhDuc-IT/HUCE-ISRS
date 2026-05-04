# University System (Mock API)

Hệ thống giả lập dữ liệu của Trường Đại học (University System) cung cấp API xác thực và truy vấn thông tin sinh viên cho hệ thống Phụ đạo.

## 1. Yêu cầu hệ thống
- **PHP**: >= 8.2
- **Composer**: >= 2.0
- **Database**: 
    - **SQLite**: Dùng để lưu trữ Client API và Tokens (mặc định).
    - **SQL Server**: Dùng để truy vấn dữ liệu sinh viên/điểm (Yêu cầu cài đặt [PHP Drivers for SQL Server](https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server)).

## 2. Cài đặt

### Bước 1: Clone và Cài đặt thư viện
```bash
composer install
```

### Bước 2: Cấu hình môi trường (.env)
Sao chép file `.env.example` thành `.env` và cập nhật các thông số:
- `DB_CONNECTION=sqlite`: Để nguyên nếu dùng SQLite cho dữ liệu nội bộ.
- `DB_SQLSRV_HOST`, `DB_SQLSRV_DATABASE`, `DB_SQLSRV_USERNAME`, `DB_SQLSRV_PASSWORD`: Cấu hình kết nối tới SQL Server (nơi đã restore database `EDU_NUCE`).

### Bước 3: Khởi tạo cơ sở dữ liệu nội bộ
```bash
# Tạo file sqlite trống nếu chưa có
touch database/database.sqlite

# Chạy migration
php artisan migrate

# Seed dữ liệu (Tạo Client ID và tài khoản mẫu)
php artisan db:seed
```

### Bước 4: Cấu hình Swagger (Tùy chọn)
Nếu bạn thay đổi logic API, hãy chạy lệnh sau để cập nhật tài liệu:
```bash
php artisan l5-swagger:generate
```

## 3. Chạy ứng dụng
Hệ thống cần chạy trên cổng **8001** để tương thích với Remedial System:
```bash
php artisan serve --port=8001
```
- **API Documentation**: [http://localhost:8001/api/documentation](http://localhost:8001/api/documentation)
- **Token Endpoint**: `POST /api/token` (Sử dụng Client ID/Secret từ `db:seed`)

## 4. Lưu ý cho SQL Server
Hệ thống sử dụng connection `sqlsrv` để truy vấn dữ liệu từ bảng `DT_SinhVien`, `TKB_MonHoc`,... Đảm bảo database trường đã được restore và tài khoản SQL có quyền SELECT.
