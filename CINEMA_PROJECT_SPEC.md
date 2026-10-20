# Cinema Ticket Reservation --- Product & Technical Specification

## 1. Project purpose

Build a polished, realistic, full-stack cinema ticket reservation
application suitable for a software-engineering portfolio. Treat this as
a production-minded product, not a static UI demo. A visitor should be
able to discover films, inspect showtimes, choose seats, reserve
tickets, and review their bookings. Administrators should be able to
manage the cinema catalogue and schedules.

The AI agent is responsible for inspecting the existing repository
first, planning the implementation, building it in small verified
increments, and testing/debugging it until the agreed acceptance
criteria pass. Do not assume the repository is empty or overwrite
existing work without inspection.

## 2. Product goals and user needs

**Architecture constraint:** This project is primarily PHP/Laravel.
Preserve its existing Laravel + Blade architecture. Do not replace it
with React or a separate SPA. Before choosing any frontend library,
inspect the repository and follow its installed stack.

### Visitor / guest

-   Understand what the site offers immediately.
-   Browse current and upcoming movies.
-   Search by title and filter by genre, language, date, or availability
    where supported.
-   Open a movie detail page with poster/backdrop, synopsis, runtime,
    genres, age rating, release information, and available screenings.
-   Select a date and showtime, then view a clear seat map.
-   See available, selected, and unavailable seats distinctly.
-   See a live order summary: film, cinema/auditorium, date, time,
    selected seats, ticket count, fees if applicable, and total.
-   Complete a reservation through a safe demo checkout flow. If real
    payment integration is not configured, label the flow clearly as a
    demo and never imply that money was charged.
-   Receive a clear confirmation and reservation reference.
-   Understand what to do when a showtime sells out, a seat becomes
    unavailable, or an API request fails.

### Registered customer

-   Register, sign in, sign out, and recover from
    validation/authentication errors.
-   View their profile and reservation history.
-   Open a reservation detail and see its status, seats, screening
    information, and total.
-   Cancel a reservation only when the business rules permit it; receive
    a clear result.
-   Never view, modify, or cancel another user's reservations.

### Administrator

-   Access protected administration routes only with an authorized admin
    role.
-   Create, edit, publish/unpublish, and archive movies.
-   Manage genres and movie-genre assignments.
-   Manage cinema auditoriums and their seat layouts.
-   Create and manage screenings/showtimes, including start/end times,
    auditorium, price, and status.
-   Inspect reservations and statuses.
-   Receive server-side validation errors for invalid or conflicting
    data.
-   Avoid deleting records that are referenced by historical
    reservations; use archival or soft deletion where appropriate.

### Core quality goals

-   Mobile-first responsive experience with accessible keyboard and
    touch interactions.
-   Consistent loading, empty, success, and error states.
-   Reliable seat availability under concurrent requests.
-   Secure authorization and validation on the server.
-   Useful tests, clear setup instructions, and repeatable local
    development.

## 3. Scope and assumptions

Build the core booking journey first. Keep the first version
deliberately achievable: 1. Movie discovery and detail pages. 2.
Showtimes. 3. Seat selection. 4. Reservation creation and confirmation.
5. Authentication and reservation history. 6. Admin catalogue/screening
management. 7. Automated tests and deployment-ready configuration.

Use a simulated payment/checkout state unless a payment provider is
explicitly configured. Do not collect or store real card numbers. Do not
add loyalty points, coupons, multiple cinema chains, complex refunds, or
third-party integrations unless the repository or owner explicitly
requires them.

## 4. System design

### Recommended architecture

-   **Frontend:** Laravel + Blade, JavaScript or TypeScript according to
    the existing project configuration; Tailwind CSS v4 for styling;
    Blade/Laravel server-rendered views for server state and cache;
    Laravel Form Requests and Blade forms for forms; the project's
    existing animation approach, if any for restrained transitions; a
    chart library only if the existing project needs admin analytics
    only for useful admin analytics.
-   **Backend:** Laravel JSON API. Keep business rules in backend
    application/service layers and use Laravel validation, policies,
    Eloquent relationships, migrations, factories, seeders, and
    automated tests.
-   **Database:** Use the database already configured in the repository.
    Prefer the project's existing choice; for production-like relational
    behavior use PostgreSQL or MySQL. SQLite may be acceptable for local
    tests if the project supports it.
-   **Communication:** Frontend calls versioned JSON endpoints such as
    `/api/v1/...`. The backend is authoritative for pricing, user
    permissions, reservation status, and seat availability.
-   **Authentication:** Prefer Laravel Sanctum for a first-party SPA
    when compatible with the existing setup. Configure CSRF,
    cookie/session behavior, CORS, and credentialed requests correctly.
    Do not invent a second auth system if one already exists.
-   **Testing:** Laravel feature/unit tests for API and business rules;
    frontend tests for important UI interactions; end-to-end tests for
    the main booking journey if the repository supports them.
-   **External services:** Not required for the core version. Poster
    images can use stable seeded local/public image URLs or project
    assets. Never make the app depend on an unreliable image API for
    basic operation.

### Request flow

1.  A page requests movie/showtime/seat data through Blade/Laravel
    server-rendered views.
2.  The frontend displays loading, success, empty, or error states.
3.  Forms submit validated input to the API.
4.  Laravel authenticates the user where required, validates input,
    authorizes the action, and applies business rules.
5.  Reservation creation runs in a database transaction and
    locks/rechecks the selected seats to prevent double booking.
6.  The API returns a consistent JSON response and appropriate HTTP
    status.
7.  Blade/Laravel server-rendered views invalidates/refetches affected
    movie, showtime, seat-map, and reservation queries.
8.  The UI displays a confirmation or actionable error.

### Booking integrity rules

-   A seat belongs to an auditorium/layout; a screening uses one
    auditorium.
-   A seat can be reserved only once for a given screening while the
    reservation is active/confirmed.
-   The same physical seat may be booked for different screenings.
-   Validate that every requested seat belongs to the auditorium used by
    the selected screening.
-   Recalculate price on the server. Never trust a client-sent total.
-   Use a transaction and appropriate row locks/unique constraints when
    creating a reservation.
-   Decide and document whether unpaid reservations expire. If
    expiration is implemented, expired holds must stop blocking seats.
-   Make reservation creation safe against duplicate submissions where
    practical (idempotency token or equivalent).
-   Keep reservation status transitions explicit, e.g. `pending`,
    `confirmed`, `cancelled`, `expired`; do not let arbitrary client
    input set a status.

## 5. Domain model and database relationships

Use Laravel migrations and Eloquent models. Names can be adapted to the
existing repository, but relationships and constraints must remain
equivalent.

### `users`

-   `id`, `name`, `email` (unique), `password`, `role`
    (`customer`/`admin` or equivalent), timestamps.
-   One user has many reservations.

### `movies`

-   `id`, `title`, `slug` (unique), `synopsis`,
    `poster_path`/`poster_url`, optional `backdrop_path`,
    `runtime_minutes`, `age_rating`, `language`, `release_date`,
    `status` (`draft`, `published`, `archived`), timestamps.
-   A movie has many screenings.
-   A movie belongs to many genres.

### `genres`

-   `id`, `name`, `slug` (unique), timestamps.
-   Many-to-many with movies through `genre_movie` (or the existing
    conventional pivot table).
-   Add a unique constraint to prevent duplicate movie/genre pairs.

### `auditoriums`

-   `id`, `name`/`code` (unique within a cinema if a cinemas table
    exists), `capacity`, optional `screen_label`, `is_active`,
    timestamps.
-   One auditorium has many seats and many screenings.

### `seats`

-   `id`, `auditorium_id` FK, `row_label`, `seat_number`, `seat_type`
    (e.g. standard, premium, accessible), `is_active`, timestamps.
-   Each seat belongs to one auditorium.
-   Unique constraint on `(auditorium_id, row_label, seat_number)`.
-   A seat is not permanently owned by a reservation; availability is
    screening-specific.

### `screenings` (also called `showtimes`)

-   `id`, `movie_id` FK, `auditorium_id` FK, `starts_at`, `ends_at`,
    `base_price` as a decimal or integer minor currency units, `status`
    (`scheduled`, `cancelled`, `completed`), timestamps.
-   Each screening belongs to one movie and one auditorium.
-   A movie and an auditorium each have many screenings.
-   Validate end time is after start time and prevent overlapping
    screenings in the same auditorium, including a reasonable
    cleanup/buffer period if required.

### `reservations`

-   `id`, `user_id` FK, `screening_id` FK, `reference` (unique
    human-friendly booking code), `status`, `subtotal`, `fees`, `total`,
    `currency`, `expires_at` nullable, `confirmed_at` nullable,
    timestamps.
-   Each reservation belongs to one user and one screening.
-   One reservation has many reserved seat records.
-   Snapshot the amount paid/owed at reservation time so later price
    changes do not rewrite booking history.

### `reservation_seats` (or `reservation_items`)

-   `id`, `reservation_id` FK, `screening_id` FK if needed for enforcing
    screening-scoped uniqueness, `seat_id` FK, `unit_price`, timestamps.
-   Each row belongs to one reservation and one physical seat.
-   Enforce that the same seat cannot be held/confirmed twice for the
    same screening. A simple unique constraint on
    `(screening_id, seat_id)` works only if cancelled/expired rows are
    physically removed or the database design handles active-status
    uniqueness correctly. Otherwise use a dedicated screening-seat
    inventory/booking table or another robust transactional strategy.
-   Ensure reservation's screening and each seat's auditorium are
    consistent at application level and in tests.

### Optional `payments`

Only add if a payment lifecycle is actually implemented: - `id`,
`reservation_id` FK, provider, provider_reference nullable, amount,
currency, status (`initiated`, `succeeded`, `failed`, `refunded`),
timestamps. - Never store card numbers, CVVs, or raw payment
credentials.

### Optional audit fields

Use timestamps and, if useful, `cancelled_at`, `cancellation_reason`,
and audit logs for admin changes. Avoid adding tables without a concrete
requirement.

### Relationship summary

-   User `hasMany` Reservations.
-   Reservation `belongsTo` User and Screening; `hasMany`
    ReservationSeats.
-   ReservationSeat `belongsTo` Reservation and Seat (and Screening if
    modeled explicitly).
-   Movie `belongsToMany` Genres; Movie `hasMany` Screenings.
-   Genre `belongsToMany` Movies.
-   Screening `belongsTo` Movie and Auditorium; Screening `hasMany`
    Reservations.
-   Auditorium `hasMany` Seats and Screenings.
-   Seat `belongsTo` Auditorium; Seat participates in reservations
    through ReservationSeat, scoped to a screening.

## 6. API routes and frontend integration

Use `/api/v1` as a consistent prefix unless the existing project already
has a convention. Return JSON consistently. Paginate list endpoints. Use
HTTP status codes meaningfully and never return stack traces in
production.

### Public endpoints

-   `GET /api/v1/movies` --- paginated published movies; support
    `search`, `genre`, `status` only if safe for public use, `sort`, and
    pagination.
-   `GET /api/v1/movies/{movie}` --- movie details, genres, and relevant
    upcoming screenings.
-   `GET /api/v1/genres` --- genres and optionally counts.
-   `GET /api/v1/screenings` --- filter by movie, date, and
    auditorium/cinema if applicable.
-   `GET /api/v1/screenings/{screening}` --- screening details.
-   `GET /api/v1/screenings/{screening}/seats` --- seat layout plus
    availability (`available`, `unavailable`, `selected` is client-local
    only). Do not expose another user's private reservation details.

### Authentication endpoints

Exact routes depend on Sanctum/session setup: - `GET /api/v1/user` ---
current authenticated user. - `POST /api/v1/register` -
`POST /api/v1/login` - `POST /api/v1/logout` - Password reset endpoints
only if configured and tested.

### Customer endpoints (authenticated)

-   `GET /api/v1/reservations` --- current user's paginated
    reservations.
-   `POST /api/v1/reservations` --- create reservation with
    `screening_id` and `seat_ids`; backend calculates price and reserves
    atomically.
-   `GET /api/v1/reservations/{reservation}` --- only the owner or
    authorized admin may access it.
-   `POST /api/v1/reservations/{reservation}/cancel` --- apply
    cancellation policy server-side.
-   `POST /api/v1/reservations/{reservation}/confirm` --- only for the
    demo checkout/payment workflow, if required.

### Admin endpoints (admin authorization required)

-   `POST /api/v1/admin/movies`
-   `PATCH /api/v1/admin/movies/{movie}`
-   `DELETE /api/v1/admin/movies/{movie}` or prefer archive where
    historical links exist.
-   CRUD for `/api/v1/admin/genres`
-   CRUD for `/api/v1/admin/auditoriums` and seats/layouts
-   CRUD for `/api/v1/admin/screenings`
-   `GET /api/v1/admin/reservations`
-   Optional dashboard summary endpoint only when the dashboard needs
    it.

Do not implement every endpoint blindly. Match endpoints to actual
screens and the current repository. Use Laravel API Resources or an
equivalent consistent response layer.

### Response conventions

Example success:

``` json
{
  "data": {
    "id": 42,
    "reference": "CIN-7K4P2",
    "status": "confirmed",
    "total": 240000,
    "currency": "IRR"
  },
  "message": "Reservation confirmed."
}
```

Example validation error:

``` json
{
  "message": "The given data was invalid.",
  "errors": {
    "seat_ids": ["One or more selected seats are no longer available."]
  }
}
```

Use Laravel's conventional validation format unless the existing API has
a documented standard. Do not wrap every response in a custom envelope
if that conflicts with the project's current convention.

### Frontend data ownership

-   Blade/Laravel server-rendered views owns remote/server state:
    movies, genres, screenings, seat availability, user, reservations,
    admin data.
-   JavaScript interaction state owns transient UI state: selected date,
    currently selected seats, modal visibility, open navigation, sort
    panel.
-   Do not duplicate the same API data in global state without a clear
    reason.
-   Define one reusable API client for base URL, credentials/CSRF, JSON
    parsing, and normalized errors.
-   Use query keys that include relevant filters/IDs.
-   After creating/cancelling a reservation, invalidate/refetch the
    reservation list, reservation detail, and seat availability for that
    screening.
-   Show API validation errors next to the relevant fields or in an
    accessible summary.
-   Never treat frontend-only seat selection as a reservation; seats are
    secured only after the backend confirms success.

## 7. Pages and UX specification

### Public pages

1.  **Home / Now Showing:** clear brand/header, primary discovery
    message, featured/current movies, genre or date shortcuts, upcoming
    section, and footer.
2.  **Movie listing/search:** responsive movie cards, search, filters,
    sort, pagination or "load more," clear no-results state.
3.  **Movie detail:** cinematic poster/backdrop, title and metadata
    hierarchy, synopsis, genre tags, runtime, age rating, and
    date-grouped showtimes.
4.  **Screening / seat selection:** movie and screening summary, legible
    seat grid, legend, selected-seat summary, total, continue button,
    and a clear unavailable/expired state.
5.  **Checkout / confirmation:** selected booking summary, simulated
    payment disclosure where applicable, validation, submission state,
    success reference, and next steps.
6.  **Login/register:** simple forms, inline validation, password
    visibility control where appropriate, and helpful errors.
7.  **My reservations:** upcoming/past/cancelled grouping, booking
    reference, date/time, seats, status, and detail/cancel actions where
    permitted.
8.  **Reservation detail:** complete booking information and policy.
9.  **Not found / error states:** friendly message and route back to
    useful content.
10. **Admin:** usable forms/tables for movies, genres, auditoriums,
    screenings, and reservations. Keep admin visual language consistent
    with the product.

### Visual direction

Create a modern cinematic identity: dark, immersive surfaces and
high-contrast content, with poster artwork carrying color. Use a
restrained accent color for primary actions and selected seats; do not
let gradients, glow, or animation compete with the film artwork. The
exact palette should follow any existing design assets in the
repository. Establish design tokens for background, surface, text, muted
text, borders, primary, success, warning, and danger.

-   Use consistent spacing, type scale, border radii, shadows, and icon
    style.
-   Poster cards should have consistent aspect ratios and graceful image
    fallbacks.
-   Avoid generic dashboard templates on public-facing pages.
-   Do not use emoji as substitutes for core interface icons.
-   Motion should be subtle, purposeful, and respect
    `prefers-reduced-motion`.
-   Do not invent a desktop-only design; make the mobile booking journey
    excellent.

### Accessibility

-   Semantic landmarks and headings in order.
-   Every input has a label; validation errors are associated with
    fields.
-   Full keyboard operation, visible focus, and no keyboard traps.
-   Sufficient contrast; do not convey seat status by color
    alone---include labels/legend and accessible names.
-   Buttons have clear names and correct disabled/loading states.
-   Dialogs manage focus and close predictably.
-   Respect reduced-motion settings and screen-reader announcements for
    booking results.
-   Use alt text for meaningful movie artwork and empty alt text for
    purely decorative imagery.

## 8. Make the product user-friendly

-   Make the next action obvious on every page.
-   Keep the booking journey short and show progress when it has
    multiple steps.
-   Preserve chosen date/seats while navigating backward where safe, but
    revalidate availability before booking.
-   Always show the total and selected seat count before final
    submission.
-   Explain unavailable seats and errors in plain language, with a
    recovery action.
-   Prevent double-click duplicate submissions and indicate in-progress
    actions.
-   Ask for confirmation before destructive or costly actions.
-   Use sensible defaults, but never silently choose seats or submit a
    booking on the user's behalf.
-   Use skeletons or restrained placeholders for loading, not
    layout-jumping blank pages.
-   Include useful empty states and retry actions.
-   Keep search and filters easy to clear.
-   Format date, time, and currency consistently for the target locale;
    keep formatting centralized.
-   Make touch targets comfortable on mobile.
-   Avoid surprise auto-playing audio/video, intrusive popups, and
    excessive animations.
-   Show only information needed for the current task; reveal secondary
    details progressively.
-   Do not show fake reviews, fake availability, fake payment success,
    or fabricated user counts.

## 9. Test and debug until ready

The agent must not stop when the app merely compiles. Use a repeated
cycle: inspect → plan → implement a small slice → run checks → fix
failures → retest → summarize.

### Required checks

-   Install/use dependencies only after inspecting package/composer
    files and existing lockfiles.
-   Run format/lint checks if configured.
-   Run tests for Blade/form/JavaScript interactions if configured.
-   Run Laravel unit/feature tests and migrations against a clean test
    database.
-   Run the project's production asset build if configured (for example,
    `npm run build`).
-   Run an end-to-end smoke test of the main booking journey if
    Playwright/Cypress or equivalent is available.
-   Verify public, customer, and admin authorization boundaries.
-   Check responsive behavior at narrow mobile, tablet, and desktop
    widths.
-   Inspect browser console, network requests, backend logs, and failed
    API responses.
-   Verify no secrets, `.env`, private credentials, or production data
    are committed.

### Minimum acceptance tests

1.  Published movies load; unpublished movies are not exposed publicly.
2.  Search/filter/pagination work together and empty results are
    handled.
3.  Movie details show correct genres and upcoming screenings.
4.  Seat map correctly reflects unavailable seats.
5.  Invalid, inactive, or foreign-auditorium seat IDs are rejected.
6.  Two concurrent attempts to reserve the same seat cannot both
    succeed.
7.  Server computes price; tampering with client price has no effect.
8.  Guest cannot access customer reservation endpoints.
9.  Customer cannot access another customer's reservation.
10. Non-admin cannot call admin endpoints.
11. Successful reservation returns a unique reference and appears in
    history.
12. Cancellation obeys policy and releases inventory correctly if the
    policy says it should.
13. Duplicate submit does not create duplicate bookings.
14. Validation and server errors are understandable and do not expose
    internals.
15. The main flow works on mobile and with keyboard navigation.
16. A clean install, migration, seed, test, and production build can be
    reproduced from documented commands.

### Completion report

At the end, report: - What was implemented. - Important architectural
choices and assumptions. - Commands run and their pass/fail results. -
Tests not run and why. - Known bugs or limitations. - How to install,
configure, seed, test, and run the project. - Any manual verification
still required.

Do not claim a test passed unless it was actually run. Do not hide
failures or silently skip a failing check.
