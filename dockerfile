# Sử dụng image PHP chính thức với Apache
FROM php:8.1-apache


RUN sed -i 's/Listen 80/Listen 4444/' /etc/apache2/ports.conf && \
    sed -i 's/:80>/:4444>/' /etc/apache2/sites-enabled/000-default.conf

# Sao chép mã nguồn vào thư mục web của Apache
COPY index.php /var/www/html/
COPY images/ /var/www/html/images/

# Cấu hình Apache để chạy trên port 80
EXPOSE 4444

# Khởi động Apache
CMD ["apache2-foreground"]
