<?php

namespace App\Exceptions;

use RuntimeException;

class BookingException extends RuntimeException
{
    /**
     * @param  list<string>  $seatLabels
     */
    public static function seatsUnavailable(array $seatLabels = []): self
    {
        if ($seatLabels !== []) {
            return new self(
                'These seats are no longer available: '.implode(', ', $seatLabels).'. Please choose again.'
            );
        }

        return new self('One or more selected seats are no longer available. Please choose again.');
    }

    public static function closed(): self
    {
        return new self('Online booking has closed for this screening. Please choose another showtime.');
    }
}
