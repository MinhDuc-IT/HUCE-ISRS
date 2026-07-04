#!/bin/bash
# ════════════════════════════════════════════════════════════
# Script restore SQL Server database từ file .bak
# Chạy MỘT LẦN duy nhất khi setup lần đầu trên EC2
#
# Cách dùng:
#   chmod +x docker/sqlserver/restore.sh
#   ./docker/sqlserver/restore.sh
# ════════════════════════════════════════════════════════════
set -e

BAK_FILE="${1:-/var/opt/mssql/backup/EDU_NUCE_backup_2024_11_08_000002_2493223.bak}"
SA_PASSWORD="${SA_PASSWORD:-M@tKhauManh2026!}"
DB_NAME="EDU_NUCE"

echo "[restore] Chờ SQL Server khởi động..."
for i in $(seq 1 30); do
    if /opt/mssql-tools18/bin/sqlcmd \
        -S localhost -U sa -P "$SA_PASSWORD" \
        -C -Q "SELECT 1" &>/dev/null; then
        echo "[restore] SQL Server sẵn sàng."
        break
    fi
    echo "[restore] Đang chờ... ($i/30)"
    sleep 3
done

# Kiểm tra DB đã tồn tại chưa
DB_EXISTS=$(/opt/mssql-tools18/bin/sqlcmd \
    -S localhost -U sa -P "$SA_PASSWORD" \
    -C -Q "SET NOCOUNT ON; SELECT COUNT(*) FROM sys.databases WHERE name='$DB_NAME'" \
    -h -1 2>/dev/null | tr -d ' \r\n')

if [ "$DB_EXISTS" = "1" ]; then
    echo "[restore] Database $DB_NAME đã tồn tại. Bỏ qua restore."
    exit 0
fi

echo "[restore] Bắt đầu restore $DB_NAME từ $BAK_FILE..."
echo "[restore] File size: $(du -sh "$BAK_FILE" 2>/dev/null | cut -f1)"
echo "[restore] Quá trình này mất 10-30 phút với file 50GB..."

/opt/mssql-tools18/bin/sqlcmd \
    -S localhost -U sa -P "$SA_PASSWORD" \
    -C -Q "
RESTORE DATABASE [$DB_NAME]
FROM DISK = N'$BAK_FILE'
WITH
    MOVE N'EDU_NUCE'     TO N'/var/opt/mssql/data/EDU_NUCE.mdf',
    MOVE N'EDU_NUCE_log' TO N'/var/opt/mssql/data/EDU_NUCE_log.ldf',
    REPLACE,
    STATS = 5
" 2>&1

echo "[restore] ✅ Restore hoàn tất!"

# Tạo login cho Laravel nếu chưa có
/opt/mssql-tools18/bin/sqlcmd \
    -S localhost -U sa -P "$SA_PASSWORD" \
    -C -Q "
IF NOT EXISTS (SELECT * FROM sys.server_principals WHERE name = 'laravel_uni')
BEGIN
    CREATE LOGIN laravel_uni WITH PASSWORD = '${DB_LARAVEL_PASSWORD:-Laravel@2024!}';
    USE [$DB_NAME];
    CREATE USER laravel_uni FOR LOGIN laravel_uni;
    ALTER ROLE db_datareader ADD MEMBER laravel_uni;
    PRINT 'Login laravel_uni đã được tạo.';
END
" 2>&1

echo "[restore] ✅ Hoàn tất setup database!"
