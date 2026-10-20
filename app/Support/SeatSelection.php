<?php

namespace App\Support;

final class SeatSelection
{
    /**
     * Normalise a comma-separated string or array of seat IDs.
     *
     * @return list<int>
     */
    public static function parse(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        if (! is_array($raw)) {
            return [];
        }

        return collect($raw)
            ->filter(fn ($value) => is_scalar($value) && preg_match('/^\d+$/', trim((string) $value)) === 1)
            ->map(fn ($value) => (int) trim((string) $value))
            ->unique()
            ->values()
            ->all();
    }
}
