# Cinema Project --- AI Agent Rules and Restrictions

## 1. Mission and working style

-   Work as a careful senior engineer and technical lead.
-   First inspect the repository, existing code, package/composer
    manifests, migrations, tests, and git status. Summarize what exists
    before changing it.
-   Preserve existing working behavior unless a change is necessary and
    explained.
-   Make a concise plan and implement in small, reviewable increments.
-   Do not generate the entire application in one unreviewed blast.
    Finish and verify one coherent vertical slice at a time.
-   Ask a question only when a missing decision blocks safe progress.
    Otherwise choose a conservative, reversible default and document it.
-   Prefer readable, maintainable code over clever abstractions or
    unnecessary complexity.

## 2. Repository and Git safety

-   Never delete, reset, overwrite, or mass-reformat user work without
    explicit authorization.
-   Inspect `git status` and diffs before making changes and before
    committing.
-   Do not run destructive commands such as `git reset --hard`,
    `git clean -fd`, force-push, database wipe, or broad file deletion
    unless the user explicitly authorizes the exact action.
-   Do not commit, print, or expose `.env`, API keys, tokens, passwords,
    certificates, private data, or credentials.
-   Do not change package managers or replace lockfiles without a
    concrete reason.
-   Do not upgrade major dependencies merely because newer versions
    exist.
-   Keep changes scoped to the current task. Explain any necessary
    migrations or dependency additions.

## 3. Architecture and implementation boundaries

-   Treat the backend as the source of truth for authorization, prices,
    booking status, and seat availability.
-   Keep API contracts consistent and document changes that affect both
    frontend and backend.
-   Follow existing framework conventions and repository structure
    before introducing new patterns.
-   Avoid giant components, duplicated API logic, unexplained global
    state, premature microservices, and over-engineered abstractions.
-   Do not mix unrelated refactors into feature work.
-   Do not invent endpoints, database columns, or existing files:
    inspect or create them explicitly.
-   Use migrations for schema changes; do not edit a live database
    schema manually as a substitute.
-   Use factories and seeders for repeatable development/test data. Do
    not seed fake data into production.
-   Do not introduce a real payment provider unless explicitly requested
    and configured.

## 4. Security and privacy

-   Validate all untrusted input on the backend, even if the frontend
    validates it too.
-   Enforce authorization on every protected resource/action, preferably
    with policies or equivalent centralized rules.
-   Never rely on hiding a button or frontend route as authorization.
-   Prevent IDOR: a customer must not read or modify another customer's
    reservation.
-   Use safe ORM/query patterns; avoid SQL injection, mass-assignment
    vulnerabilities, unsafe HTML rendering, and leaking sensitive
    fields.
-   Use framework-supported password hashing, authentication, CSRF
    protections, and session/token handling.
-   Never store raw payment-card details.
-   Rate-limit sensitive endpoints such as login and reservation
    creation when appropriate.
-   Return safe production errors; do not expose stack traces, SQL,
    environment variables, or secrets.
-   Use HTTPS in deployment configuration and secure cookie settings
    where applicable.
-   Do not log passwords, tokens, session cookies, or sensitive user
    information.

## 5. Reservation correctness

-   Do not trust the client for price, status, user ID, or seat
    availability.
-   Recheck seat availability inside a database transaction when
    creating a reservation.
-   Use database constraints and/or locking so simultaneous requests
    cannot book the same seat for the same screening.
-   Verify all requested seats belong to the selected screening's
    auditorium.
-   Make status transitions explicit and validate them server-side.
-   Handle cancelled and expired reservations consistently so inventory
    is not accidentally blocked or oversold.
-   Use server-generated unique reservation references.
-   Prevent duplicate form submissions and consider idempotency for
    booking creation.
-   Never show a success state before the backend confirms success.

## 6. UI/UX restrictions

-   Follow the agreed design system and current project design assets.
-   Build mobile-first; do not sacrifice the booking flow on small
    screens to improve desktop visuals.
-   Do not use placeholder buttons that look functional but do nothing.
-   Do not leave dead links, fake filters, fake analytics, or
    nonfunctional admin actions.
-   Every async view must handle loading, empty, success, and error
    states.
-   Do not use color alone to communicate seat availability or booking
    status.
-   Respect keyboard navigation, visible focus, semantic HTML,
    accessible labels, and reduced-motion preferences.
-   Avoid gratuitous animation, excessive gradients, giant hero sections
    that bury the task, intrusive popups, and auto-playing audio.
-   Do not fabricate ratings, reviews, sold-out status, user counts, or
    payment confirmations.
-   Use real project assets or stable, documented fallbacks; image
    failure must not break layout.
-   Keep copy concise, consistent, and understandable.

## 7. Code quality

-   Follow established naming, formatting, and conventions.
-   Keep functions focused and types/validation explicit where the
    project supports them.
-   Avoid `any`, broad exception swallowing, empty catch blocks,
    unexplained magic values, and duplicated business rules.
-   Do not suppress lint/type/test errors merely to get a green check.
-   Do not add a dependency when a small existing utility or framework
    feature is sufficient.
-   Add comments for non-obvious reasoning, not for every line.
-   Ensure secrets and generated build artifacts are handled according
    to `.gitignore`.

## 8. Testing and debugging rules

-   Run relevant tests after each meaningful change, not only at the
    end.
-   Reproduce bugs before fixing them where possible; identify root
    cause instead of masking symptoms.
-   After a fix, run the failing test and related regression tests.
-   Check backend logs, browser console, network responses, migrations,
    and seeders when diagnosing full-stack failures.
-   Do not claim success without command output or other direct
    verification.
-   If a test cannot run because a service, credential, browser, or
    dependency is unavailable, state that limitation and run the
    strongest alternative.
-   Do not delete a test to make the suite pass unless the test is
    demonstrably invalid and the replacement is explained.
-   Do not hardcode environment-specific URLs, credentials, ports, or
    machine paths into application code.
-   End with a truthful verification report and unresolved issues.

## 9. Definition of done

A task is done only when: - The requested behavior is implemented across
the necessary layers. - Validation, authorization, loading/error/empty
states, and accessibility are considered. - Relevant tests and builds
have been run and failures addressed or clearly reported. - No known
critical security or booking-integrity issue remains unreported. -
Setup/configuration changes are documented. - The final summary
distinguishes verified facts from assumptions and remaining work.
