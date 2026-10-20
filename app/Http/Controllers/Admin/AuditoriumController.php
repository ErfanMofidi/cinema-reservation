<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SeatType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditoriumLayoutRequest;
use App\Http\Requests\Admin\AuditoriumRequest;
use App\Models\Auditorium;
use App\Services\SeatLayoutGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AuditoriumController extends Controller
{
    public function index(): View
    {
        $auditoriums = Auditorium::query()
            ->withCount(['seats', 'screenings'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.auditoriums.index', compact('auditoriums'));
    }

    public function create(): View
    {
        return view('admin.auditoriums.create');
    }

    public function store(AuditoriumRequest $request, SeatLayoutGenerator $layouts): RedirectResponse
    {
        $data = $request->validated();
        $rows = (int) $data['rows'];
        $seatsPerRow = (int) $data['seats_per_row'];
        $premiumRows = (int) $data['premium_rows'];

        $auditorium = DB::transaction(function () use ($data, $rows, $seatsPerRow, $premiumRows, $layouts) {
            $auditorium = Auditorium::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'screen_label' => $data['screen_label'] ?? null,
                'is_active' => $data['is_active'],
                'capacity' => 0,
            ]);

            $layouts->generate($auditorium, $rows, $seatsPerRow, $premiumRows);

            return $auditorium->refresh();
        });

        return redirect()
            ->route('admin.auditoriums.edit', $auditorium)
            ->with('success', "{$auditorium->name} was created with {$auditorium->capacity} seats.");
    }

    public function edit(Auditorium $auditorium): View
    {
        $auditorium->loadCount(['seats', 'screenings']);

        $rowCount = (int) $auditorium->seats()->distinct()->count('row_label');
        $seatsPerRow = (int) ($auditorium->seats()
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('row_label')
            ->orderByDesc('aggregate')
            ->value('aggregate') ?? 12);
        $premiumRows = (int) $auditorium->seats()
            ->where('seat_type', SeatType::Premium->value)
            ->distinct()
            ->count('row_label');

        return view('admin.auditoriums.edit', [
            'auditorium' => $auditorium,
            'layoutDefaults' => [
                'rows' => max(1, $rowCount),
                'seats_per_row' => max(1, $seatsPerRow),
                'premium_rows' => $premiumRows,
            ],
        ]);
    }

    public function update(AuditoriumRequest $request, Auditorium $auditorium): RedirectResponse
    {
        $auditorium->update($request->safe()->only(['name', 'code', 'screen_label', 'is_active']));

        return redirect()
            ->route('admin.auditoriums.edit', $auditorium)
            ->with('success', 'The auditorium details were updated.');
    }

    public function layout(
        AuditoriumLayoutRequest $request,
        Auditorium $auditorium,
        SeatLayoutGenerator $layouts,
    ): RedirectResponse {
        if ($auditorium->screenings()->exists()) {
            return back()->with('error', 'The seat layout cannot be rebuilt while the auditorium has screenings.');
        }

        $layouts->replace(
            $auditorium,
            (int) $request->validated('rows'),
            (int) $request->validated('seats_per_row'),
            (int) $request->validated('premium_rows'),
        );

        return redirect()
            ->route('admin.auditoriums.edit', $auditorium->refresh())
            ->with('success', "The seat layout was rebuilt with {$auditorium->capacity} seats.");
    }
}
