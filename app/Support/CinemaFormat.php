<?php

namespace App\Support;

use Carbon\CarbonInterface;

final class CinemaFormat
{
    public static function money(int $amount): string
    {
        return number_format($amount, 0, '.', ',').' '.config('cinema.currency');
    }

    public static function date(CarbonInterface $date): string
    {
        return $date->locale(app()->getLocale())->format('D, M j');
    }

    public static function longDate(CarbonInterface $date): string
    {
        return $date->locale(app()->getLocale())->format('l, F j, Y');
    }

    public static function time(CarbonInterface $date): string
    {
        return $date->locale(app()->getLocale())->format('H:i');
    }

    public static function dateTime(CarbonInterface $date): string
    {
        return $date->locale(app()->getLocale())->format('D, M j · H:i');
    }

    public static function duration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($hours === 0) {
            return "{$remaining} min";
        }

        return $remaining === 0
            ? "{$hours} hr"
            : "{$hours} hr {$remaining} min";
    }
}
