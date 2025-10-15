# ใช้ PHP 8.2 + Apache
FROM php:8.2-apache

# ติดตั้ง extension PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# คัดลอกโค้ด PHP ไปยัง container
COPY . /var/www/html/

# ตั้งสิทธิ์โฟลเดอร์
RUN chown -R www-data:www-data /var/www/html

# expose port 80
EXPOSE 80
