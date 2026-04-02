#!/bin/sh

# Đợi DB kết nối (Tùy chọn nếu cần, Render thường quản lý SQL riêng)
echo "Đang cấu hình Laravel cho sản phẩm..."

# Cache cấu hình và routes cho hiệu năng cao nhất
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Tự động chạy Migration để cập nhật database mới nhất
echo "Đang chạy database migrations..."
php artisan migrate --force

# Chạy server Laravel cho Render
# PORT là biến Render tự động cấp phát, mặc định 8080 nếu không có
PORT="${PORT:-8080}"
echo "Ứng dụng đang khởi chạy trên port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT
