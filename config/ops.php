<?php

return [
    'alert' => [
        'mail_to' => env('OPS_ALERT_MAIL_TO'),
        'telegram_bot_token' => env('OPS_TELEGRAM_BOT_TOKEN'),
        'telegram_chat_id' => env('OPS_TELEGRAM_CHAT_ID'),
        'enabled' => (bool) env('OPS_ALERT_ENABLED', false),
    ],
    'backup' => [
        'path' => env('OPS_BACKUP_PATH', storage_path('app/private/backups')),
        'retention_days' => (int) env('OPS_BACKUP_RETENTION_DAYS', 14),
        'restore_drill_path' => env('OPS_RESTORE_DRILL_PATH', storage_path('app/private/backups/restore-drill')),
        'mysqldump_bin' => env('OPS_MYSQLDUMP_BIN'),
    ],
];
