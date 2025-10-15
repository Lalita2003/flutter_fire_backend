# ใช้ PHP 8.2 Apache base image
FROM php:8.2-apache

# ติดตั้ง dependencies สำหรับ PostgreSQL และ PDO
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ตั้ง environment variables ของ Neon PostgreSQL
# Render จะ override ตัวแปรเหล่านี้จาก Environment Variables ของ service
ENV DB_HOST=localhost
ENV DB_PORT=5432
ENV DB_NAME=neondb
ENV DB_USER=neondb_owner
ENV DB_PASS=your_password

# คัดลอกโค้ด PHP ไปยัง container
COPY . /var/www/html/

# ตั้งค่า Apache ให้รองรับ .htaccess (ถ้าจำเป็น)
RUN a2enmod rewrite

# ตั้ง permission สำหรับ log และ upload folder (ถ้ามี)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# คำสั่งเริ่มต้น Apache
CMD ["apache2-foreground"]
