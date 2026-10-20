<?php

namespace App\Models;

use App\Enums\SeatType;
use Database\Factories\SeatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['auditorium_id', 'row_label', 'seat_number', 'seat_type', 'is_active'])]
class Seat extends Model
{
    /** @use HasFactory<SeatFactory> */
    use HasFactory;

    protected $attributes = [
        'seat_type' => 'standard',
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seat_number' => 'integer',
            'seat_type' => SeatType::class,
            'is_active' => 'boolean',
        ];
    }

    public function auditorium(): BelongsTo
    {
        return $this->belongsTo(Auditorium::class);
    }

    public function holds(): HasMany
    {
        return $this->hasMany(SeatHold::class);
    }

    public function label(): string
    {
        return $this->row_label.$this->seat_number;
    }

    public function accessibleName(): string
    {
        return "Row {$this->row_label}, seat {$this->seat_number}, {$this->seat_type->label()}";
    }
}
