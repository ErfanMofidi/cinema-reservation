# Cinema Project — Implementation Plan

Date: 2026-10-21
Based on: `PROJECT_AUDIT.md`, `CINEMA_PROJECT_SPEC.md`, `CINEMA_AGENT_RULES.md`

## Architecture decisions

| Decision | Reasoning |
| --- | --- |
| Laravel 13 + Blade + Tailwind v4 + vanilla JS | Matches the installed repository and the mandatory no-SPA constraint. |
| Session authentication using Laravel's built-in guard | No auth scaffolding or Sanctum is installed; adding a new auth stack is unnecessary. |
| Blade web routes and HTML forms for navigation and writes | Master prompt requires server-rendered flows; JSON is limited to the live seat map. |
| `GET /screenings/{screening}/seats.json` | Provides a real benefit: refreshing availability without a full page reload. The final booking still revalidates inside a transaction. |
| `seat_holds` inventory table with unique `(screening_id, seat_id)` | Active availability cannot be represented safely by a partial unique index across SQLite/MySQL; a dedicated hold table is portable, explicit, and testable. |
| Historical `reservation_seats.unit_price` | Preserves the price snapshot when screening prices change later. |
| Server-side pricing from `screenings.base_price` + seat-type multiplier | Client-submitted totals are never read. |
| Transaction + `lockForUpdate` + unique hold insert | Prevents two customers from securing the same screening/seat. |
| Movie/auditorium archival instead of destructive deletion | Protects historical reservations and screenings. |
| Demo checkout, immediate confirmation | The specification permits a simulated flow. No card data is collected and no payment success is fabricated. |
| Custom design system instead of an admin template | Keeps public and admin surfaces coherent and avoids new dependencies. |

## Final status

All eight milestones were implemented. The table records the verification state at the end of the build.

| Milestone | Status | Verification |
| --- | --- | --- |
| 1 — Foundation, schema, models, seed | Complete | Migrations and seed ran against local SQLite; domain tests pass |
| 2 — Authentication, roles, authorization | Complete | Auth, rate-limit, middleware, and policy tests pass |
| 3 — Public discovery and schedule | Complete | Public page, filter, visibility, and seeded-page tests pass |
| 4 — Seat map, checkout, atomic booking | Complete | Booking, pricing, conflict, and idempotency tests pass |
| 5 — Reservation history and cancellation | Complete | Ownership, cancellation, and hold-release tests pass |
| 6 — Administration | Complete | Admin CRUD, validation, guard, and authorization tests pass |
| 7 — Design and accessibility polish | Complete | Production build passes; manual responsive/keyboard review still recommended |
| 8 — Verification and documentation | Complete | 75 tests / 394 assertions, Pint, and Vite build verified; README and audit updated |

Deliberate adjustments from the original plan are listed below so the document matches the delivered code:

- Seat selection is restored after login through Laravel's `redirect()->intended()` and the seat map's `?seats=` query parameter, not `sessionStorage`.
- Checkout uses a single `StoreCheckoutRequest`; the planned separate `StoreReservationRequest` was unnecessary because validation and authorization are handled in one request class.
- The JSON surface is a single read-only availability endpoint, not a versioned public API.
- The `reservation_seats` price snapshot and unique `seat_holds` inventory are implemented as planned.

## Milestone 1 — Foundation, schema, domain models, seed data

**Objective:** Establish configuration, enums, migrations, Eloquent relationships, factories, and
repeatable development data.

**Dependencies:** None.

**Expected files/components:**

- `config/cinema.php`, `config/app.php` timezone.
- `app/Enums/{UserRole,MovieStatus,ScreeningStatus,ReservationStatus,SeatType}.php`.
- Migrations: add role to users; create genres, movies, genre_movie, auditoriums, seats, screenings,
  reservations, reservation_seats, seat_holds.
- Models with relationships, scopes, casts, and pricing method.
- Factories for every domain model.
- `DatabaseSeeder` and dedicated seeders for cinema data and demo users.
- Local poster/backdrop SVG assets and fallback artwork.
- `.env.example` cinema settings.

**Acceptance criteria:**

- `php artisan migrate:fresh --seed` succeeds on the local SQLite database.
- Seeded data includes genres, two auditoriums with generated seats, published/upcoming movies, a
  week of non-overlapping screenings, demo users, and sample reservations.
- Unique constraints exist for movie slug, genre slug, seat position, screening/seat holds, and
  reservation reference/idempotency key.
- Foreign-key deletion behavior protects historical data.
- Models expose the relationships required by the specification.

**Tests/checks:** migration run, targeted model relationship tests, seed idempotency on a fresh DB.

**Risks:** SQLite limitations on concurrent locks; screening overlap must still be validated in the
admin layer. Seeder dates must be relative to `now()` so the demo never expires.

## Milestone 2 — Authentication, roles, and authorization boundaries

**Objective:** Registration, login, logout, profile password change, admin middleware, and reservation
policy.

**Dependencies:** Milestone 1 (user role).

**Expected files/components:**

- Auth controllers and Form Requests.
- Login rate limiter.
- `EnsureUserIsAdmin` middleware alias.
- `ReservationPolicy`.
- Auth layout and login/register/profile views.
- Profile routes.
- Security headers middleware.

**Acceptance criteria:**

- Guests can register and sign in; validation errors render inline.
- Registration always creates a `customer`, regardless of submitted fields.
- Admin routes reject non-admin users with 403; guests redirect to login.
- Customers cannot view or cancel another customer's reservation.
- Login attempts are rate limited.
- Session is regenerated on login; logout invalidates the session and CSRF token.

**Tests:** registration, duplicate email, login success/failure, rate limit, logout, role middleware,
policy matrix, cross-user access, security headers.

**Risks:** Registration role escalation; open redirect; session fixation. Avoid user-controlled
redirects and never accept `role`.

## Milestone 3 — Public discovery and screening schedule

**Objective:** Home, movie index/search/filter, movie detail, and upcoming screenings.

**Dependencies:** Milestones 1–2 for data and layout.

**Expected files/components:**

- `HomeController`, `MovieController`, `ScreeningController`.
- Public layout, header/footer, movie card, poster, badges, pagination.
- Home, movies index, movie detail, screening page shell.
- Published/upcoming scopes and query filters.
- Custom error pages.

**Acceptance criteria:**

- Only published movies appear publicly; drafts/archived return 404 on detail routes.
- Search, genre filter, sort, and pagination compose correctly.
- Movie details show genres, metadata, and future scheduled screenings grouped by date.
- Empty search results have a clear state and a clear-filters action.
- Upcoming (not-yet-released) films are distinguishable from now-showing films.

**Tests:** public visibility matrix, filters/search/pagination, detail rendering, upcoming screening
query, empty states, escaping.

**Risks:** N+1 queries; date grouping in the cinema timezone; unpublished data leaks through route
binding.

## Milestone 4 — Seat map, availability endpoint, checkout, atomic booking

**Objective:** Deliver the core booking journey end-to-end.

**Dependencies:** Milestones 1–3.

**Expected files/components:**

- Seat map page with accessible checkbox-based seats.
- `GET /screenings/{screening}/seats.json`.
- Checkout preview route and view.
- `ReservationService` (atomic create/cancel).
- `StoreCheckoutRequest`.
- Booking exception types and friendly error handling.
- Seat map JavaScript module: selection, live summary, availability refresh, duplicate-submit guard,
  intended-URL selection recovery after login.
- Reservation show/confirmation page.

**Acceptance criteria:**

- A guest can select seats but is asked to sign in before checkout; selection is restored.
- The selected-seat summary and server-calculated total agree.
- The backend rejects foreign-auditorium, inactive, already-held, duplicate, oversized, and
  non-existent seat selections.
- Price is computed from the screening and seat type; client price/status/user fields are ignored.
- Two attempts on the same seat cannot both succeed.
- Duplicate submit returns the same reservation instead of creating a second one.
- The confirmation page shows a unique `CIN-XXXXXX` reference and demo-checkout disclosure.
- Expired/started/cancelled screenings cannot be booked.

**Tests:** seat map availability, guest redirect, validation matrix, price snapshot, duplicate seat,
second-customer conflict, idempotency, booking cutoff, cancelled/inactive screening, reference
uniqueness, relationship consistency, double-submit behavior.

**Risks:** Race conditions; stale seat selection; session loss between checkout and confirmation;
user tampering with hidden inputs. Mitigate with transaction, unique constraint, session-scoped
checkout, and full revalidation.

## Milestone 5 — Reservation history, detail, cancellation

**Objective:** Customer reservation management with explicit status and policy.

**Dependencies:** Milestone 4.

**Expected files/components:**

- Reservation index/show/cancel controllers.
- Policy-backed cancellation.
- Reservation list grouping and detail view.
- Cancel confirmation dialog.

**Acceptance criteria:**

- A customer sees only their reservations, paginated and grouped by upcoming/past/cancelled.
- Detail pages are owner/admin only.
- Cancellation works only for confirmed reservations before the cutoff; holds are released and the
  seat can be booked again.
- Cancelled reservations remain visible with their seat and price history.
- Guest access redirects to login; cross-user access is refused.

**Tests:** list ownership, show ownership, admin view, cancel success/release, cancel too late,
cancel already cancelled, cannot cancel another user's booking.

**Risks:** Releasing inventory twice; stale cancellation UI. Use a transaction and status guard.

## Milestone 6 — Administration

**Objective:** Functional, protected management of the cinema catalogue and schedule.

**Dependencies:** Milestones 1–5.

**Expected files/components:**

- Admin layout/sidebar and dashboard.
- Admin movie CRUD + archive; genre CRUD; auditorium CRUD with seat generation; screening CRUD +
  cancel; reservation inspection.
- Form Requests, custom screening overlap rule, guarded deletes.
- Admin pagination/filtering.

**Acceptance criteria:**

- Non-admins cannot reach any admin route.
- Movie create/edit validates slug uniqueness, poster path safety, status, runtime, and dates.
- Genre deletion is refused when referenced.
- Auditorium creation generates unique seats; referenced auditoriums cannot be deleted.
- Screenings validate end-after-start, active auditorium, and overlaps in the same auditorium,
  including a 15-minute buffer.
- Reservations with history cannot be destroyed; screenings with reservations can be cancelled but
  not deleted.
- Every admin form reports validation errors next to fields.
- Dashboard and reservation inspection show accurate aggregate data.

**Tests:** admin CRUD authorization, validation, archive behavior, seat generation, overlap rule,
delete guards, reservation filters, dashboard query correctness.

**Risks:** Invalid schedule creation; accidental destructive actions; duplicate movie/genre slugs.
Guard at validation and database level.

## Milestone 7 — Design, accessibility, responsive polish

**Objective:** Make the complete journey polished, coherent, and usable on mobile and with a keyboard.

**Dependencies:** All feature milestones.

**Expected files/components:**

- Tailwind v4 theme tokens and component classes.
- Responsive header/navigation, cinematic hero, poster cards, admin tables.
- Focus states, reduced-motion support, accessible seat legend/labels, skip link.
- Flash messages, empty/error/loading states, 403/404/419/500 pages.
- Confirmation dialog with focus management.
- Local artwork fallbacks.

**Acceptance criteria:**

- No horizontal overflow at 320 px, 768 px, and 1280 px.
- Seat map is operable with keyboard and screen readers; status is not conveyed by color alone.
- All form controls have labels and associated errors.
- Focus is visible; dialogs close predictably and restore focus.
- Reduced-motion preference is respected.
- No fake metrics, reviews, or payment claims.

**Tests/checks:** build, manual viewport review, keyboard walkthrough, HTML structure review, contrast
spot checks.

**Risks:** Seat map horizontal scrolling on tiny screens; admin tables overflowing. Use wrappers and
responsive stacking.

## Milestone 8 — Verification, security review, and documentation

**Objective:** Prove the implementation and leave accurate operating instructions.

**Dependencies:** All previous milestones.

**Expected files/components:**

- Complete Pest suite.
- `README.md` replacement preserving the Laravel attribution where useful.
- Final audit/implementation notes and known limitations.
- Pint formatting, production build, route list, migration check.

**Acceptance criteria:**

- Relevant tests pass; failures are fixed or honestly reported.
- Pint passes on all application/test files (or remaining style issues are reported).
- Production build succeeds.
- README documents prerequisites, setup, seeding, demo credentials, test commands, architecture,
  security decisions, and limitations.
- No secrets are committed.

**Risks:** Time-dependent tests, SQLite concurrency test limitations, missing browser automation.
Use `freezeTime()`, sequential conflict tests, and document that true multi-process race testing was
not performed.

## Dependency graph

```text
M1 schema/models/seed
  ├─ M2 auth/roles/policies
  │    ├─ M3 public discovery
  │    │    └─ M4 seat map/checkout/booking
  │    │         ├─ M5 reservations/cancellation
  │    │         └─ M6 admin management
  │    └─ M6 admin management
  └─ M7 design polish (incremental, finalized after M6)
                 └─ M8 verification/docs
```

## Definition of done for the project

A feature counts as complete only when its route, validation, authorization, persistence, failure
states, UI, and tests are all in place. Local verification will be reported separately from
production readiness. Real payments, email delivery, and a professional security audit remain out of
scope and will be documented as such.
