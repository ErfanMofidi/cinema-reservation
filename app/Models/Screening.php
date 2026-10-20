<?php

namespace App\Models;

use App\Enums\ScreeningStatus;
use Carbon\CarbonInterface;
use Database\Factories\ScreeningFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'movie_id',
    'auditorium_id',
    'starts_at',
    'ends_at',
    'base_price',
    'status',
])]
class Screening extends Model
{
    /** @use HasFactory<ScreeningFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'scheduled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'base_price' => 'integer',
            'status' => ScreeningStatus::class,
        ];
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function auditorium(): BelongsTo
    {
        return $this->belongsTo(Auditorium::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function seatHolds(): HasMany
    {
        return $this->hasMany(SeatHold::class);
    }

    #[Scope]
    protected function scheduled(Builder $query): Builder
    {
        return $query->where('status', ScreeningStatus::Scheduled);
    }

    #[Scope]
    protected function upcoming(Builder $query): Builder
    {
        return $query
            ->scheduled()
            ->where('starts_at', '>', now());
    }

    #[Scope]
    protected function bookable(Builder $query): Builder
    {
        return $query
            ->scheduled()
            ->where('starts_at', '>', now()->addMinutes((int) config('cinema.booking_cutoff_minutes')));
    }

    public function bookingClosesAt(): CarbonInterface
    {
        return $this->starts_at->copy()->subMinutes((int) config('cinema.booking_cutoff_minutes'));
    }

    public function isBookingOpen(): bool
    {
        return $this->status === ScreeningStatus::Scheduled
            && now()->lt($this->bookingClosesAt());
    }

    public function isFinished(): bool
    {
        return $this->ends_at->isPast();
    }

    public function unitPriceFor(Seat $seat): int
    {
        return (int) round($this->base_price * $seat->seat_type->priceMultiplier());
    }

    public function remainingSeatCount(): int
    {
        $held = $this->relationLoaded('seatHolds')
            ? $this->seatHolds->count()
            : $this->seatHolds()->count();

        return max(0, (int) ($this->auditorium?->capacity ?? 0) - $held);
    }
}
