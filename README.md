# Lumière Cinema

A complete, server-rendered cinema reservation application built with **Laravel 13**, **Blade**, **Tailwind CSS v4**, and vanilla JavaScript.

Customers can browse the programme, filter films, inspect showtimes, pick seats from a live seat map, and reserve tickets through a clearly labelled demo checkout. Staff can manage the catalogue, auditoriums, screenings, and reservations from a protected admin area.

> **Demo checkout:** no payment provider is integrated and no card data is collected. Confirming a booking creates a `confirmed` reservation immediately and the interface states that no payment was taken. Do not present this project as a production ticketing or payment system.

---

## Features

### Customer experience

- Home page with now-showing and upcoming films.
- Movie catalogue with search, genre filter, sorting, and pagination.
- Movie detail pages with genres, runtime, certificate, synopsis, and upcoming screenings grouped by date.
- Screening pages with an accessible, keyboard-operable seat map.
- Live availability refresh from a JSON endpoint, with a manual refresh control.
- Server-calculated pricing per seat type; the browser never supplies the charged amount.
- Demo checkout with explicit seat revalidation inside a database transaction.
- Unique booking references in the `CIN-XXXXXX` format.
- Reservation history grouped into upcoming, past, and cancelled bookings.
- Reservation detail pages and policy-based online cancellation.
- Registration, login, logout, and profile/password management.
- Responsive public and admin layouts, visible focus states, reduced-motion support, and custom error pages.

### Administration

- Protected dashboard with catalogue and booking metrics.
- Movie management, including archive instead of destructive deletion.
- Genre management with guards against deleting referenced genres.
- Auditorium management with generated seat layouts and layout regeneration.
- Screening management with end-time assistance, overlap validation, cancellation, and guarded deletion.
- Reservation inspection and filtering.
- Admin-only middleware and ownership-aware policies.

---

## Technology

| Area | Choice |
| --- | --- |
| Framework | Laravel 13 |
| Language | PHP 8.3+ |
| Views | Blade components and layouts |
| Styling | Tailwind CSS v4 through the Vite plugin |
| Client JavaScript | Vanilla ES modules |
| Build tool | Vite 8 |
| Database | SQLite for local development and tests |
| Tests | Pest 5 with the Laravel plugin |
| Formatter | Laravel Pint |

No SPA framework, auth starter kit, Sanctum, or payment SDK is installed.

---

## Requirements

- PHP **8.3+** with the `pdo_sqlite` extension
- Composer 2
- Node.js **22+** and npm
- A writable `database/` directory

The project was verified with PHP 8.4.25, Composer 2.10.2, Node 22.15.0, and npm 10.9.2.

---

## Getting started

### 1. Install dependencies and configure the environment

```bash
composer install
cp .env.example .env
php artisan key:generate
```

On PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### 2. Create the SQLite database, migrate, and seed

```bash
touch database/database.sqlite
php artisan migrate --seed
```

On PowerShell:

```powershell
New-Item -ItemType File -Path database/database.sqlite -Force
php artisan migrate --seed
```

The seeder is repeatable and creates:

- 7 published/upcoming films and 9 genres
- 2 auditoriums with 96 and 60 seats
- Roughly one week of non-overlapping screenings relative to the current date
- Demo admin and customer accounts
- Three demo reservations (upcoming, past, and cancelled)

> `composer setup` installs dependencies, creates `.env`, generates the key, migrates, and builds assets, but it does **not** seed data. Run `php artisan migrate --seed` afterwards if you want the demo catalogue.

### 3. Build front-end assets

```bash
npm install
npm run build
```

### 4. Run the application

```bash
php artisan serve
```

Open <http://localhost:8000>.

For a combined development process (server, queue listener, logs, and Vite):

```bash
composer dev
```

---

## Demo accounts

These accounts are created by `php artisan db:seed` and exist only for local demonstration.

| Role | Email | Password |
| --- | --- | --- |
| Administrator | `admin@cinema.test` | `Cinema123!` |
| Customer | `customer@cinema.test` | `Cinema123!` |

The customer account owns the three seeded reservations. The admin account can access `/admin`.

**Never use these credentials in a deployed environment.** Replace or remove the demo seeder before exposing the application publicly.

---

## Configuration

Cinema behaviour is configured in `config/cinema.php` and can be overridden through `.env`:

| Variable | Default | Purpose |
| --- | --- | --- |
| `CINEMA_NAME` | `Lumière Cinema` | Display name used across the UI |
| `CINEMA_TAGLINE` | `Stories worth leaving the house for.` | Home-page tagline |
| `CINEMA_CURRENCY` | `IRR` | Currency label for prices |
| `CINEMA_BOOKING_CUTOFF_MINUTES` | `15` | Minutes before showtime that online booking closes |
| `CINEMA_CANCELLATION_CUTOFF_MINUTES` | `120` | Minutes before showtime that online cancellation closes |
| `CINEMA_MAX_SEATS` | `8` | Maximum seats per reservation |
| `CINEMA_SCREENING_BUFFER_MINUTES` | `15` | Required buffer between screenings in one auditorium |
| `CINEMA_BOOKING_FEE` | `0` | Flat booking fee in whole currency units |
| `APP_TIMEZONE` | `Asia/Tehran` | Timezone used to display and group showtimes |

Prices are stored as whole currency units (integers). Seat prices are derived from the screening's base price and a server-side seat-type multiplier:

| Seat type | Multiplier |
| --- | --- |
| Standard | 1.00 |
| Premium | 1.25 |
| Accessible | 1.00 |

There are no hidden fees. `fees` is stored explicitly on each reservation and is `0` by default.

---

## Booking rules

| Rule | Behaviour |
| --- | --- |
| Booking cutoff | Online booking closes 15 minutes before the screening starts |
| Cancellation cutoff | Customers may cancel up to 120 minutes before the screening starts |
| Seat limit | Up to 8 seats per reservation |
| Seat inventory | A unique `(screening_id, seat_id)` row in `seat_holds` is the authoritative active inventory |
| Pricing | Computed server-side from the screening and seat type; client totals are ignored |
| Price history | `reservation_seats.unit_price` snapshots the price at booking time |
| Duplicate submit | A unique idempotency key returns the original reservation instead of creating another |
| Cancellation | Releases seat holds and keeps the reservation, seats, and prices for history |
| Screening cancellation | Cancels affected confirmed reservations and releases their holds |

The seat map, checkout preview, and booking transaction all validate auditorium ownership, seat activity, availability, screening status, movie publication status, and the booking cutoff. The final transaction rechecks everything because the browser state may be stale.

---

## Architecture

### Request flow

1. Blade routes in `routes/web.php` handle navigation and all writes.
2. Form Requests validate input and keep authorization/validation out of controllers.
3. Controllers load only the data required by each view.
4. `App\Services\ReservationService` performs booking, customer cancellation, and screening cancellation inside database transactions.
5. Blade components and layouts render the public, account, and admin surfaces.

The only JSON endpoint is:

```text
GET /screenings/{screening}/seats.json
```

It returns current seat availability for the live seat map. It is read-only, same-origin, session-aware, and marked `Cache-Control: no-store, private`. It is not a public API and is not versioned.

### Domain model

| Table | Responsibility |
| --- | --- |
| `users` | Customers and administrators (`role` enum) |
| `genres`, `movies`, `genre_movie` | Film catalogue and many-to-many genres |
| `auditoriums`, `seats` | Venue layout, rows, seat numbers, and seat types |
| `screenings` | Scheduled showings with start/end, base price, and status |
| `reservations` | Booking header, reference, status, totals, and idempotency key |
| `reservation_seats` | Seat-to-reservation mapping with historical unit price |
| `seat_holds` | Active seat inventory with a unique screening/seat constraint |

Movies, genres, and auditoriums with history are archived or guarded rather than hard-deleted. Reservations are never deleted.

### Key implementation choices

- **Blade-first, not an SPA.** Server-rendered pages and HTML forms satisfy the project constraints and keep the stack simple. JavaScript enhances the seat map and dialogs; it is not required for navigation.
- **Inventory in a dedicated table.** A unique `seat_holds` constraint works consistently on SQLite, MySQL, and PostgreSQL. SQLite does not support the partial unique index that an availability flag would require.
- **Server-authoritative pricing and status.** Hidden fields carry seat IDs and an idempotency key only. Price, user, reservation status, and reference are generated by the server.
- **Archive over delete.** Historical reservations remain valid even after a film or auditorium is retired.
- **Local artwork.** Posters and backdrops are local SVGs, so the demo has no external image dependency.

---

## Security

- Session authentication uses Laravel's built-in guard with session regeneration on login and invalidation on logout.
- Passwords are hashed with the configured bcrypt driver.
- Registration always creates a `customer`; submitted roles are ignored.
- CSRF protection is enabled on all state-changing forms.
- Login, registration, checkout, and cancellation routes are rate limited.
- Admin routes require both `auth` and the `admin` middleware.
- `ReservationPolicy` enforces ownership, with an explicit admin override.
- Every reservation read and cancellation is authorized against the authenticated user.
- Mass assignment is restricted to validated fields.
- Blade escapes output by default; raw HTML is limited to trusted, locally controlled content.
- Security headers are applied by middleware (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, and related headers).
- `.env`, `vendor`, `node_modules`, and `public/build` are ignored by Git. No secrets are committed.

A Content Security Policy is **not** configured. Add one at the web-server or middleware layer before a public deployment, together with HTTPS, trusted proxy configuration, and a production database. HSTS is sent automatically on secure requests by `AddSecurityHeaders`.

---

## Testing and quality checks

Run the test suite:

```bash
php artisan test
```

or:

```bash
composer test
```

Run the formatter check:

```bash
vendor/bin/pint --test
```

Apply formatting:

```bash
vendor/bin/pint
```

Build production assets:

```bash
npm run build
```

Clear or warm caches:

```bash
php artisan optimize:clear
php artisan optimize
```

The Pest suite covers public visibility and filters, authentication and rate limiting, booking validation and pricing, double-booking conflicts, idempotency, cancellation policy, reservation ownership, admin authorization, admin CRUD and guards, seeded demo pages, and domain helpers. Tests run against in-memory SQLite via `phpunit.xml`.

**Verified at the time of writing:** 75 tests passing with 394 assertions, Pint passing, and a successful Vite production build. See the final completion report for the exact commands and results of the last run.

### Manual checks still recommended

- Keyboard walkthrough of the seat map, dialogs, and forms.
- Visual review at 320 px, 768 px, and 1280 px widths.
- Screen-reader spot check of the seat map legend and live summary.
- Browser console/network inspection during checkout.
- A real multi-process load test against MySQL/PostgreSQL before production use.

---

## Route overview

### Public

| Method | URI | Purpose |
| --- | --- | --- |
| GET | `/` | Home and programme |
| GET | `/movies` | Catalogue with search/filter/sort |
| GET | `/movies/{movie}` | Film detail and showtimes |
| GET | `/screenings/{screening}` | Seat map |
| GET | `/screenings/{screening}/seats.json` | Live availability |
| GET | `/login`, `/register` | Authentication pages |

### Authenticated customers

| Method | URI | Purpose |
| --- | --- | --- |
| GET | `/checkout/{screening}` | Checkout preview |
| POST | `/checkout/{screening}` | Create reservation |
| GET | `/reservations` | Booking history |
| GET | `/reservations/{reservation}` | Booking detail |
| POST | `/reservations/{reservation}/cancel` | Cancel booking |
| GET/PATCH | `/profile` | Profile settings |
| PUT | `/profile/password` | Change password |

### Administration (`/admin`, admin only)

Dashboard, movie archive and CRUD, genre CRUD, auditorium CRUD and layout generation, screening CRUD and cancellation, and reservation inspection.

---

## Limitations

- **Demo checkout only.** There is no payment gateway, card processing, refund, invoice, or settlement flow.
- **No email delivery.** Registration does not require email verification, and password reset by email is not implemented. Signed-in users can change their password from the profile page.
- **No public API.** The specification's API surface is intentionally replaced by Blade routes; only the read-only seat availability endpoint returns JSON.
- **No true concurrency proof.** Booking uses a transaction, row locks, and a unique hold constraint, and conflict tests are sequential. A multi-process load test was not performed. SQLite is for local use; use MySQL/PostgreSQL in production.
- **No browser automation.** Responsive layout, keyboard navigation, and screen-reader behaviour were reviewed manually rather than through Playwright/Cypress.
- **No CSP.** Security headers and conditional HSTS are present, but a Content Security Policy remains a deployment hardening task.
- **Single cinema, English UI, IRR currency.** These are configuration defaults, not a multi-tenant or multi-currency implementation.
- **No real artwork or film data.** Films, posters, and copy are fictional demo content.
- **Queue and mail transports are configured but unused** by the current feature set.

---

## Project documentation

- `CINEMA_PROJECT_SPEC.md` — product specification and acceptance criteria
- `CINEMA_AGENT_RULES.md` — mandatory engineering and security rules
- `CINEMA_AGENT_SKILLS.md` — recommended workflow guidance
- `PROJECT_AUDIT.md` — initial repository audit
- `IMPLEMENTATION_PLAN.md` — phased plan and final status

---

## License

The application is built on the Laravel framework, which is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT). Laravel is a trademark of Taylor Otwell. Project-specific content and artwork are demo material.
