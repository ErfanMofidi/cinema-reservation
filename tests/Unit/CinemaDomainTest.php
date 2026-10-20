<?php

use App\Enums\ReservationStatus;
use App\Enums\SeatType;
use App\Models\Screening;
use App\Models\Seat;
use App\Support\CinemaFormat;
use App\Support\SeatSelection;
use Carbon\CarbonImmutable;

it('parses, deduplicates, and normalises seat selections', function () {
    expect(SeatSelection::parse('3,1,3, 2'))->toBe([3, 1, 2])
        ->and(SeatSelection::parse(['4', '4', 'x', 0, -1]))->toBe([4, 0])
        ->and(SeatSelection::parse(null))->toBe([])
        ->and(SeatSelection::parse(''))->toBe([])
        ->and(SeatSelection::parse('not-a-seat'))->toBe([]);
});

it('formats money, dates, and durations consistently', function () {
    $date = CarbonImmutable::parse('2026-10-21 19:30:00');

    expect(CinemaFormat::money(1_250_000))->toBe('1,250,000 IRR')
        ->and(CinemaFormat::date($date))->toBe('Wed, Oct 21')
        ->and(CinemaFormat::longDate($date))->toBe('Wednesday, October 21, 2026')
        ->and(CinemaFormat::time($date))->toBe('19:30')
        ->and(CinemaFormat::dateTime($date))->toBe('Wed, Oct 21 · 19:30')
        ->and(CinemaFormat::duration(45))->toBe('45 min')
        ->and(CinemaFormat::duration(120))->toBe('2 hr')
        ->and(CinemaFormat::duration(135))->toBe('2 hr 15 min');
});

it('applies seat-type price multipliers on the server', function () {
    $screening = new Screening(['base_price' => 200_000]);

    $standard = new Seat(['seat_type' => SeatType::Standard]);
    $premium = new Seat(['seat_type' => SeatType::Premium]);
    $accessible = new Seat(['seat_type' => SeatType::Accessible]);

    expect($screening->unitPriceFor($standard))->toBe(200_000)
        ->and($screening->unitPriceFor($premium))->toBe(250_000)
        ->and($screening->unitPriceFor($accessible))->toBe(200_000);
});

it('exposes readable enum labels', function () {
    expect(SeatType::Premium->label())->toBe('Premium')
        ->and(ReservationStatus::Confirmed->label())->toBe('Confirmed');
});
