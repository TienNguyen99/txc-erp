# TXC ERP

Phần mềm quản lý nội bộ dành cho công ty TXC. Xây dựng bằng Laravel 12.

---

## Tính năng

- Quản lý hàng hóa / danh mục sản phẩm
- Quản lý nhập xuất kho
- Quản lý công nợ
- Báo cáo & xuất Excel
- Phân quyền theo vai trò (Role-based)
- Lịch sử thao tác (Activity Log)

> Cập nhật danh sách này theo đúng module bạn đã làm thực tế.

---

## Tech Stack

| Layer | Công nghệ |
|---|---|
| Backend | Laravel 12, PHP 8.2 |
| Frontend | Blade, Tailwind CSS, Vite |
| Database | MySQL |
| Auth & Phân quyền | Laravel Breeze, Spatie Permission |
| Audit Log | Spatie Activity Log |
| Export | Maatwebsite Excel |

---

## Yêu cầu môi trường

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL >= 8.0

---

## Cài đặt

```bash
# 1. Clone repo
git clone https://github.com/TienNguyen99/txc-erp.git
cd txc-erp

# 2. Cài dependencies
composer install
npm install

# 3. Tạo file môi trường
cp .env.example .env
php artisan key:generate

# 4. Cấu hình database trong .env
# DB_DATABASE=txc_erp
# DB_USERNAME=root
# DB_PASSWORD=...

# 5. Migrate & seed
php artisan migrate --seed

# 6. Build frontend
npm run build

# 7. Chạy local
php artisan serve
```

Hoặc dùng lệnh tắt đã cấu hình sẵn:

```bash
composer run setup   # cài đặt toàn bộ
composer run dev     # chạy dev server (Laravel + Vite + Queue + Log)
```

---

## Tài khoản mặc định (sau khi seed)

| Role | Email | Password |
|---|---|---|
| Admin | admin@txc.vn | password |

> Đổi mật khẩu ngay sau khi deploy lên production.

---

## Cấu trúc thư mục chính

```
app/
├── Http/Controllers/   # Xử lý request
├── Models/             # Eloquent models
└── ...

resources/views/        # Blade templates
database/migrations/    # Lịch sử cấu trúc DB
routes/web.php          # Định nghĩa routes
```

---

## Deploy production

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Đảm bảo file `.env` trên server có `APP_ENV=production` và `APP_DEBUG=false`.

---

## Liên hệ

Dự án nội bộ — mọi thắc mắc liên hệ **Tiến Nguyễn** (dev).
