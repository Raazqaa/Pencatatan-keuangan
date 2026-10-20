<?php

namespace App\Support;

class Rupiah
{
    /**
     * Format angka menjadi Rupiah, contoh: 1500000 => "Rp1.500.000".
     */
    public static function format(float|int|string|null $amount, bool $withPrefix = true): string
    {
        $amount = (float) ($amount ?? 0);
        $formatted = number_format($amount, 0, ',', '.');

        return $withPrefix ? 'Rp'.$formatted : $formatted;
    }

    /**
     * Format ringkas untuk axis grafik, contoh: 1500000 => "Rp1,5jt".
     */
    public static function compact(float|int|string|null $amount): string
    {
        $amount = (float) ($amount ?? 0);
        $sign = $amount < 0 ? '-' : '';
        $amount = abs($amount);

        if ($amount >= 1_000_000_000) {
            return $sign.'Rp'.number_format($amount / 1_000_000_000, 1, ',', '.').'M';
        }

        if ($amount >= 1_000_000) {
            return $sign.'Rp'.number_format($amount / 1_000_000, 1, ',', '.').'jt';
        }

        if ($amount >= 1_000) {
            return $sign.'Rp'.number_format($amount / 1_000, 0, ',', '.').'rb';
        }

        return $sign.'Rp'.number_format($amount, 0, ',', '.');
    }
}
