# ใช้ PHP Apache base image
FROM php:8.2-apache

# ติดตั้ง dependencies สำหรับ PostgreSQL + PDO
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# คัดลอกโค้ด PHP ไปยัง container
COPY . /var/www/html/

# ตั้งค่าพอร์ตถ้าจำเป็น
EXPOSE 80
