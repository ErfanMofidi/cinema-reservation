<?php

namespace Database\Seeders;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\ReservationSeat;
use App\Models\Screening;
use App\Models\Seat;
use App\Models\SeatHold;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoBookingSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@cinema.test'],
            [
                'name' => 'Ava Director',
                'password' => 'Cinema123!',
                'email_verified_at' => now(),
            ],
        );
        $admin->forceFill(['role' => UserRole::Admin])->save();

        $customer = User::updateOrCreate(
            ['email' => 'customer@cinema.test'],
            [
                'name' => 'Mina Viewer',
                'password' => 'Cinema123!',
                'email_verified_at' => now(),
            ],
        );
        $customer->forceFill(['role' => UserRole::Customer])->save();

        $upcoming = Screening::query()
            ->upcoming()
            ->orderBy('starts_at')
            ->first();

        if ($upcoming) {
            $this->seedReservation(
                $customer,
                $upcoming,
                'CIN-DEMO01',
                ['A3', 'A4'],
                ReservationStatus::Confirmed,
            );
        }

        $past = Screening::query()
            ->where('status', 'completed')
            ->orderByDesc('starts_at')
            ->first();

        if ($past) {
            $this->seedReservation(
                $customer,
                $past,
                'CIN-DEMO02',
                ['C5', 'C6'],
                ReservationStatus::Confirmed,
            );
        }

        $cancelled = Screening::query()
            ->where('status', 'cancelled')
            ->orderBy('starts_at')
            ->first();

        if ($cancelled) {
            $this->seedReservation(
                $customer,
                $cancelled,
                'CIN-DEMO03',
                ['B3', 'B4'],
                ReservationStatus::Cancelled,
            );
        }
    }

    /**
     * @param  list<string>  $seatLabels
     */
    private function seedReservation(
        User $user,
        Screening $screening,
        string $reference,
        array $seatLabels,
        ReservationStatus $status,
    ): void {
        if (Reservation::where('reference', $reference)->exists()) {
            return;
        }

        $screening->loadMissing('auditorium');

        $seats = $screening->auditorium->seats()
            ->get()
            ->filter(fn (Seat $seat) => in_array($seat->label(), $seatLabels, true))
            ->values();

        if ($seats->count() !== count($seatLabels)) {
            return;
        }

        DB::transaction(function () use ($user, $screening, $reference, $seats, $status) {
            $subtotal = $seats->sum(fn (Seat $seat) => $screening->unitPriceFor($seat));

            $reservation = Reservation::create([
                'user_id' => $user->id,
                'screening_id' => $screening->id,
                'reference' => $reference,
                'idempotency_key' => 'seed-'.$reference,
                'status' => $status,
                'subtotal' => $subtotal,
                'fees' => 0,
                'total' => $subtotal,
                'currency' => config('cinema.currency'),
                'confirmed_at' => now(),
                'cancelled_at' => $status === ReservationStatus::Cancelled ? now() : null,
                'cancellation_reason' => $status === ReservationStatus::Cancelled
                    ? 'Cancelled by customer.'
                    : null,
            ]);

            foreach ($seats as $seat) {
                ReservationSeat::create([
                    'reservation_id' => $reservation->id,
                    'seat_id' => $seat->id,
                    'unit_price' => $screening->unitPriceFor($seat),
                ]);

                if ($status === ReservationStatus::Confirmed) {
                    SeatHold::firstOrCreate(
                        [
                            'screening_id' => $screening->id,
                            'seat_id' => $seat->id,
                        ],
                        ['reservation_id' => $reservation->id],
                    );
                }
            }
        });
    }
}
