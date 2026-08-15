<?php

namespace App\Support;

class Format
{
    public static function onlyDigits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    public static function document(?string $document): string
    {
        $document = (string) $document;

        return match (strlen($document)) {
            11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document) ?? $document,
            14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document) ?? $document,
            default => $document,
        };
    }

    public static function phone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        return match (strlen($phone)) {
            10 => preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone) ?? $phone,
            11 => preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone) ?? $phone,
            default => $phone,
        };
    }

    public static function money(mixed $value): string
    {
        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }

    public static function decimal(mixed $value): string
    {
        $value = trim((string) $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
        }

        return str_replace(',', '.', $value);
    }
}