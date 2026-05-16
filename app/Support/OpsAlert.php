<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OpsAlert
{
    public static function send(string $title, string $message, array $context = []): void
    {
        if (!config('ops.alert.enabled')) {
            return;
        }

        $payload = self::formatPayload($title, $message, $context);

        self::sendMail($title, $payload);
        self::sendTelegram($payload);
    }

    private static function formatPayload(string $title, string $message, array $context = []): string
    {
        $lines = [
            '[ERP ALERT] ' . $title,
            $message,
            'Time: ' . now()->toDateTimeString(),
            'Env: ' . config('app.env'),
            'App: ' . config('app.name'),
        ];

        if (!empty($context)) {
            $lines[] = 'Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        return implode("\n", $lines);
    }

    private static function sendMail(string $subject, string $body): void
    {
        $to = config('ops.alert.mail_to');
        if (empty($to)) {
            return;
        }

        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject('[ERP] ' . $subject);
            });
        } catch (\Throwable $e) {
            Log::error('OpsAlert mail failed', ['error' => $e->getMessage()]);
        }
    }

    private static function sendTelegram(string $body): void
    {
        $botToken = config('ops.alert.telegram_bot_token');
        $chatId = config('ops.alert.telegram_chat_id');

        if (empty($botToken) || empty($chatId)) {
            return;
        }

        try {
            Http::timeout(10)->asForm()->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $body,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('OpsAlert telegram failed', ['error' => $e->getMessage()]);
        }
    }
}

