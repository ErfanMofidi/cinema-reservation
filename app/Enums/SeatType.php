<?php

namespace App\Enums;

enum SeatType: string
{
    case Standard = 'standard';
    case Premium = 'premium';
    case Accessible = 'accessible';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Premium => 'Premium',
            self::Accessible => 'Accessible',
        };
    }

    /**
     * Server-side price multiplier for this seat type.
     */
    public function priceMultiplier(): float
    {
        return (float) config("cinema.seat_price_multipliers.{$this->value}", 1.0);
    }
}
