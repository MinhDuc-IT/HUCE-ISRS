# 🚀 Hướng dẫn Deploy – HUCE-ISRS

Tài liệu này mô tả toàn bộ quy trình deploy hệ thống HUCE-ISRS lên môi trường production.

## Kiến trúc production

```
Internet
   │
   ├──► Cloudflare Pages (FE – MIỄN PHÍ)
   │       React 19 + Vite · Static CDN toàn cầu
   │       URL: https://huce-isrs-fe.pages.dev
   │
   ├──► EC2 t3.small · remedial_system (~$17/tháng)
   │       Laravel 12 + MySQL 8 + Redis 7
   │       URL: http://remedial-huce.duckdns.org
   │       Containers: nginx · app · queue · scheduler · mysql · redis
   │
   └──► EC2 t3.large · university_system (~$60/tháng)
           Laravel 12 + SQL Server 2022 (DB: EDU_NUCE ~50GB)
           URL: http://university-huce.duckdns.org
           Containers: nginx · app · sqlserver
```

```
FE → remedial_system:80 → university_system:80 → SQL Server:1433
```

---

## Cấu trúc files deploy

```
HUCE-ISRS/
├── .github/workflows/
│   ├── ci.yml                    ← CI: test PHPUnit + lint FE (mọi PR)
│   ├── deploy-fe.yml             ← CD: FE → Cloudflare Pages
│   ├── deploy-remedial.yml       ← CD: remedial_system → EC2 t3.small
│   └── deploy-university.yml     ← CD: university_system → EC2 t3.large
│
├── HUCE-ISRS-FE/
│   ├── Dockerfile                ← Multi-stage: Node 20 build → Nginx Alpine
│   ├── nginx.conf                ← Nginx SPA (React Router support)
│   └── .env.production.example  ← Template biến môi trường FE
│
└── HUCE-ISRS-BE/
    ├── remedial_system/
    │   ├── Dockerfile
    │   ├── docker-compose.yml
    │   ├── .env.production.example
    │   └── docker/
    │       ├── php/entrypoint.sh
    │       ├── php/php-fpm.conf
    │       ├── php/opcache.ini
    │       └── nginx/nginx.conf
    └── university_system/
        ├── Dockerfile            ← Dùng Debian (cần glibc cho msodbcsql18)
        ├── docker-compose.yml
        ├── .env.production.example
        └── docker/
            ├── php/entrypoint.sh
            ├── php/php-fpm.conf
            ├── php/opcache.ini
            ├── nginx/nginx.conf
            └── sqlserver/restore.sh   ← Restore file .bak 50GB (chạy 1 lần)
```

---

## Phần 1 – Chuẩn bị (Làm 1 lần)

### 1.1 Cấu hình Duck DNS (Domain miễn phí)

1. Đăng ký tại [https://www.duckdns.org](https://www.duckdns.org)
2. Tạo **2 subdomain**:
   - `remedial-huce.duckdns.org` → IP của EC2 remedial
   - `university-huce.duckdns.org` → IP của EC2 university
3. **Cài auto-update IP** trên mỗi EC2 (IP thay đổi khi restart instance):
   ```bash
   # Thay YOUR_TOKEN và YOUR_SUBDOMAIN
   (crontab -l 2>/dev/null; echo "*/5 * * * * curl -s 'https://www.duckdns.org/update?domains=remedial-huce&token=YOUR_TOKEN&ip=' > /dev/null") | crontab -
   ```

### 1.2 Cấu hình GitHub Secrets

Vào **GitHub Repo → Settings → Secrets and variables → Actions** → thêm các secrets:

#### Secrets dùng chung
| Secret | Mô tả |
|---|---|
| `GHCR_TOKEN` | GitHub Personal Access Token với quyền `write:packages, read:packages` |

#### Secrets cho deploy FE (Cloudflare Pages)
| Secret | Mô tả | Lấy từ đâu |
|---|---|---|
| `CLOUDFLARE_API_TOKEN` | API Token Cloudflare | Dashboard → My Profile → API Tokens |
| `CLOUDFLARE_ACCOUNT_ID` | Account ID Cloudflare | Dashboard → bên phải trang |
| `VITE_API_BASE_URL` | URL API remedial_system | `http://remedial-huce.duckdns.org/api` |

#### Secrets cho deploy remedial_system
| Secret | Mô tả |
|---|---|
| `REMEDIAL_EC2_HOST` | IP hoặc `remedial-huce.duckdns.org` |
| `REMEDIAL_EC2_USER` | `ubuntu` (Amazon Linux: `ec2-user`) |
| `REMEDIAL_EC2_SSH_KEY` | Toàn bộ nội dung file `.pem` (kể cả `-----BEGIN...`) |
| `REMEDIAL_EC2_PORT` | `22` |

#### Secrets cho deploy university_system
| Secret | Mô tả |
|---|---|
| `UNIVERSITY_EC2_HOST` | IP hoặc `university-huce.duckdns.org` |
| `UNIVERSITY_EC2_USER` | `ubuntu` |
| `UNIVERSITY_EC2_SSH_KEY` | Toàn bộ nội dung file `.pem` |
| `UNIVERSITY_EC2_PORT` | `22` |

### 1.3 Tạo Cloudflare Pages project

```bash
# Cài Wrangler CLI
npm install -g wrangler

# Đăng nhập Cloudflare
wrangler login

# Tạo project (chỉ 1 lần)
wrangler pages project create huce-isrs-fe
```

---

## Phần 2 – Setup EC2 Remedial (t3.small)

### 2.1 Cài đặt môi trường

```bash
# SSH vào EC2
ssh -i your-key.pem ubuntu@remedial-huce.duckdns.org

# Cài Docker + Docker Compose Plugin
sudo apt update && sudo apt install -y docker.io docker-compose-plugin
sudo usermod -aG docker ubuntu
newgrp docker   # Áp dụng ngay mà không cần logout

# Kiểm tra
docker --version && docker compose version
```

### 2.2 Cấu hình .env production

```bash
mkdir -p ~/remedial_system
cd ~/remedial_system

# Tạo file .env.production từ template
# (Copy nội dung .env.production.example và điền giá trị thật)
nano .env.production
```

**Nội dung `.env.production`** (dựa theo `.env.production.example`):
```env
APP_URL=http://remedial-huce.duckdns.org
APP_KEY=base64:...   # Sẽ tự gen ở lần deploy đầu

DB_DATABASE=remedial_db
DB_USERNAME=remedial_user
DB_PASSWORD=<mật_khẩu_mạnh>
DB_ROOT_PASSWORD=<mật_khẩu_root_mạnh>

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=teamwork28032025@gmail.com
MAIL_PASSWORD=xqmcztubhjotpdct
MAIL_FROM_ADDRESS=teamwork28032025@gmail.com

UNIVERSITY_BASE_URL=http://university-huce.duckdns.org
UNIVERSITY_CLIENT_ID=remedial_system
UNIVERSITY_CLIENT_SECRET=remedial_secret_2024

GITHUB_REPOSITORY=<your-github-org>/huce-isrs
IMAGE_TAG=latest
```

### 2.3 Deploy lần đầu

```bash
# Copy docker-compose.yml lên server (hoặc để CI/CD tự làm)
scp -i your-key.pem \
  HUCE-ISRS-BE/remedial_system/docker-compose.yml \
  ubuntu@remedial-huce.duckdns.org:~/remedial_system/

# Đăng nhập GHCR
echo "GHCR_TOKEN" | docker login ghcr.io -u YOUR_GITHUB_USERNAME --password-stdin

# Pull và start tất cả services
cd ~/remedial_system
docker compose --env-file .env.production up -d

# Chờ MySQL healthcheck xanh (~30s) rồi kiểm tra
docker compose --env-file .env.production ps

# Seed dữ liệu ban đầu (admin, departments...)
docker exec remedial_app php artisan db:seed

# Kiểm tra log
docker compose --env-file .env.production logs -f app
```

---

## Phần 3 – Setup EC2 University (t3.large + SQL Server)

> ⚠️ **Quan trọng**: EC2 t3.large cần **ít nhất 4GB RAM** cho SQL Server. t3.small sẽ không đủ.

### 3.1 Cài đặt môi trường + Mount EBS

```bash
# SSH vào EC2
ssh -i your-key.pem ubuntu@university-huce.duckdns.org

# Cài Docker
sudo apt update && sudo apt install -y docker.io docker-compose-plugin
sudo usermod -aG docker ubuntu
newgrp docker

# Tạo và mount EBS Volume 100GB cho SQL Server data (làm trên AWS Console trước)
# Sau khi attach EBS vào EC2:
sudo mkfs -t ext4 /dev/nvme1n1          # Format lần đầu
sudo mkdir -p /mnt/ebs-sqlserver/data
sudo mkdir -p /mnt/ebs-sqlserver/backup
sudo mount /dev/nvme1n1 /mnt/ebs-sqlserver
sudo chown -R ubuntu:ubuntu /mnt/ebs-sqlserver

# Auto-mount sau reboot
echo "/dev/nvme1n1 /mnt/ebs-sqlserver ext4 defaults,nofail 0 2" | sudo tee -a /etc/fstab
```

### 3.2 Upload file .bak SQL Server

File `.bak` (~50GB): `EDU_NUCE_backup_2024_11_08_000002_2493223.bak`

```bash
# Cách 1: Upload trực tiếp qua SCP (chậm nếu upload từ máy local)
scp -i your-key.pem \
  "HUCE-ISRS-BE/university_system/EDU_NUCE_backup_2024_11_08_000002_2493223.bak" \
  ubuntu@university-huce.duckdns.org:/mnt/ebs-sqlserver/backup/

# Cách 2 (khuyến nghị – nhanh hơn): Upload lên S3 trước, rồi download từ EC2
# Trên máy local:
aws s3 cp "EDU_NUCE_backup_2024_11_08_000002_2493223.bak" s3://your-bucket/backup/
# Trên EC2:
aws s3 cp s3://your-bucket/backup/EDU_NUCE_backup_2024_11_08_000002_2493223.bak \
  /mnt/ebs-sqlserver/backup/
```

### 3.3 Cấu hình .env production

```bash
mkdir -p ~/university_system
cd ~/university_system
nano .env.production
```

**Nội dung `.env.production`**:
```env
APP_URL=http://university-huce.duckdns.org
APP_KEY=base64:...

SA_PASSWORD=<mật_khẩu_SA_mạnh>
DB_SQLSRV_USERNAME=laravel_uni
DB_SQLSRV_PASSWORD=<mật_khẩu_laravel_mạnh>

UNIVERSITY_BACKDOOR_PASS=nuce_backdoor_2026

GITHUB_REPOSITORY=<your-github-org>/huce-isrs
IMAGE_TAG=latest
```

### 3.4 Restore SQL Server (Chỉ làm 1 lần)

```bash
# Copy files cần thiết lên server
scp -i your-key.pem \
  HUCE-ISRS-BE/university_system/docker-compose.yml \
  ubuntu@university-huce.duckdns.org:~/university_system/

# Đăng nhập GHCR và khởi động SQL Server trước
echo "GHCR_TOKEN" | docker login ghcr.io -u YOUR_GITHUB_USERNAME --password-stdin

docker compose --env-file .env.production up -d sqlserver

# Chờ SQL Server khởi động xong (~60 giây)
docker compose --env-file .env.production ps
# Đợi sqlserver STATUS = "healthy"

# ⏳ RESTORE DATABASE (mất 10-30 phút với file 50GB)
SA_PASS=$(grep SA_PASSWORD .env.production | cut -d= -f2)
docker exec \
  -e SA_PASSWORD="$SA_PASS" \
  university_sqlserver \
  bash /restore.sh

# Kiểm tra restore thành công
docker exec university_sqlserver \
  /opt/mssql-tools18/bin/sqlcmd \
  -S localhost -U sa -P "$SA_PASS" \
  -C -Q "SELECT name FROM sys.databases WHERE name = 'EDU_NUCE'"
```

### 3.5 Start toàn bộ university_system

```bash
docker compose --env-file .env.production up -d

# Kiểm tra
docker compose --env-file .env.production ps
docker compose --env-file .env.production logs -f app
```

---

## Phần 4 – Luồng CI/CD tự động

Sau khi setup xong, mọi push vào branch `main` sẽ tự động deploy:

```
Developer: git push origin main
                │
                ├── ci.yml chạy trước (test + lint)
                │       PHPUnit (remedial + university) + ESLint/Build FE
                │       ❌ Fail → Dừng, không deploy
                │
                ├── Phát hiện thay đổi HUCE-ISRS-FE/**
                │       deploy-fe.yml: npm build → Cloudflare Pages (~2 phút)
                │
                ├── Phát hiện thay đổi remedial_system/**
                │       deploy-remedial.yml:
                │         1. Build Docker image (PHP-FPM Alpine)
                │         2. Push lên GHCR (GitHub Container Registry)
                │         3. SSH vào EC2 t3.small
                │         4. docker compose pull app queue scheduler
                │         5. docker compose up --no-deps app queue scheduler nginx
                │         (MySQL + Redis KHÔNG bị restart)
                │
                └── Phát hiện thay đổi university_system/**
                        deploy-university.yml:
                          1. Build Docker image (PHP-FPM Debian + msodbcsql18)
                             ⚠️ Lần đầu ~10 phút vì cài MS ODBC Driver
                          2. Push lên GHCR
                          3. SSH vào EC2 t3.large
                          4. docker compose pull app
                          5. docker compose up --no-deps app nginx
                          (SQL Server KHÔNG bị restart - tránh downtime DB)
```

---

## Phần 5 – Xử lý sự cố thường gặp

### 5.1 Token University System bị hết hạn
```bash
# Xóa cached token trên EC2 remedial
docker exec remedial_app php artisan cache:forget university_auth:access_token
```

### 5.2 Migration bị lỗi khi deploy
```bash
# SSH vào EC2, chạy thủ công
docker exec remedial_app php artisan migrate --force
```

### 5.3 Xem logs realtime
```bash
# Trên EC2 remedial
docker compose --env-file .env.production logs -f app
docker compose --env-file .env.production logs -f queue

# Trên EC2 university
docker compose --env-file .env.production logs -f app
docker compose --env-file .env.production logs -f sqlserver
```

### 5.4 Restart service đơn lẻ (không restart toàn bộ)
```bash
docker compose --env-file .env.production restart app
docker compose --env-file .env.production restart nginx
```

### 5.5 SQL Server container bị OOM (Out of Memory)
- Kiểm tra: `docker stats university_sqlserver`
- Nếu RAM < 4GB còn trống → nâng instance lên t3.xlarge
- Hoặc giảm `MSSQL_MEMORY_LIMIT_MB` trong `docker-compose.yml`

### 5.6 Rollback về phiên bản cũ
```bash
# Xem danh sách images đã có
docker images ghcr.io/your-org/remedial-system

# Rollback về commit cụ thể
export IMAGE_TAG=<git-sha-cũ>
docker compose --env-file .env.production up --no-deps -d app
```

---

## Phần 6 – Checklist Deploy lần đầu

- [ ] Tạo 2 Duck DNS subdomains
- [ ] Tạo 2 EC2 instances (t3.small + t3.large) với Ubuntu 22.04
- [ ] Tạo EBS Volume 100GB và attach vào EC2 university
- [ ] Cài Docker trên cả 2 EC2
- [ ] Cấu hình tất cả GitHub Secrets (9 secrets)
- [ ] Tạo Cloudflare Pages project `huce-isrs-fe`
- [ ] Upload file `.bak` lên EC2 university
- [ ] Deploy lần đầu bằng cách push lên `main` hoặc trigger `workflow_dispatch`
- [ ] Restore SQL Server (chạy `restore.sh`)
- [ ] Seed database remedial (`php artisan db:seed`)
- [ ] Kiểm tra end-to-end: FE login → API remedial → API university → SQL Server
