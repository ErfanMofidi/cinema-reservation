<?php

namespace App\Http\Requests\Admin;

use App\Enums\MovieStatus;
use App\Models\Movie;
use App\Support\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
        }

        $this->merge([
            'slug' => Str::lower(trim((string) $this->input('slug'))),
            'poster_path' => $this->filled('poster_path') ? $this->input('poster_path') : null,
            'backdrop_path' => $this->filled('backdrop_path') ? $this->input('backdrop_path') : null,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Movie|null $movie */
        $movie = $this->route('movie');

        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'required',
                'string',
                'max:200',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(Movie::class)->ignore($movie?->getKey()),
            ],
            'tagline' => ['nullable', 'string', 'max:240'],
            'synopsis' => ['required', 'string', 'min:40', 'max:8000'],
            'poster_path' => ['nullable', 'string', Rule::in(Media::posterOptions())],
            'backdrop_path' => ['nullable', 'string', Rule::in(Media::backdropOptions())],
            'runtime_minutes' => ['required', 'integer', 'min:1', 'max:400'],
            'age_rating' => ['required', 'string', 'max:12'],
            'language' => ['required', 'string', 'max:40'],
            'release_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(MovieStatus::class)],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['integer', 'distinct', Rule::exists('genres', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Use lowercase letters, numbers, and single hyphens only.',
            'poster_path.in' => 'Choose a poster from the local media library.',
            'backdrop_path.in' => 'Choose a backdrop from the local media library.',
            'genres.required' => 'Select at least one genre.',
            'synopsis.min' => 'Write a slightly longer synopsis so visitors know what the film is about.',
        ];
    }
}
