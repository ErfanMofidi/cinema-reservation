<?php

namespace Database\Seeders;

use App\Enums\MovieStatus;
use App\Enums\ScreeningStatus;
use App\Models\Auditorium;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Screening;
use App\Services\SeatLayoutGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CinemaSeeder extends Seeder
{
    public function run(): void
    {
        $genres = collect([
            'Action', 'Adventure', 'Animation', 'Comedy', 'Drama',
            'Mystery', 'Romance', 'Sci-Fi', 'Thriller',
        ])->mapWithKeys(fn (string $name) => [
            $name => Genre::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            ),
        ]);

        $movies = [
            [
                'title' => 'Orbital',
                'slug' => 'orbital',
                'tagline' => 'Gravity is the only thing holding us together.',
                'synopsis' => 'Six months into a solo maintenance mission, engineer Leila Rahimi detects a signal that should not exist. As the station drifts beyond its last safe orbit, she must decide whether to answer a voice from home or protect the mission that keeps her alive.',
                'poster_path' => 'images/posters/orbital.svg',
                'backdrop_path' => 'images/backdrops/orbital.svg',
                'runtime_minutes' => 138,
                'age_rating' => 'PG-13',
                'language' => 'English',
                'release_days_ago' => 14,
                'status' => MovieStatus::Published,
                'genres' => ['Sci-Fi', 'Drama'],
            ],
            [
                'title' => 'The Quiet Floor',
                'slug' => 'the-quiet-floor',
                'tagline' => 'Every archive hides a missing page.',
                'synopsis' => 'A meticulous city archivist discovers that an entire floor of public records has been erased from the building plans. Her search for the missing documents draws her into a decades-old disappearance that powerful people would prefer to keep filed away.',
                'poster_path' => 'images/posters/quiet-floor.svg',
                'backdrop_path' => 'images/backdrops/quiet-floor.svg',
                'runtime_minutes' => 112,
                'age_rating' => 'PG-13',
                'language' => 'English',
                'release_days_ago' => 7,
                'status' => MovieStatus::Published,
                'genres' => ['Mystery', 'Thriller'],
            ],
            [
                'title' => 'Neon Harvest',
                'slug' => 'neon-harvest',
                'tagline' => 'The city grows what it consumes.',
                'synopsis' => 'In a vertical city where crops are grown under artificial suns, a night-shift agronomist uncovers a harvest that feeds something other than the population. To expose the truth, she will have to survive the greenhouse districts after dark.',
                'poster_path' => 'images/posters/neon-harvest.svg',
                'backdrop_path' => 'images/backdrops/neon-harvest.svg',
                'runtime_minutes' => 126,
                'age_rating' => 'R',
                'language' => 'English',
                'release_days_ago' => 3,
                'status' => MovieStatus::Published,
                'genres' => ['Action', 'Sci-Fi'],
            ],
            [
                'title' => 'Paper Moons',
                'slug' => 'paper-moons',
                'tagline' => 'A small map. A very large sky.',
                'synopsis' => 'When a young mapmaker inherits a hand-drawn atlas from her grandmother, she follows its impossible routes across valleys, rooftops, and one very stubborn river. A hand-painted adventure about the places we invent so we can find our way home.',
                'poster_path' => 'images/posters/paper-moons.svg',
                'backdrop_path' => 'images/backdrops/paper-moons.svg',
                'runtime_minutes' => 98,
                'age_rating' => 'PG',
                'language' => 'English',
                'release_days_ago' => 21,
                'status' => MovieStatus::Published,
                'genres' => ['Animation', 'Adventure'],
            ],
            [
                'title' => 'Ember & Ash',
                'slug' => 'ember-and-ash',
                'tagline' => 'Some fires leave a garden.',
                'synopsis' => 'Two former partners return to the ceramics studio they once shared, each carrying a different version of the night that ended it. Over one long summer, they rebuild a kiln, a friendship, and perhaps something neither of them has the vocabulary for yet.',
                'poster_path' => 'images/posters/ember-and-ash.svg',
                'backdrop_path' => 'images/backdrops/ember-and-ash.svg',
                'runtime_minutes' => 119,
                'age_rating' => 'PG-13',
                'language' => 'English',
                'release_days_ago' => 28,
                'status' => MovieStatus::Published,
                'genres' => ['Romance', 'Drama'],
            ],
            [
                'title' => 'The Last Projectionist',
                'slug' => 'the-last-projectionist',
                'tagline' => 'Every frame remembers.',
                'synopsis' => 'The week before a beloved neighbourhood cinema closes, its projectionist programmes one final season of films for the people who grew up in its seats. A warm, wry portrait of the rituals that turn a room into a home.',
                'poster_path' => 'images/posters/last-projectionist.svg',
                'backdrop_path' => null,
                'release_date' => now()->addDays(30)->toDateString(),
                'runtime_minutes' => 105,
                'age_rating' => 'PG',
                'language' => 'English',
                'status' => MovieStatus::Published,
                'genres' => ['Drama', 'Comedy'],
            ],
            [
                'title' => 'Northbound',
                'slug' => 'northbound',
                'tagline' => 'The road remembers everyone.',
                'synopsis' => 'A father and daughter drive north through the last snow of the season to deliver a map that has been in the family for three generations. The road keeps offering detours, and each one asks them what they are really carrying.',
                'poster_path' => 'images/posters/northbound.svg',
                'backdrop_path' => 'images/backdrops/northbound.svg',
                'release_date' => now()->addDays(16)->toDateString(),
                'runtime_minutes' => 131,
                'age_rating' => 'PG-13',
                'language' => 'English',
                'status' => MovieStatus::Published,
                'genres' => ['Adventure', 'Drama'],
            ],
        ];

        $movieModels = [];

        foreach ($movies as $data) {
            $releaseDate = $data['release_date'] ?? now()->subDays($data['release_days_ago'])->toDateString();
            $genreNames = $data['genres'];
            unset($data['release_days_ago'], $data['release_date'], $data['genres']);

            $movie = Movie::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['release_date' => $releaseDate]),
            );

            $movie->genres()->sync(
                collect($genreNames)->map(fn (string $name) => $genres[$name]->id)->all()
            );

            $movieModels[$movie->slug] = $movie;
        }

        $generator = app(SeatLayoutGenerator::class);

        $grand = Auditorium::updateOrCreate(
            ['code' => 'GRAND'],
            [
                'name' => 'The Grand Hall',
                'screen_label' => 'Screen 1',
                'is_active' => true,
            ],
        );

        $studio = Auditorium::updateOrCreate(
            ['code' => 'STUDIO'],
            [
                'name' => 'The Studio',
                'screen_label' => 'Screen 2',
                'is_active' => true,
            ],
        );

        if (! $grand->seats()->exists()) {
            $generator->generate($grand, 8, 12, 2);
        }

        if (! $studio->seats()->exists()) {
            $generator->generate($studio, 6, 10, 1);
        }

        $this->seedScreenings($grand, $studio, $movieModels);

        Screening::firstOrCreate(
            [
                'movie_id' => $movieModels['ember-and-ash']->id,
                'auditorium_id' => $studio->id,
                'starts_at' => now()->subDays(4)->setTime(19, 0),
            ],
            [
                'ends_at' => now()->subDays(4)->setTime(21, 0),
                'base_price' => 320000,
                'status' => ScreeningStatus::Completed,
            ],
        );

        Screening::firstOrCreate(
            [
                'movie_id' => $movieModels['orbital']->id,
                'auditorium_id' => $grand->id,
                'starts_at' => now()->addDays(3)->setTime(9, 0),
            ],
            [
                'ends_at' => now()->addDays(3)->setTime(11, 20),
                'base_price' => 300000,
                'status' => ScreeningStatus::Cancelled,
            ],
        );
    }

    /**
     * @param  array<string, Movie>  $movies
     */
    private function seedScreenings(Auditorium $grand, Auditorium $studio, array $movies): void
    {
        $nowShowing = [
            $movies['orbital'],
            $movies['the-quiet-floor'],
            $movies['neon-harvest'],
            $movies['paper-moons'],
            $movies['ember-and-ash'],
        ];

        $slots = [
            13 => 300000,
            16 => 350000,
            19 => 390000,
            22 => 360000,
        ];

        $auditoriums = [$grand, $studio];

        for ($day = 1; $day <= 7; $day++) {
            foreach (array_keys($slots) as $slotIndex => $hour) {
                foreach ($auditoriums as $auditoriumIndex => $auditorium) {
                    $poolIndex = (($day - 1) * 8 + $slotIndex * 2 + $auditoriumIndex) % count($nowShowing);
                    $movie = $nowShowing[$poolIndex];

                    $startsAt = now()->startOfDay()->addDays($day)->setTime($hour, 0);
                    $endsAt = $startsAt->copy()->addMinutes($movie->runtime_minutes);

                    Screening::firstOrCreate(
                        [
                            'movie_id' => $movie->id,
                            'auditorium_id' => $auditorium->id,
                            'starts_at' => $startsAt,
                        ],
                        [
                            'ends_at' => $endsAt,
                            'base_price' => $slots[$hour],
                            'status' => ScreeningStatus::Scheduled,
                        ],
                    );
                }
            }
        }
    }
}
