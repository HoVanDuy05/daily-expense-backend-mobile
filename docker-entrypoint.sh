#!/bin/sh

# Đảm bảo app key đã có mặt (fallback nếu ko có trong Env)
if [ -z "$APP_KEY" ]; then
    echo "LỖI: Chưa có APP_KEY. Đang tạo tạm thời..."
    php artisan key:generate --show
fi

# Làm sạch và reset cache để bắt kịp thay đổi mới nhất từ GitHub
echo "🧹 Đang làm sạch cache..."
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Tối ưu hóa cấu hình cho Production (Render tốc độ cao)
echo "⚡ Đang tối ưu hóa cấu hình..."
php artisan config:cache
php artisan route:cache

# Tự động chạy Migration để cập nhật database (Supabase PG)
echo "🐘 Đang chạy database migrations..."
php artisan migrate --force

# Chạy server chính thức
# Sử dụng HOST 0.0.0.0 là QUAN TRỌNG để Render có thể kết nối từ bên ngoài
PORT="${PORT:-10000}" 
echo "🚀 Ứng dụng đang cất cánh tại port $PORT..."

# Sử dụng PHP server trực tiếp từ folder 'public' (Chuẩn bài Laravel)
php -S 0.0.0.0:$PORT -t public
