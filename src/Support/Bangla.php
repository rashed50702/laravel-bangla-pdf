<?php

namespace Rashed50702\BanglaPdf\Support;

use DateTimeInterface;
use DateTimeImmutable;
use InvalidArgumentException;

final class Bangla
{
    private const array DIGITS = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];

    private const array MONTHS = [
        'January' => 'জানুয়ারি', 'February' => 'ফেব্রুয়ারি', 'March' => 'মার্চ', 'April' => 'এপ্রিল',
        'May' => 'মে', 'June' => 'জুন', 'July' => 'জুলাই', 'August' => 'আগস্ট',
        'September' => 'সেপ্টেম্বর', 'October' => 'অক্টোবর', 'November' => 'নভেম্বর', 'December' => 'ডিসেম্বর',
    ];

    private const array DAYS = [
        'Saturday' => 'শনিবার', 'Sunday' => 'রবিবার', 'Monday' => 'সোমবার', 'Tuesday' => 'মঙ্গলবার',
        'Wednesday' => 'বুধবার', 'Thursday' => 'বৃহস্পতিবার', 'Friday' => 'শুক্রবার',
    ];

    private const array WORDS = [
        '', 'এক', 'দুই', 'তিন', 'চার', 'পাঁচ', 'ছয়', 'সাত', 'আট', 'নয়',
        'দশ', 'এগারো', 'বারো', 'তেরো', 'চৌদ্দ', 'পনেরো', 'ষোলো', 'সতেরো', 'আঠারো', 'উনিশ',
        'বিশ', 'একুশ', 'বাইশ', 'তেইশ', 'চব্বিশ', 'পঁচিশ', 'ছাব্বিশ', 'সাতাশ', 'আটাশ', 'ঊনত্রিশ',
        'ত্রিশ', 'একত্রিশ', 'বত্রিশ', 'তেত্রিশ', 'চৌত্রিশ', 'পঁয়ত্রিশ', 'ছত্রিশ', 'সাঁইত্রিশ', 'আটত্রিশ', 'ঊনচল্লিশ',
        'চল্লিশ', 'একচল্লিশ', 'বিয়াল্লিশ', 'তেতাল্লিশ', 'চুয়াল্লিশ', 'পঁয়তাল্লিশ', 'ছেচল্লিশ', 'সাতচল্লিশ', 'আটচল্লিশ', 'ঊনপঞ্চাশ',
        'পঞ্চাশ', 'একান্ন', 'বায়ান্ন', 'তিপ্পান্ন', 'চুয়ান্ন', 'পঞ্চান্ন', 'ছাপ্পান্ন', 'সাতান্ন', 'আটান্ন', 'ঊনষাট',
        'ষাট', 'একষট্টি', 'বাষট্টি', 'তেষট্টি', 'চৌষট্টি', 'পঁয়ষট্টি', 'ছেষট্টি', 'সাতষট্টি', 'আটষট্টি', 'ঊনসত্তর',
        'সত্তর', 'একাত্তর', 'বাহাত্তর', 'তিয়াত্তর', 'চুয়াত্তর', 'পঁচাত্তর', 'ছিয়াত্তর', 'সাতাত্তর', 'আটাত্তর', 'ঊনআশি',
        'আশি', 'একাশি', 'বিরাশি', 'তিরাশি', 'চুরাশি', 'পঁচাশি', 'ছিয়াশি', 'সাতাশি', 'আটাশি', 'ঊননব্বই',
        'নব্বই', 'একানব্বই', 'বিরানব্বই', 'তিরানব্বই', 'চুরানব্বই', 'পঁচানব্বই', 'ছিয়ানব্বই', 'সাতানব্বই', 'আটানব্বই', 'নিরানব্বই',
    ];

    private const array PUNCTUATION = [
        '—' => '&mdash;', '–' => '&ndash;', '‘' => '&lsquo;', '’' => '&rsquo;',
        '“' => '&ldquo;', '”' => '&rdquo;', '…' => '&hellip;', '•' => '&bull;',
    ];

    final public static function digits(string|int|float|null $value): string
    {
        return strtr((string) $value, self::DIGITS);
    }

    final public static function englishDigits(string|int|float|null $value): string
    {
        return strtr((string) $value, array_flip(self::DIGITS));
    }

    final public static function number(int|float|string $value, int $decimals = 0, bool $banglaDigits = true): string
    {
        $value = (float) self::englishDigits($value);
        $negative = $value < 0;
        $fixed = number_format(abs($value), $decimals, '.', '');
        [$integer, $fraction] = array_pad(explode('.', $fixed), 2, null);

        $lastThree = substr($integer, -3);
        $rest = substr($integer, 0, -3);
        $grouped = $rest === '' ? $lastThree : preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' . $lastThree;
        $result = ($negative ? '-' : '') . $grouped . ($fraction !== null ? '.' . $fraction : '');

        return $banglaDigits ? self::digits($result) : $result;
    }

    final public static function taka(int|float|string $amount, int $decimals = 2, bool $banglaDigits = true, string $symbol = '৳'): string
    {
        $formatted = self::number($amount, $decimals, $banglaDigits);

        return str_starts_with($formatted, '-') ? '-' . $symbol . substr($formatted, 1) : $symbol . $formatted;
    }

    final public static function words(int|string $number): string
    {
        $number = (int) self::englishDigits($number);

        if ($number === 0) {
            return 'শূন্য';
        }

        if ($number < 0) {
            return 'ঋণাত্মক ' . self::words(-$number);
        }

        $parts = [];
        $crore = intdiv($number, 10000000);
        $number %= 10000000;

        if ($crore > 0) {
            $parts[] = self::words($crore) . ' কোটি';
        }

        foreach ([[100000, ' লক্ষ'], [1000, ' হাজার'], [100, 'শত']] as [$unit, $label]) {
            $count = intdiv($number, $unit);
            $number %= $unit;
            if ($count > 0) {
                $parts[] = self::WORDS[$count] . $label;
            }
        }

        if ($number > 0) {
            $parts[] = self::WORDS[$number];
        }

        return implode(' ', $parts);
    }

    final public static function takaInWords(int|float|string $amount, string $suffix = 'মাত্র'): string
    {
        $amount = round((float) self::englishDigits($amount), 2);

        if ($amount < 0) {
            throw new InvalidArgumentException('Amount in words requires a non-negative amount.');
        }

        $taka = (int) floor($amount);
        $paisa = (int) round(($amount - $taka) * 100);

        $text = self::words($taka) . ' টাকা';

        if ($paisa > 0) {
            $text .= ' ' . self::words($paisa) . ' পয়সা';
        }

        return trim($text . ' ' . $suffix);
    }

    final public static function date(DateTimeInterface|string|int|null $date, string $format = 'j F, Y'): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        $date = match (true) {
            $date instanceof DateTimeInterface => $date,
            is_int($date) => (new DateTimeImmutable())->setTimestamp($date),
            default => new DateTimeImmutable($date),
        };

        $text = $date->format($format);
        $months = self::MONTHS + array_combine(array_map(fn (string $m) => substr($m, 0, 3), array_keys(self::MONTHS)), self::MONTHS);
        $days = self::DAYS + array_combine(array_map(fn (string $d) => substr($d, 0, 3), array_keys(self::DAYS)), self::DAYS);

        $text = preg_replace_callback('/\b(' . implode('|', array_keys($months + $days)) . ')\b/', fn (array $m) => ($months + $days)[$m[1]], $text);

        return self::digits(strtr($text, ['AM' => 'পূর্বাহ্ণ', 'PM' => 'অপরাহ্ণ', 'am' => 'পূর্বাহ্ণ', 'pm' => 'অপরাহ্ণ']));
    }

    final public static function protectPunctuation(string $html, string $latinFont, string $bengaliFont = 'freeserifbangla'): string
    {
        $pattern = '/(' . implode('|', array_map(fn (string $c) => preg_quote($c, '/'), array_merge(array_keys(self::PUNCTUATION), array_values(self::PUNCTUATION)))) . ')+/u';
        $danda = '/[।॥]+/u';
        $segments = preg_split('/(<[^>]*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $skip = false;

        foreach ($segments as $i => $segment) {
            if ($segment !== '' && $segment[0] === '<') {
                if (preg_match('/^<\s*(style|script)\b/i', $segment)) {
                    $skip = true;
                } elseif (preg_match('/^<\s*\/\s*(style|script)\b/i', $segment)) {
                    $skip = false;
                }
                continue;
            }

            if (!$skip && $segment !== '') {
                $segment = preg_replace($pattern, '<span style="font-family: ' . $latinFont . ';">$0</span>', $segment);
                $segments[$i] = preg_replace($danda, '<span lang="bn" style="font-family: ' . $bengaliFont . ';">$0</span>', $segment);
            }
        }

        return implode('', $segments);
    }
}
