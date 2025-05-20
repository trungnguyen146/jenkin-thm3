# Sử dụng image PHP chính thức với Apache
FROM php:8.1-apache

# Cài đặt các tiện ích cần thiết (nếu cần)
RUN apt-get update && apt-get install -y \
    && rm -rf /var/lib/apt/lists/*

# Sao chép mã nguồn vào thư mục web của Apache
COPY index.php /var/www/html/
COPY images/ /var/www/html/images/

# Cấu hình Apache để chạy trên port 80
EXPOSE 8080

# Khởi động Apache
CMD ["apache2-foreground"]
