# HUCE International Student Remedial System (ISRS)

Hệ thống đăng ký học phần phụ đạo cho sinh viên quốc tế — gồm backend Laravel, mock University System và frontend React.

## Cấu trúc monorepo

| Thư mục | Mô tả | Cổng mặc định |
|---------|--------|----------------|
| `HUCE-ISRS-BE/remedial_system` | API phụ đạo (Hexagonal) | **8000** |
| `HUCE-ISRS-BE/university_system` | Mock xác thực & dữ liệu trường | **8001** |
| `HUCE-ISRS-FE` | Giao diện React + Vite | **5173** (Vite dev) |

## Chạy nhanh (development)

Mở **3 terminal**:

```powershell
# 1 — University System
cd HUCE-ISRS-BE\university_system
composer install
php artisan migrate --seed
php artisan serve --port=8001

# 2 — Remedial API
cd HUCE-ISRS-BE\remedial_system
composer install
copy .env.example .env   # cấu hình MySQL + UNIVERSITY_BASE_URL=http://localhost:8001
php artisan key:generate
php artisan migrate --seed
php artisan serve

# 3 — Frontend
cd HUCE-ISRS-FE
npm install
npm run dev
```

Truy cập FE: http://localhost:5173 — API: http://127.0.0.1:8000/api

## Tài khoản mẫu

| Vai trò | Đăng nhập | Mật khẩu |
|---------|-----------|----------|
| Admin | `admin@remedial.edu.vn` | `Admin@2024!` |
| Bộ môn | `bokhoa.cntt@remedial.edu.vn` | `BoMon@2024!` |
| Sinh viên | `student_code` (VD: từ University seed) | bằng mã SV |

Sau đăng nhập, FE redirect theo `home_url`: `/admin`, `/department`, `/student`.

## Kiểm thử

- **PHPUnit (BE):** `cd HUCE-ISRS-BE/remedial_system && php artisan test`
- **Regression thủ công (3 role):** xem [REGRESSION.md](./REGRESSION.md)
- **Kiến trúc & API:** [HUCE-ISRS-BE/remedial_system/ARCHITECTURE.md](./HUCE-ISRS-BE/remedial_system/ARCHITECTURE.md)
