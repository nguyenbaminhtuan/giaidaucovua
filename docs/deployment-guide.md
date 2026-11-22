# Deployment Guide

## 1. Yêu cầu môi trường (Server Requirements)
- PHP >= 8.3
- Tiện ích mở rộng PHP: `pdo`, `pdo_sqlite` (hoặc `pdo_mysql`), `mbstring`, `xml`, `bcmath`, `tokenizer`, `ctype`.
- Composer >= 2.5
- Node.js >= 20.x và npm >= 10.x

## 2. Hướng dẫn chạy môi trường phát triển (Local Development)
```bash
# 1. Cài đặt các gói phụ thuộc PHP
composer install

# 2. Cấu hình biến môi trường
cp .env.example .env
php artisan key:generate

# 3. Chạy migrations tạo cơ sở dữ liệu
php artisan migrate

# 4. Cài đặt các gói phụ thuộc Node và build frontend
npm install
npm run build # hoặc npm run dev khi đang code

# 5. Khởi chạy máy chủ nội bộ
php artisan serve
```

Truy cập ứng dụng tại `http://localhost:8000`.
