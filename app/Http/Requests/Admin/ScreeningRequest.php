<?php

namespace App\Http\Requests\Admin;

use App\Enums\ScreeningStatus;
use App\Models\Auditorium;
use App\Models\Screening;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ScreeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'movie_id' => ['required', 'integer', Rule::exists('movies', 'id')],
            'auditorium_id' => ['required', 'integer', Rule::exists('auditoriums', 'id')],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'base_price' => ['required', 'integer', 'min:0', 'max:100000000'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $startsAt = CarbonImmutable::parse((string) $this->input('starts_at'));
                $endsAt = CarbonImmutable::parse((string) $this->input('ends_at'));
                $auditoriumId = (int) $this->input('auditorium_id');

                /** @var Screening|null $screening */
                $screening = $this->route('screening');

                $auditorium = Auditorium::query()->find($auditoriumId);

                if (! $auditorium) {
                    return;
                }

                if (! $auditorium->is_active && (! $screening || $screening->auditorium_id !== $auditorium->id)) {
                    $validator->errors()->add('auditorium_id', 'Choose an active auditorium for a new screening.');

                    return;
                }

                if ($this->isMethod('post') && $startsAt->isPast()) {
                    $validator->errors()->add('starts_at', 'A new screening must start in the future.');
                }

                $buffer = (int) config('cinema.screening_buffer_minutes');

                $conflict = Screening::query()
                    ->where('auditorium_id', $auditoriumId)
                    ->where('status', ScreeningStatus::Scheduled)
                    ->when($screening, fn ($query) => $query->whereKeyNot($screening->getKey()))
                    ->where('starts_at', '<', $endsAt->addMinutes($buffer))
                    ->where('ends_at', '>', $startsAt->subMinutes($buffer))
                    ->with('movie:id,title')
                    ->orderBy('starts_at')
                    ->first();

                if ($conflict) {
                    $validator->errors()->add(
                        'starts_at',
                        sprintf(
                            'This auditorium is already booked for %s from %s to %s. Allow a %d-minute buffer between screenings.',
                            $conflict->movie->title,
                            $conflict->starts_at->format('M j, H:i'),
                            $conflict->ends_at->format('M j, H:i'),
                            $buffer,
                        ),
                    );
                }
            },
        ];
    }
}
