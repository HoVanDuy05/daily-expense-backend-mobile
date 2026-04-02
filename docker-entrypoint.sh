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

# Làm sạch route cache để Render luôn nhận diện route mới
echo "⚡ Khởi tạo cấu hình sạch cho môi trường sản phẩm..."

# Tự động chạy Migration để cập nhật database (Supabase PG)
echo "🐘 Đang chạy database migrations..."
php artisan migrate --force

# Chạy server chính thức với Artisan Serve
# Artisan Serve tích hợp Router của Laravel, giúp xử lý đường dẫn URL tốt hơn hẳn php -S
PORT="${PORT:-10000}" 
echo "🚀 Ứng dụng quản lý tài chính đang cất cánh tại port $PORT..."

php artisan serve --host=0.0.0.0 --port=$PORT
