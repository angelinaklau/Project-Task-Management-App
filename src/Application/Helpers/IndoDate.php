<?php

namespace App\Application\Helpers;

class IndoDate
{
    
    public static function now(): string
    {
        $date = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
        return $date->format('Y-m-d H:i:s');
    }

 
    public static function formatIndo(string $date): string
    {
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $timestamp = strtotime($date);
        $day = date('d', $timestamp);
        $month = (int) date('m', $timestamp);
        $year = date('Y', $timestamp);

        return $day . ' ' . $bulan[$month] . ' ' . $year;
    }
}
