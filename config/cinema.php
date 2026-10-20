<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cinema identity
    |--------------------------------------------------------------------------
    |
    | This is a single-cinema installation. The name is used in navigation,
    | transactional copy and metadata. Change it without touching views.
    |
    */

    'name' => env('CINEMA_NAME', 'Lumière Cinema'),

    'tagline' => env('CINEMA_TAGLINE', 'Stories worth leaving the house for.'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Prices are stored as whole currency units (integers). IRR has no minor
    | unit, so integer amounts avoid floating-point rounding permanently.
    |
    */

    'currency' => env('CINEMA_CURRENCY', 'IRR'),

    /*
    |--------------------------------------------------------------------------
    | Booking rules
    |--------------------------------------------------------------------------
    |
    | All values are minutes. Booking closes before a screening begins so the
    | venue can prepare the auditorium. Cancellation is allowed until the
    | cutoff, after which the reservation is retained but cannot be cancelled
    | online.
    |
    */

    'booking_cutoff_minutes' => (int) env('CINEMA_BOOKING_CUTOFF_MINUTES', 15),

    'cancellation_cutoff_minutes' => (int) env('CINEMA_CANCELLATION_CUTOFF_MINUTES', 120),

    'max_seats_per_reservation' => (int) env('CINEMA_MAX_SEATS', 8),

    'screening_buffer_minutes' => (int) env('CINEMA_SCREENING_BUFFER_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Pricing
    |--------------------------------------------------------------------------
    |
    | The screening's base price is multiplied by the seat type. Fees are zero
    | in the demo checkout; the field exists so totals are explicit and future
    | pricing changes do not alter historical reservations.
    |
    */

    'booking_fee' => (int) env('CINEMA_BOOKING_FEE', 0),

    'seat_price_multipliers' => [
        'standard' => 1.0,
        'premium' => 1.25,
        'accessible' => 1.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    */

    'poster_fallback' => 'images/posters/fallback.svg',

];
