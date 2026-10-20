<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Carbon\CarbonInterface;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'screening_id',
    'reference',
    'idempotency_key',
    'status',
    'subtotal',
    'fees',
    'total',
    'currency',
    'expires_at',
    'confirmed_at',
    'cancelled_at',
    'cancellation_reason',
])]
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'subtotal' => 'integer',
            'fees' => 'integer',
            'total' => 'integer',
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function screening(): BelongsTo
    {
        return $this->belongsTo(Screening::class);
    }

    public function seats(): BelongsToMany
    {
        return $this->belongsToMany(Seat::class, 'reservation_seats')
            ->withPivot('unit_price')
            ->withTimestamps();
    }

    public function reservationSeats(): HasMany
    {
        return $this->hasMany(ReservationSeat::class);
    }

    public function seatHolds(): HasMany
    {
        return $this->hasMany(SeatHold::class);
    }

    #[Scope]
    protected function confirmed(Builder $query): Builder
    {
        return $query->where('status', ReservationStatus::Confirmed);
    }

    public function isCancellable(): bool
    {
        return $this->status === ReservationStatus::Confirmed
            && $this->screening->starts_at->isAfter(now()->addMinutes(
                (int) config('cinema.cancellation_cutoff_minutes')
            ));
    }

    public function cancellationDeadline(): CarbonInterface
    {
        return $this->screening->starts_at->copy()->subMinutes(
            (int) config('cinema.cancellation_cutoff_minutes')
        );
    }

    public function seatCount(): int
    {
        return $this->reservationSeats->count();
    }

    public function seatLabelList(): string
    {
        return $this->seats->sortBy(['row_label', 'seat_number'])
            ->map(fn (Seat $seat) => $seat->label())
            ->join(', ');
    }
}
