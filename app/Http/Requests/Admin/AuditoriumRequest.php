<?php

namespace App\Http\Requests\Admin;

use App\Models\Auditorium;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuditoriumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => Str::upper(trim((string) $this->input('code'))),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Auditorium|null $auditorium */
        $auditorium = $this->route('auditorium');

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'code' => [
                'required',
                'string',
                'max:24',
                'regex:/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/',
                Rule::unique(Auditorium::class, 'code')->ignore($auditorium?->getKey()),
            ],
            'screen_label' => ['nullable', 'string', 'max:60'],
            'is_active' => ['boolean'],
        ];

        if ($this->isMethod('post')) {
            $rules += [
                'rows' => ['required', 'integer', 'min:1', 'max:26'],
                'seats_per_row' => ['required', 'integer', 'min:1', 'max:30'],
                'premium_rows' => ['required', 'integer', 'min:0', 'lte:rows'],
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'Use capital letters, numbers, and single hyphens only.',
            'premium_rows.lte' => 'Premium rows cannot exceed the total number of rows.',
        ];
    }
}
