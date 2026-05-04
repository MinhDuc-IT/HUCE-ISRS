# Remedial Registration System

Hệ thống Đăng ký Học phần Phụ đạo (Remedial System) cho phép sinh viên đăng ký học bổ sung các môn bị nợ/hỏng dựa trên dữ liệu đồng bộ từ University System.

## 1. Yêu cầu hệ thống
- **PHP**: >= 8.2
- **Composer**: >= 2.0
- **Database**: MySQL >= 8.0
- **University System**: Phải đang chạy (thường ở cổng 8001).

## 2. Cài đặt

### Bước 1: Cài đặt thư viện
```bash
composer install
```

### Bước 2: Cấu hình môi trường (.env)
1. Sao chép `.env.example` thành `.env`.
2. Cấu hình Database MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=remedial_db
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```
3. Cấu hình kết nối University System:
   ```env
   UNIVERSITY_BASE_URL=http://localhost:8001
   UNIVERSITY_CLIENT_ID=remedial_system
   UNIVERSITY_CLIENT_SECRET=remedial_secret_2024
   ```

### Bước 3: Khởi tạo ứng dụng
```bash
# Tạo key ứng dụng
php artisan key:generate

# Chạy Migration để tạo cấu trúc bảng (PascalCase)
php artisan migrate

# Seed dữ liệu (Tạo các đợt phụ đạo mẫu và Admin)
php artisan db:seed
```

### Bước 4: Tạo tài liệu API (Swagger)
```bash
php artisan l5-swagger:generate
```

## 3. Chạy ứng dụng
```bash
php artisan serve
```
- **Hệ thống**: [http://localhost:8000](http://localhost:8000)
- **API Documentation**: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

## 4. Luồng hoạt động chính
1. **Xác thực**: Sinh viên đăng nhập bằng `Mã sinh viên` và `Mật khẩu trường`. Hệ thống sẽ gọi sang University System để xác thực.
2. **Provisioning**: Lần đầu đăng nhập thành công, hệ thống tự động đồng bộ thông tin sinh viên và danh sách môn học từ trường về database nội bộ.
3. **Đăng ký**: Sinh viên đăng ký môn học dựa trên danh sách môn nợ đã được đồng bộ.

## 5. Các lệnh hữu ích
- `php artisan migrate:fresh --seed`: Làm mới toàn bộ database và dữ liệu mẫu.
- `php artisan cache:clear`: Xóa cache (bao gồm cả cache token của University System).
