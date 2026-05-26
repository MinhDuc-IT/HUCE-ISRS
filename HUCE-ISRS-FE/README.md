# HUCE-ISRS Frontend

Giao diện React + TypeScript + Vite cho hệ thống đăng ký phụ đạo. Ba vai trò: **admin**, **department** (API `bo_mon`), **student** (`sinh_vien`).

## Yêu cầu

- Node.js 18+
- Remedial API chạy tại `http://127.0.0.1:8000` (xem `src/shared/utils/apiClient.ts`)
- University System tại `http://localhost:8001` khi đăng nhập sinh viên

## Cài đặt & chạy

```bash
npm install
npm run dev
```

Mở http://localhost:5173

Build production:

```bash
npm run build
npm run preview
```

## Định tuyến theo vai trò

| Role API | Trang chính |
|----------|-------------|
| `admin` | `/admin/*` — đợt phụ đạo, người dùng, bộ môn, cấu hình, tra cứu đăng ký |
| `bo_mon` | `/department/*` — hồ sơ BM, danh sách đăng ký, cập nhật GV |
| `sinh_vien` | `/student/*` — đăng ký môn, danh sách đã đăng ký, GV phụ đạo |

Login map `home_url` từ API (`authUserMapper.ts`, `rolePaths.ts`).

## API

- Base URL: `http://127.0.0.1:8000/api`
- Payload/response: **snake_case** (không fallback PascalCase)
- Token: Bearer từ `POST /auth/login`, gửi qua `apiFetch`

Bảng endpoint đầy đủ: [../HUCE-ISRS-BE/remedial_system/ARCHITECTURE.md](../HUCE-ISRS-BE/remedial_system/ARCHITECTURE.md)

## Regression

Checklist 3 vai trò: [../REGRESSION.md](../REGRESSION.md)
