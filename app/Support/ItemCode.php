<?php

namespace App\Support;

class ItemCode
{
    public const VALIDATION_REGEX = '/^[A-Za-z0-9_-]+$/';

    public static function normalize($value): string
    {
        if ($value === null) {
            return '';
        }

        return preg_replace('/\s+/', '', trim((string) $value));
    }

    public static function isValid(?string $value): bool
    {
        return $value !== null && $value !== '' && preg_match(self::VALIDATION_REGEX, $value) === 1;
    }
}
