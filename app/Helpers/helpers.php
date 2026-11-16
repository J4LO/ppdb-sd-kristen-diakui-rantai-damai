<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

if (!function_exists('school_timezone')) {
    /**
     * Mendapatkan identifier zona waktu sekolah (default Asia/Makassar).
     */
    function school_timezone(): string
    {
        return config('app.display_timezone', 'Asia/Makassar');
    }
}

if (!function_exists('school_timezone_label')) {
    /**
     * Mendapatkan label zona waktu sekolah (default WITA).
     */
    function school_timezone_label(): string
    {
        return config('app.display_timezone_label', 'WITA');
    }
}

if (!function_exists('to_school_time')) {
    /**
     * Mengonversi datetime UTC ke Carbon instance dalam zona waktu sekolah.
     */
    function to_school_time($datetime): ?Carbon
    {
        if (empty($datetime)) {
            return null;
        }

        if ($datetime instanceof Carbon) {
            $carbon = $datetime->copy();
        } else {
            $carbon = Carbon::parse($datetime, 'UTC');
        }

        return $carbon->setTimezone(school_timezone());
    }
}

if (!function_exists('format_school_time')) {
    /**
     * Memformat datetime dari UTC ke format tampilan lokal sekolah lengkap dengan label zona waktu.
     * Contoh hasil: "31 Okt 2026, 13:35 WITA" atau "31-10-2026 13:35 WITA"
     */
    function format_school_time($datetime, string $format = 'd-m-Y H:i', bool $withLabel = true): string
    {
        $schoolTime = to_school_time($datetime);
        if (!$schoolTime) {
            return '-';
        }

        // Jika format memerlukan nama hari / bulan berbahasa Indonesia
        $hasTextFormat = preg_match('/[FMlD]/', $format);
        $formatted = $hasTextFormat
            ? $schoolTime->locale('id')->translatedFormat($format)
            : $schoolTime->format($format);

        if ($withLabel) {
            $formatted .= ' ' . school_timezone_label();
        }

        return $formatted;
    }
}

if (!function_exists('parse_midtrans_time')) {
    /**
     * Parsing timestamp dari callback Midtrans (Asia/Jakarta / WIB) dan ubah ke UTC untuk database.
     */
    function parse_midtrans_time(?string $timeString): ?Carbon
    {
        if (empty($timeString)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $timeString, 'Asia/Jakarta')->setTimezone('UTC');
        } catch (\Exception $e) {
            try {
                return Carbon::parse($timeString, 'Asia/Jakarta')->setTimezone('UTC');
            } catch (\Exception $e2) {
                return now('UTC');
            }
        }
    }
}

if (!function_exists('mask_nik')) {
    /**
     * Samarkan NIK agar tidak bocor di tampilan publik / log.
     * Contoh: 7322012345670001 -> 732201******0001
     */
    function mask_nik(?string $nik): string
    {
        if (empty($nik)) {
            return '-';
        }

        $length = strlen($nik);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        $prefix = substr($nik, 0, 6);
        $suffix = substr($nik, -4);
        $maskLength = max(4, $length - 10);

        return $prefix . str_repeat('*', $maskLength) . $suffix;
    }
}

