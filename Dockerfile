# Sử dụng PHP 8.2 FPM làm base image
FROM php:8.2-fpm

# Cài đặt các system dependencies cần thiết
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libjpeg-dev \
    libfreetype6-dev

# Xoá cache apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài đặt PHP extensions cần thiết cho Laravel & PostgreSQL
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Lấy Composer bản mới nhất
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Copy toàn bộ code dự án vào container
COPY . /var/www

# Cài đặt PHP dependencies bằng composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Cấp quyền cho thư mục storage & bootstrap/cache (Rất quan trọng trên Render)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port (Render sẽ tự động ghi đè qua biến $PORT)
EXPOSE 8080

# Chạy lệnh khởi động app
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
