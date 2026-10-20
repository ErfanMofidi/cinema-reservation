# Cinema Project — Repository Audit

Date: 2026-10-21
Auditor: Implementation agent
Repository: `C:\Users\Arian.Tech\Herd\cinema-ai-project`

## 1. Executive summary

The repository is a **fresh Laravel 13 application skeleton**, not an existing cinema application.
It contains the default Laravel welcome page, the default user/cache/jobs migrations, one example
factory/seeder, and two placeholder tests. There is no cinema domain, authentication UI,
authorization role, administration area, booking flow, or design system to preserve.

The three project documents exist and were read in full:

- `CINEMA_PROJECT_SPEC.md`
- `CINEMA_AGENT_RULES.md`
- `CINEMA_AGENT_SKILLS.md`

The practical consequence is that the cinema product described in the specification must be built
from the skeleton. The Laravel/Blade architecture is preserved; no React or SPA migration is needed
or permitted.

## 2. Verified technology stack

| Area | Verified value |
| --- | --- |
| Framework | Laravel **13.37.0** |
| PHP | **8.4.25** (NTS, Herd PHP build) |
| Composer | 2.10.2 |
| Node / npm | 22.15.0 / 10.9.2 |
| Database | SQLite (`database/database.sqlite`), three default migrations already run |
| Frontend | Blade, Tailwind CSS **v4** via `@tailwindcss/vite`, Vite **8.3.4**, vanilla JS entry |
| Test framework | Pest **5.3** with Laravel plugin |
| Formatter | Laravel Pint 1.27 installed |
| Extra dev tooling | Laravel Boost 2.2, Pail, Pao, Collision |
| API scaffolding | **None** — no `routes/api.php`, no Sanctum, no API middleware |
| Auth scaffolding | **None** — only the default `User` model and users migration |
| Assets | Instrument Sans font downloaded by the Vite font plugin; no project images |
| Git | Branch `main`, one commit `78604f3`, working tree clean before this work |

Verified commands and results:

- `php artisan test` → 2 passed, 2 assertions.
- `npm run build` → successful Vite production build.
- `php artisan migrate:status` → default migrations ran in batch 1.
- `php artisan route:list` → only `/`, health check, storage routes, and Boost browser-log route.

## 3. Existing implementation

### Application code

- `app/Models/User.php` — default model using Laravel 13's `#[Fillable]` / `#[Hidden]` attributes.
- `app/Providers/AppServiceProvider.php` — empty.
- `app/Http/Controllers/Controller.php` — empty base controller.
- No middleware, policies, form requests, enums, services, actions, or view components.

### Routes and flows

- `routes/web.php` — one closure returning `welcome`.
- `routes/console.php` — the default `inspire` command.
- No authentication, customer, admin, JSON, or booking routes exist.
- No route names beyond framework routes.

### Database

Default schema only:

- `users` — id, name, email, password, remember token, timestamps.
- `password_reset_tokens`, `sessions`.
- `cache`, `cache_locks`.
- `jobs`, `job_batches`, `failed_jobs`.

No roles, movies, genres, auditoriums, seats, screenings, reservations, reservation seats, or
seat-hold inventory tables exist.

### Views, CSS, JavaScript

- `resources/views/welcome.blade.php` — the stock Laravel 13 welcome page with inline fallback CSS.
- `resources/css/app.css` — Tailwind import and Instrument Sans theme font only.
- `resources/js/app.js` — empty.
- No layouts, Blade components, design tokens, icons, or project artwork.

### Tests

- `tests/Feature/ExampleTest.php` — homepage responds 200.
- `tests/Unit/ExampleTest.php` — trivial truth assertion.
- `phpunit.xml` already configures SQLite `:memory:` for tests.
- Pest is bound to the Laravel test case for feature tests; `RefreshDatabase` is commented out.

### Environment and safety

- `.env` exists and is ignored by Git. It was not printed during the audit.
- `.env.example` is the stock Laravel file (SQLite, database sessions/cache/queue).
- App timezone is hard-coded to UTC in `config/app.php`.
- `APP_DEBUG=true` in local environment only; no evidence of production configuration.
- No committed secrets were observed. `.env` and `vendor`, `node_modules`, `public/build` are ignored.
- `README.md` is the stock Laravel README and contains no project-specific setup instructions.

## 4. Findings: gaps against the specification

Everything below is **missing** unless explicitly marked partial.

### Domain and persistence

- No role-aware users.
- No movies, genres, pivot table, auditoriums, seats, screenings, reservations, seat inventory,
  or pricing snapshots.
- No constraints for unique seat holds, screening overlap prevention, or historical protection.
- No factories or realistic seed data.
- No locale/currency/timezone policy for the target cinema.

### Customer experience

- No movie discovery, search, filters, pagination, or movie detail pages.
- No screening selection, seat map, seat legend, live total, or unavailable states.
- No checkout/confirmation flow, reservation history, cancellation, or reservation reference.
- No registration, login, logout, profile, or password management.
- No responsive or accessible design system.

### Administration

- No protected admin area.
- No movie/genre/auditorium/seat/screening/reservation management.
- No validation for screening times, overlaps, or historical deletions.
- No dashboard.

### Engineering controls

- No policies or admin middleware.
- No Form Requests.
- No booking transaction or race-condition protection.
- No rate limiting for login or booking.
- No security headers.
- No cinema-specific feature tests.
- No deployment/run documentation for this product.

## 5. Detected risks and architectural weaknesses

| Risk | Severity | Note |
| --- | --- | --- |
| Entire product absent | Critical | Expected; this is a build-from-skeleton project, not a repair. |
| Seat overselling if naive | Critical | Must use DB transaction plus a unique screening/seat hold constraint; frontend checks are insufficient. |
| IDOR on reservations | High | Must authorize every show/cancel action against ownership/admin role. |
| Mass assignment / role escalation | High | Registration must never accept `role`; admin forms must use validated fields only. |
| Screening overlap | Medium | Must validate start/end and same-auditorium overlaps server-side. |
| Price tampering | High | Server must compute unit prices from screening + seat type. |
| Duplicate submit | Medium | Session-scoped checkout plus idempotency key and unique seat holds. |
| SQLite concurrency | Medium | Unique hold constraint is the final authority; document that production should use MySQL/PostgreSQL. |
| Stale seat state | Medium | Refresh JSON endpoint plus final revalidation inside the booking transaction. |
| No mail configuration | Low | Password reset email is not implemented; profile password change is provided instead. |
| Debug mode | Low | Local only; deployment docs require `APP_DEBUG=false`. |
| No CSP | Low | Basic security headers will be added; CSP is documented as a deployment hardening item. |

## 6. Compatibility notes

- Laravel 13 uses model attributes such as `#[Fillable([...])]` and `#[Hidden([...])]` instead of
  the older `$fillable` / `$hidden` properties. New models will follow the installed convention.
- `bootstrap/app.php` uses the Laravel 11+ application builder. Middleware aliases and exception
  handling will be registered there.
- Tailwind v4 is configured through the Vite plugin and `@theme`; there is no
  `tailwind.config.js`. Design tokens belong in `resources/css/app.css`.
- No Breeze/Jetstream/UI package is installed. Authentication will be implemented with Laravel's
  built-in session guard, controllers, Form Requests, and Blade views. No new dependency is needed.
- No Sanctum is installed. The customer UI is session-authenticated, so Sanctum is unnecessary.
- The spec recommends a JSON API, while the master prompt explicitly requires Blade web routes and
  forms and allows JSON only where it provides value. The implementation is **Blade-first**, with a
  single read-only JSON endpoint for live seat availability. This is the deliberate architecture
  decision for this repository. The backend remains authoritative for price, availability, status,
  and authorization.

## 7. Product decisions adopted (reversible)

These are not inferred as facts about existing code; they are documented decisions for the build.

1. **Cinema identity:** one cinema, English interface, currency `IRR`, cinema timezone
   `Asia/Tehran`. Configurable through `config/cinema.php` / `.env`.
2. **Checkout:** demo checkout only. Confirming a reservation creates a `confirmed` reservation
   without collecting card data or claiming a payment occurred.
3. **Booking cutoff:** reservations close 15 minutes before a screening starts.
4. **Cancellation:** a customer may cancel their own `confirmed` reservation up to 120 minutes
   before the screening starts. Cancellation releases held seats and keeps the booking record.
5. **Seat inventory:** `seat_holds` is the authoritative active inventory with a unique
   `(screening_id, seat_id)` constraint. `reservation_seats` stores the historical price snapshot.
6. **Pricing:** screening base price plus a server-side multiplier for premium seats. Accessible
   seats use the base price. No hidden fees; `fees = 0`.
7. **Seat limit:** 8 seats per reservation.
8. **Deletion policy:** movies, genres, and auditoriums with historical references are archived or
   guarded, not hard-deleted. Screenings can be cancelled; reservations are never hard-deleted.
9. **Admin demo credentials:** seeded development users will be documented in the README; they are
   not production credentials.
10. **Scope exclusions:** no real payments, refunds, loyalty, coupons, multiple cinema chains,
    external movie APIs, email verification, or password reset email. These limitations will be
    stated in the README.

## 8. Recommended implementation sequence

1. Configuration, enums, migrations, models, factories, and seed data.
2. Session authentication, roles, admin middleware, and policies.
3. Public discovery, search, filters, movie details, and screening schedule.
4. Accessible seat map, availability JSON, checkout, transactional reservation creation.
5. Reservation history, detail, cancellation, and ownership protection.
6. Admin dashboard and CRUD for catalogue, auditoriums, screenings, and reservation inspection.
7. Visual polish, responsive/accessibility pass, and error pages.
8. Feature/unit tests, security review, formatting, build, and documentation.

## 9. Assumptions and unresolved questions

- No existing visual identity or assets exist, so a new cinematic design system will be created.
- The target locale is not stated. `IRR`/`Asia/Tehran` follows the specification's example currency
  and the local project context; changing `config/cinema.php` and `APP_TIMEZONE` is sufficient to
  adapt it.
- Whether real payments are expected is unresolved, but the specification allows a clearly labelled
  demo flow. No payment provider is configured, so none will be simulated as a real charge.
- SQLite is appropriate for local verification. Production should use MySQL/PostgreSQL for stronger
  row locking; the unique inventory constraint preserves correctness on SQLite as well.
- No blocking clarification is required to proceed with the reversible decisions above.

## 10. Post-implementation status

The gaps recorded in section 4 were addressed by the implementation. As of the final verification:

- The cinema schema, enums, models, factories, and repeatable seed data exist; migrations and
  seeding succeed on local SQLite.
- Session authentication, customer/admin roles, the admin middleware, and reservation policy are in
  place.
- Public discovery, search/filter/sort/pagination, movie details, and screening schedules are
  implemented.
- The seat map, live availability endpoint, checkout, transactional booking, idempotency, and
  server-authoritative pricing are implemented.
- Reservation history, detail, and policy-based cancellation are implemented.
- Admin dashboard, movie/genre/auditorium/screening management, archive/delete guards, and
  reservation inspection are implemented.
- The Blade/Tailwind design system, responsive layouts, accessible seat controls, dialogs, and
  error pages are implemented.
- The Pest suite covers the acceptance-critical paths; Pint and the production Vite build pass.
- `README.md` documents installation, demo credentials, configuration, architecture, security, and
  remaining limitations.

Remaining limitations are intentional and documented in `README.md`: demo checkout only, no email
verification or password-reset email, no public API, no CSP, no multi-process concurrency proof,
and no automated browser/accessibility testing.
