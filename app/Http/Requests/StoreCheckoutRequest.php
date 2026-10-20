<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('seats'))) {
            $this->merge(['seats' => explode(',', $this->input('seats'))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $maxSeats = max(1, (int) config('cinema.max_seats_per_reservation'));

        return [
            'seats' => ['required', 'array', 'min:1', "max:{$maxSeats}"],
            'seats.*' => ['required', 'integer', 'distinct'],
            'idempotency_key' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'seats.required' => 'Choose at least one seat before confirming your reservation.',
            'seats.max' => 'You can reserve up to :max seats at a time.',
            'seats.*.integer' => 'One or more selected seats are invalid. Please choose again.',
            'idempotency_key.required' => 'Your checkout session expired. Please review your seats and try again.',
        ];
    }
}
