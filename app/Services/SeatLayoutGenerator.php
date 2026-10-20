<?php

namespace App\Services;

use App\Enums\SeatType;
use App\Models\Auditorium;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;

final class SeatLayoutGenerator
{
    /**
     * Create or complete a rectangular seat layout for an auditorium.
     * Existing seats are never removed here; use replace() for a full rebuild.
     */
    public function generate(
        Auditorium $auditorium,
        int $rows,
        int $seatsPerRow,
        int $premiumRows = 2,
    ): int {
        $rows = max(1, min(26, $rows));
        $seatsPerRow = max(1, min(30, $seatsPerRow));
        $premiumRows = max(0, min($rows, $premiumRows));

        $rowLabels = range('A', 'Z');
        $created = 0;

        DB::transaction(function () use ($auditorium, $rows, $seatsPerRow, $premiumRows, $rowLabels, &$created) {
            for ($rowIndex = 0; $rowIndex < $rows; $rowIndex++) {
                for ($seatNumber = 1; $seatNumber <= $seatsPerRow; $seatNumber++) {
                    $type = $rowIndex >= ($rows - $premiumRows)
                        ? SeatType::Premium
                        : SeatType::Standard;

                    // Accessible positions sit at the ends of the front row.
                    if ($rowIndex === 0 && in_array($seatNumber, [1, $seatsPerRow], true)) {
                        $type = SeatType::Accessible;
                    }

                    Seat::firstOrCreate(
                        [
                            'auditorium_id' => $auditorium->id,
                            'row_label' => $rowLabels[$rowIndex],
                            'seat_number' => $seatNumber,
                        ],
                        [
                            'seat_type' => $type,
                            'is_active' => true,
                        ],
                    );

                    $created++;
                }
            }

            $auditorium->update([
                'capacity' => $auditorium->seats()->where('is_active', true)->count(),
            ]);
        });

        return $created;
    }

    /**
     * Rebuild a layout. Callers must ensure the auditorium has no screenings.
     */
    public function replace(
        Auditorium $auditorium,
        int $rows,
        int $seatsPerRow,
        int $premiumRows = 2,
    ): void {
        DB::transaction(function () use ($auditorium, $rows, $seatsPerRow, $premiumRows) {
            $auditorium->seats()->delete();
            $this->generate($auditorium, $rows, $seatsPerRow, $premiumRows);
        });
    }
}
