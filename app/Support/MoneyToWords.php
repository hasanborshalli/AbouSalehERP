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
    /**
     * Convert a USD/currency amount to Arabic words.
     * Self-contained — does not rely on NumberFormatter locale output,
     * which produces inconsistent word order across platforms.
     */
    public static function ar(float $amount, string $currency = 'USD'): string
    {
        $amount  = round($amount, 2);
        $dollars = (int) floor($amount);

        $curAr = match(strtoupper($currency)) {
            'USD'  => 'دولار امريكي',
            'LBP'  => 'ليرة لبنانية',
            'EUR'  => 'يورو',
            default => $currency,
        };

        $words = self::arInteger($dollars);
        // Return number words + currency only.
        // "فقط لا غير" is added as a SEPARATE block in the PDF template so that
        // line-wrapping of the long amount string never displaces it to the wrong side.
        return $words . ' ' . $curAr;
    }

    /** Convert integer to Arabic words (logical order for utf8Glyphs). */
    private static function arInteger(int $n): string
    {
        if ($n === 0) return 'صفر';

        $ones = [
            1 => 'واحد',      2 => 'اثنان',       3 => 'ثلاثة',
            4 => 'اربعة',     5 => 'خمسة',         6 => 'ستة',
            7 => 'سبعة',      8 => 'ثمانية',        9 => 'تسعة',
            10 => 'عشرة',     11 => 'احد عشر',      12 => 'اثنا عشر',
            13 => 'ثلاثة عشر', 14 => 'اربعة عشر',   15 => 'خمسة عشر',
            16 => 'ستة عشر',  17 => 'سبعة عشر',     18 => 'ثمانية عشر',
            19 => 'تسعة عشر',
        ];
        $tens = [
            2 => 'عشرون',  3 => 'ثلاثون', 4 => 'اربعون',
            5 => 'خمسون',  6 => 'ستون',   7 => 'سبعون',
            8 => 'ثمانون', 9 => 'تسعون',
        ];
        $hundreds = [
            1 => 'مائة',      2 => 'مئتان',     3 => 'ثلاثمائة',
            4 => 'اربعمائة',  5 => 'خمسمائة',   6 => 'ستمائة',
            7 => 'سبعمائة',   8 => 'ثمانمائة',   9 => 'تسعمائة',
        ];

        $parts = [];

        if ($n >= 1000000) {
            $m = (int) ($n / 1000000);
            $parts[] = ($m === 1) ? 'مليون' : (self::arInteger($m) . ' مليون');
            $n %= 1000000;
        }

        if ($n >= 1000) {
            $t = (int) ($n / 1000);
            if ($t === 1)      $parts[] = 'الف';
            elseif ($t === 2)  $parts[] = 'الفان';
            else               $parts[] = self::arInteger($t) . ' الاف';
            $n %= 1000;
        }

        if ($n >= 100) {
            $parts[] = $hundreds[(int) ($n / 100)];
            $n %= 100;
        }

        if ($n > 0) {
            if ($n < 20) {
                $parts[] = $ones[$n];
            } else {
                $ten  = $tens[(int) ($n / 10)];
                $one  = $ones[$n % 10] ?? '';
                $parts[] = $one ? ($one . ' و' . $ten) : $ten;
            }
        }

        return implode(' و', $parts);
    }

}