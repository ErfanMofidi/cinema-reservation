<?php

namespace App\Models;

use App\Enums\MovieStatus;
use App\Support\Media;
use Database\Factories\MovieFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'slug',
    'tagline',
    'synopsis',
    'poster_path',
    'backdrop_path',
    'runtime_minutes',
    'age_rating',
    'language',
    'release_date',
    'status',
])]
class Movie extends Model
{
    /** @use HasFactory<MovieFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'draft',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'runtime_minutes' => 'integer',
            'status' => MovieStatus::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->withTimestamps();
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('status', MovieStatus::Published);
    }

    #[Scope]
    protected function nowShowing(Builder $query): Builder
    {
        return $query
            ->published()
            ->whereHas('screenings', fn (Builder $screenings) => $screenings->upcoming());
    }

    #[Scope]
    protected function comingSoon(Builder $query): Builder
    {
        return $query
            ->published()
            ->whereDate('release_date', '>', today());
    }

    public function posterUrl(): string
    {
        return Media::poster($this->poster_path);
    }

    public function backdropUrl(): ?string
    {
        return Media::image($this->backdrop_path);
    }
}
