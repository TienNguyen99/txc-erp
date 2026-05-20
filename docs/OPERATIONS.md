# Operations Baseline

## Commands
- `php artisan ops:backup-db`: backup DB vao `storage/app/private/backups`.
- `php artisan ops:restore-drill`: kiem tra file backup moi nhat va copy qua thu muc drill.
- `php artisan ops:heartbeat`: gui heartbeat alert de kiem tra scheduler.
- `php artisan ops:check-low-stock`: tao thong bao cho cac ma hang co ton kho duoi muc toi thieu.

## Scheduler
- Daily 01:00: `ops:backup-db`
- Daily 08:00: `ops:check-low-stock`
- Weekly Sunday 02:00: `ops:restore-drill`
- Hourly: `ops:heartbeat`

## Daily Operating Checklist
- 08:00: admin/manager mo dashboard, xem lot tre, WIP, ton kho am/thap.
- Dau ca: kho vao cong Nhan vien, chon Lenh SX, nhap so luong thuc te, he thong tao phieu nhap kho.
- Trong ca: san xuat quet QR lenh con de ghi `sl_dat`, `sl_hu`, ca va ma nhan vien.
- Cuoi ca: doi soat production reports, warehouse documents va cac thong bao ton kho.
- Truoc khi lenh moi: kiem tra BOM, dinh muc NVL, ton kho toi thieu va PO dang mo.

## Alert Channels
- Mail: set `OPS_ALERT_MAIL_TO`
- Telegram: set `OPS_TELEGRAM_BOT_TOKEN`, `OPS_TELEGRAM_CHAT_ID`
- Bat/tat alert: `OPS_ALERT_ENABLED=true|false`

## Production Notes
- Dam bao server co `mysqldump` trong PATH neu dung MySQL.
- Chay scheduler:
  - Linux cron: `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`
  - Windows Task Scheduler: run `php artisan schedule:run` moi phut.
