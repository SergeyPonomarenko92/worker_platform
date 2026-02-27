<?php

namespace App\Support;

class ContactFieldNormalizer
{
    public static function website(?string $raw): ?string
    {
        $v = trim((string) ($raw ?? ''));

        if ($v === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $v) === 1) {
            return $v;
        }

        return 'https://'.$v;
    }

    public static function phone(?string $raw): ?string
    {
        $v = trim((string) ($raw ?? ''));

        if ($v === '') {
            return null;
        }

        return $v;
    }
}
