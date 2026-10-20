<?php

namespace App\Support;

use Illuminate\Support\Str;

final class Media
{
    public static function image(?string $path, ?string $fallback = null): ?string
    {
        if (is_string($path) && $path !== '') {
            if (Str::startsWith($path, 'https://')) {
                return $path;
            }

            if (preg_match('/^images\/[A-Za-z0-9._\-\/]+$/', $path) === 1
                && is_file(public_path($path))) {
                return asset($path);
            }
        }

        if ($fallback === null) {
            return null;
        }

        return asset($fallback);
    }

    public static function poster(?string $path): string
    {
        return self::image($path, config('cinema.poster_fallback'))
            ?? asset(config('cinema.poster_fallback'));
    }

    /**
     * @return list<string>
     */
    public static function posterOptions(): array
    {
        return self::localImages('images/posters');
    }

    /**
     * @return list<string>
     */
    public static function backdropOptions(): array
    {
        return self::localImages('images/backdrops');
    }

    /**
     * @return list<string>
     */
    private static function localImages(string $directory): array
    {
        $absolute = public_path($directory);

        if (! is_dir($absolute)) {
            return [];
        }

        $files = scandir($absolute) ?: [];

        return collect($files)
            ->filter(fn (string $file) => preg_match('/\.(svg|png|jpe?g|webp)$/i', $file) === 1)
            ->map(fn (string $file) => $directory.'/'.$file)
            ->sort()
            ->values()
            ->all();
    }
}
