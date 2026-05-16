# Operations Baseline

## Commands
- `php artisan ops:backup-db`: backup DB vao `storage/app/private/backups`.
- `php artisan ops:restore-drill`: kiem tra file backup moi nhat va copy qua thu muc drill.
- `php artisan ops:heartbeat`: gui heartbeat alert de kiem tra scheduler.

## Scheduler
- Daily 01:00: `ops:backup-db`
- Weekly Sunday 02:00: `ops:restore-drill`
- Hourly: `ops:heartbeat`

## Alert Channels
- Mail: set `OPS_ALERT_MAIL_TO`
- Telegram: set `OPS_TELEGRAM_BOT_TOKEN`, `OPS_TELEGRAM_CHAT_ID`
- Bat/tat alert: `OPS_ALERT_ENABLED=true|false`

## Production Notes
- Dam bao server co `mysqldump` trong PATH neu dung MySQL.
- Chay scheduler:
  - Linux cron: `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`
  - Windows Task Scheduler: run `php artisan schedule:run` moi phut.
