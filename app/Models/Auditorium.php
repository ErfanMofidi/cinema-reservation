<?php

namespace App\Models;

use Database\Factories\AuditoriumFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('auditoriums')]
#[Fillable(['name', 'code', 'screen_label', 'capacity', 'is_active'])]
class Auditorium extends Model
{
    /** @use HasFactory<AuditoriumFactory> */
    use HasFactory;

    protected $attributes = [
        'is_active' => true,
        'capacity' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class)->orderBy('row_label')->orderBy('seat_number');
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function seatLabelRange(): string
    {
        return $this->code.' · '.$this->capacity.' seats';
    }
}
