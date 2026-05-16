<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use App\Support\OpsAlert;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('activitylog:clean')->daily();
Schedule::command('ops:backup-db')->dailyAt('01:00');
Schedule::command('ops:restore-drill')->weeklyOn(0, '02:00');
Schedule::command('ops:heartbeat')->hourly();

Artisan::command('ops:heartbeat', function () {
    $this->info('ERP heartbeat OK at ' . now()->toDateTimeString());
    OpsAlert::send('Heartbeat', 'ERP scheduled heartbeat is running.');
})->purpose('Send hourly heartbeat for monitoring');

Artisan::command('ops:backup-db', function () {
    $driver = config('database.default');
    $backupDir = config('ops.backup.path');
    $retentionDays = config('ops.backup.retention_days', 14);

    File::ensureDirectoryExists($backupDir);
    $timestamp = now()->format('Ymd_His');

    if ($driver === 'sqlite') {
        $dbFile = config('database.connections.sqlite.database');
        $target = $backupDir . DIRECTORY_SEPARATOR . "db_{$timestamp}.sqlite";
        File::copy($dbFile, $target);
        $this->info("SQLite backup created: {$target}");
    } else {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $db = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $mysqldumpBin = config('ops.backup.mysqldump_bin');
        if (empty($mysqldumpBin)) {
            $defaultXampp = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
            $mysqldumpBin = File::exists($defaultXampp) ? $defaultXampp : 'mysqldump';
        }

        $target = $backupDir . DIRECTORY_SEPARATOR . "db_{$timestamp}.sql";

        $result = Process::run([
            $mysqldumpBin,
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
            "--password={$password}",
            (string) $db,
            "--result-file={$target}",
        ]);
        if (!$result->successful()) {
            OpsAlert::send('Backup Failed', 'mysqldump failed', ['error' => $result->errorOutput()]);
            $this->error('Backup failed: ' . $result->errorOutput());
            return self::FAILURE;
        }

        $this->info("MySQL backup created: {$target}");
    }

    foreach (File::files($backupDir) as $file) {
        if ($file->getMTime() < now()->subDays($retentionDays)->timestamp) {
            File::delete($file->getRealPath());
        }
    }

    OpsAlert::send('Backup Completed', 'Database backup completed successfully.');
    return self::SUCCESS;
})->purpose('Backup database and rotate old backup files');

Artisan::command('ops:restore-drill {--file=} {--force}', function () {
    $backupDir = config('ops.backup.path');
    $manualFile = $this->option('file');
    $latest = collect(File::files($backupDir))
        ->sortByDesc(fn($f) => $f->getMTime())
        ->first();

    $backupFile = $manualFile ?: ($latest?->getRealPath());
    if (empty($backupFile) || !File::exists($backupFile)) {
        $this->error('No backup file found.');
        return self::FAILURE;
    }

    $size = File::size($backupFile);
    if ($size <= 0) {
        OpsAlert::send('Restore Drill Failed', 'Backup file is empty', ['file' => $backupFile]);
        $this->error('Backup file is empty.');
        return self::FAILURE;
    }

    $drillDir = config('ops.backup.restore_drill_path');
    File::ensureDirectoryExists($drillDir);
    $copied = $drillDir . DIRECTORY_SEPARATOR . basename($backupFile);
    File::copy($backupFile, $copied);

    $this->info("Restore drill check OK. Source={$backupFile}; Copied={$copied}; Size={$size} bytes");
    OpsAlert::send('Restore Drill Passed', 'Restore drill artifact check passed.', ['file' => basename($backupFile)]);
    return self::SUCCESS;
})->purpose('Run restore drill by validating latest backup artifact');
