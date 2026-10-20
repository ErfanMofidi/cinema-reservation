<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ScreeningStatus;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScreeningRequest;
use App\Models\Auditorium;
use App\Models\Movie;
use App\Models\Screening;
use App\Services\ReservationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => mb_substr(trim((string) $request->query('q', '')), 0, 80),
            'date' => (string) $request->query('date', ''),
            'movie' => (string) $request->query('movie', ''),
            'auditorium' => (string) $request->query('auditorium', ''),
            'status' => (string) $request->query('status', ''),
        ];

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date']) !== 1) {
            $filters['date'] = '';
        }

        if (! in_array($filters['status'], array_column(ScreeningStatus::cases(), 'value'), true)) {
            $filters['status'] = '';
        }

        $screenings = Screening::query()
            ->with(['movie', 'auditorium'])
            ->withCount('reservations')
            ->when($filters['q'] !== '', function (Builder $query) use ($filters) {
                $search = '%'.$filters['q'].'%';
                $query->whereHas('movie', fn (Builder $movies) => $movies->where('title', 'like', $search));
            })
            ->when($filters['date'] !== '', fn (Builder $query) => $query->whereDate('starts_at', $filters['date']))
            ->when($filters['movie'] !== '', fn (Builder $query) => $query->where('movie_id', $filters['movie']))
            ->when($filters['auditorium'] !== '', fn (Builder $query) => $query->where('auditorium_id', $filters['auditorium']))
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->orderByDesc('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.screenings.index', [
            'screenings' => $screenings,
            'filters' => $filters,
            'movies' => Movie::query()->orderBy('title')->get(['id', 'title']),
            'auditoriums' => Auditorium::query()->orderBy('name')->get(['id', 'name', 'code']),
            'statuses' => ScreeningStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.screenings.create', $this->formData());
    }

    public function store(ScreeningRequest $request): RedirectResponse
    {
        $screening = Screening::create([
            ...$request->validated(),
            'status' => ScreeningStatus::Scheduled,
        ]);

        return redirect()
            ->route('admin.screenings.index')
            ->with('success', "Screening scheduled for {$screening->starts_at->format('M j, Y \a\t H:i')}.");
    }

    public function edit(Screening $screening): View
    {
        $screening->load(['movie', 'auditorium']);

        return view('admin.screenings.edit', $this->formData($screening));
    }

    public function update(ScreeningRequest $request, Screening $screening): RedirectResponse
    {
        $screening->update($request->validated());

        return redirect()
            ->route('admin.screenings.index')
            ->with('success', 'The screening was updated.');
    }

    public function cancel(Screening $screening, ReservationService $reservations): RedirectResponse
    {
        try {
            $cancelledReservations = $reservations->cancelScreening($screening);
        } catch (BookingException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $message = $cancelledReservations > 0
            ? "Screening cancelled. {$cancelledReservations} confirmed reservation(s) were also cancelled."
            : 'Screening cancelled. No confirmed reservations were affected.';

        return back()->with('success', $message);
    }

    public function destroy(Screening $screening): RedirectResponse
    {
        if ($screening->reservations()->exists()) {
            return back()->with('error', 'This screening has reservation history and cannot be deleted. Cancel it instead.');
        }

        $screening->delete();

        return redirect()
            ->route('admin.screenings.index')
            ->with('success', 'The screening was removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?Screening $screening = null): array
    {
        $movies = Movie::query()
            ->published()
            ->orderBy('title')
            ->get(['id', 'title', 'runtime_minutes']);

        $auditoriums = Auditorium::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        if ($screening) {
            if (! $movies->contains('id', $screening->movie_id)) {
                $movies->push($screening->movie);
                $movies = $movies->sortBy('title')->values();
            }

            if (! $auditoriums->contains('id', $screening->auditorium_id)) {
                $auditoriums->push($screening->auditorium);
                $auditoriums = $auditoriums->sortBy('name')->values();
            }
        }

        return [
            'screening' => $screening,
            'movies' => $movies,
            'auditoriums' => $auditoriums,
        ];
    }
}
