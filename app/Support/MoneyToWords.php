<?php

namespace App\Support;

use NumberFormatter;

class MoneyToWords
{
    public static function en(float $amount, string $currency = 'USD'): string
    {
        $amount = round($amount, 2);

        $integer = (int) floor($amount);
        $fraction = (int) round(($amount - $integer) * 100);

        $fmt = new NumberFormatter('en', NumberFormatter::SPELLOUT);

        $intWords = self::clean($fmt->format($integer));
        $curName = self::currencyNameEn($currency);

        $result = ucfirst($intWords) . " {$curName}";

        if ($fraction > 0) {
            $fracWords = self::clean($fmt->format($fraction));
            $result .= " and {$fracWords} cents";
        }

        return $result . " only";
    }

    private static function clean(string $s): string
    {
        // normalize hyphens/spaces from ICU output
        $s = preg_replace('/\s+/', ' ', trim($s));
        return str_replace('-', ' ', $s);
    }

    private static function currencyNameEn(string $currency): string
    {
        return match (strtoupper($currency)) {
            'LBP' => 'Lebanese pounds',
            'EUR' => 'euros',
            'GBP' => 'pounds',
            default => 'dollars', // USD
        };
    }
}