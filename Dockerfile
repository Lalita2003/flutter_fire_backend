# ใช้ PHP 8.2 Apache base image
FROM php:8.2-apache

# ติดตั้ง dependencies สำหรับ PostgreSQL และ PDO
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# เปิด mod_rewrite และตั้งค่า AllowOverride
RUN a2enmod rewrite

# ตั้ง environment variables ของ Neon PostgreSQL
ENV DB_HOST=localhost
ENV DB_PORT=5432
ENV DB_NAME=neondb
ENV DB_USER=neondb_owner
ENV DB_PASS=your_password

# คัดลอกโค้ด PHP ไปยัง container
COPY . /var/www/html/

# ตั้ง permission ให้ Apache อ่านและเขียนได้
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# สร้างไฟล์ Apache config ใหม่ เพื่อ AllowOverride และ Require all granted
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/allow-html.conf \
    && a2enconf allow-html

# Expose port 80
EXPOSE 80

# คำสั่งเริ่มต้น Apache
CMD ["apache2-foreground"]
