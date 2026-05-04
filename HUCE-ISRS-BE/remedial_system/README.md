# Remedial Registration System (BE)

Hệ thống Đăng ký Học phần Phụ đạo (Remedial System) cho phép sinh viên đăng ký học bổ sung các môn bị nợ/hỏng dựa trên dữ liệu đồng bộ từ University System. Dự án được phát triển theo mô hình **Clean Architecture (Hexagonal)** để đảm bảo tính linh hoạt và dễ bảo trì.

## 1. Yêu cầu hệ thống
- **PHP**: >= 8.2
- **Composer**: >= 2.0
- **Database**: MySQL >= 8.0
- **University System**: Phải đang chạy (thường ở cổng 8001).
- **Extension**: `gd` (cần thiết cho việc xuất báo cáo Excel).

## 2. Kiến trúc dự án
Dự án được tái cấu trúc theo mô hình **Clean Architecture**:
- **Domain Layer**: Chứa các Entity, Value Object, Interface (Port) và Logic nghiệp vụ cốt lõi. Không phụ thuộc vào Laravel/Eloquent.
- **Application Layer**: Chứa các Use Case (Services), điều phối logic giữa Domain và Infrastructure.
- **Infrastructure Layer**: Chứa các cài đặt cụ thể cho Database (Eloquent Repositories), API Adapters (University System Client).
- **Presentation Layer**: Laravel Controllers và Request Validation.

## 3. Cài đặt

### Bước 1: Cài đặt thư viện
Nếu gặp lỗi về phiên bản PHP hoặc thiếu extension GD trong môi trường phát triển:
```bash
composer install --ignore-platform-reqs
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

# Seed dữ liệu (Cấu hình hệ thống, Mail template, Admin)
php artisan db:seed --class=SystemConfigSeeder
php artisan db:seed
```

### Bước 4: Tạo tài liệu API (Swagger)
```bash
php artisan l5-swagger:generate
```

## 4. Chạy ứng dụng
```bash
php artisan serve
```
- **Hệ thống**: [http://localhost:8000](http://localhost:8000)
- **API Documentation**: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

## 5. Luồng hoạt động chính
1. **Xác thực**: Sinh viên đăng nhập bằng `Mã sinh viên` và `Mật khẩu trường`. Hệ thống sẽ gọi sang University System để xác thực.
2. **Provisioning**: Lần đầu đăng nhập thành công, hệ thống tự động đồng bộ thông tin sinh viên và danh sách môn học từ trường về database nội bộ.
3. **Cấu hình Động**: Các thông tin như sĩ số tối đa, tiêu đề email thông báo được quản lý linh hoạt trong bảng `SystemConfig`.
4. **Đăng ký**: Sinh viên đăng ký môn học dựa trên danh sách môn nợ đã được đồng bộ.

## 6. Các lệnh hữu ích
- `php artisan migrate:fresh --seed`: Làm mới toàn bộ database và dữ liệu mẫu.
- `php artisan cache:clear`: Xóa cache (bao gồm cả cache token của University System).
