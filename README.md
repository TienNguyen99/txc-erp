# TXC ERP

He thong ERP noi bo cho xuong may/nhan mac quy mo nho. Xay dung bang Laravel 12, Blade, Tailwind/Vite va MySQL.

## Module Chinh

- Don hang, tracking lot, lenh san xuat
- Bao cao san xuat theo QR/lenh con
- Nhap/xuat kho, phieu kho in duoc
- Danh muc hang hoa, khach hang, nha cung cap
- BOM/dinh muc NVL, PO mua hang, gia von
- Phan quyen theo vai tro, activity log, thong bao
- Baseline van hanh: backup, restore drill, heartbeat, canh bao ton kho thap

## Yeu Cau

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL >= 8.0

## Cai Dat Local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Voi XAMPP, cau hinh `.env` thuong dung:

```env
APP_URL=http://127.0.0.1:8888
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=texenco_erp
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

## Tai Khoan Mac Dinh Sau Khi Seed

Tat ca tai khoan co mat khau mac dinh la `password`.

| Vai tro | Email | Cong dang nhap |
|---|---|---|
| Admin | admin@txc.vn | Quan tri |
| Manager | manager@txc.vn | Quan tri |
| Staff/Kho | staff@txc.vn | Nhan vien |

Doi mat khau ngay sau khi dua len production.

## Lenh Kiem Tra

```bash
php artisan test
npm run build
php artisan route:list
php artisan ops:check-low-stock
php artisan ops:backup-db
```

## Van Hanh

- Tai lieu van hanh: `docs/OPERATIONS.md`
- SOP vai tro: `docs/SOP_ROLES.md`
- Scheduler production: chay `php artisan schedule:run` moi phut bang cron hoac Windows Task Scheduler.

## Deploy Production

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Dam bao production co:

- `APP_ENV=production`
- `APP_DEBUG=false`
- Backup DB tu dong
- Queue worker hoac scheduler dang chay
- Tai khoan admin da doi mat khau
