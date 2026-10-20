# Recommended Skills for the Cinema AI Agent

Install only skills that are compatible with the agent you are using. A
skill is useful when it provides repeatable procedures, not just a broad
label. Start with a small set and add more when the agent reaches that
part of the project.

## Highest priority before starting

1.  **Repository exploration and planning**
    -   Inspect project structure, package manifests, environment
        configuration, migrations, tests, and git status.
    -   Produce a concise implementation plan and dependency map before
        editing.
    -   Useful skill/search terms: `repository analysis`,
        `codebase exploration`, `implementation planning`.
2.  **Laravel backend engineering**
    -   Laravel routing/controllers, Form Requests, Eloquent
        relationships, API Resources, migrations, factories, seeders,
        policies, transactions, queues only if needed, and feature
        tests.
    -   Useful terms: `Laravel best practices`, `Laravel API`,
        `Eloquent relationships`, `Laravel testing`.
3.  **React frontend engineering**
    -   React component design, hooks, routing, controlled forms, async
        states, error boundaries where appropriate, and maintainable
        component composition.
    -   Useful terms: `React best practices`,
        `React component architecture`, `Vite`.
4.  **Tailwind CSS v4 and responsive UI**
    -   Correct v4 configuration and utilities, design tokens,
        responsive layouts, accessible states, and avoiding inconsistent
        one-off styling.
    -   Useful terms: `Tailwind CSS v4`, `responsive design`,
        `design systems`.
5.  **API integration and TanStack Query**
    -   Query keys, caching, invalidation, mutations, retries,
        optimistic updates only when safe, request cancellation, and
        normalized API errors.
    -   Useful terms: `TanStack Query`, `REST API integration`,
        `API error handling`.
6.  **Database design and transactions**
    -   Relational modeling, foreign keys, unique constraints, indexes,
        migrations, transaction isolation, row locking, and
        race-condition prevention.
    -   Especially important for preventing two customers from booking
        the same seat.
    -   Useful terms: `relational database design`, `SQL transactions`,
        `database concurrency`, `booking systems`.
7.  **Testing and debugging**
    -   Laravel PHPUnit/Pest tests, frontend unit/component tests,
        browser devtools, network debugging, production builds,
        regression tests, and Playwright end-to-end testing if
        available.
    -   Useful terms: `test-driven development`, `Laravel testing`,
        `Playwright`, `debugging`.
8.  **Web application security**
    -   Authentication/session security, CSRF/CORS,
        authorization/policies, IDOR, input validation, mass assignment,
        rate limiting, secrets management, and safe error handling.
    -   Useful terms: `OWASP Top 10`, `web application security review`,
        `Laravel security`.

## Add when relevant

9.  **Accessibility (WCAG)**
    -   Keyboard navigation, contrast, labels, focus management,
        semantic markup, screen-reader feedback, and reduced motion.
    -   Useful terms: `WCAG 2.2 AA`, `accessible React components`.
10. **UI/UX design and design-system implementation**
    -   User journeys, information hierarchy, mobile-first layouts,
        reusable components, empty/error states, and visual consistency.
    -   Useful terms: `product UX`, `cinema booking UX`,
        `design system`.
11. **Authentication with Laravel Sanctum**
    -   Use only if Sanctum is selected or already present. Understand
        SPA cookie authentication, CSRF initialization, CORS, and
        credentials.
    -   Useful terms: `Laravel Sanctum SPA authentication`.
12. **Git workflow and code review**
    -   Small diffs, branch hygiene, reviewing changed files, regression
        risk, and safe commits.
    -   Useful terms: `Git workflow`, `code review`, `safe refactoring`.
13. **Deployment and environment configuration**
    -   Production build, environment variables, database migration
        strategy, logging, HTTPS, health checks, and rollback planning.
    -   Useful terms: `Laravel deployment`, `frontend deployment`,
        `production readiness`.
14. **Motion and animation**
    -   Add only for meaningful transitions; respect reduced-motion
        preferences and avoid distracting the booking flow.
    -   Useful terms: `Framer Motion accessibility`, `reduced motion`.
15. **Admin analytics / Recharts**
    -   Add only after the core booking flow is reliable and if the
        admin dashboard actually needs charts.
    -   Useful terms: `Recharts`, `dashboard data visualization`.

## Suggested order

**Before the first edit:** repository exploration,
architecture/planning, Laravel backend, React frontend, database design,
security, and testing.

**During UI implementation:** Tailwind v4, TanStack Query, UX/design
system, accessibility.

**Before completion:** end-to-end testing, code review, deployment, and
security review.

## Skill selection rules

-   Prefer official framework documentation or well-maintained, narrowly
    scoped skills.
-   Check a skill's contents, source, and supported framework versions
    before installing or trusting it.
-   Do not install several overlapping skills that instruct the agent to
    use conflicting architectures.
-   A skill is guidance, not proof: the agent must still inspect the
    actual repository and run the project's checks.
-   Avoid skills that request secrets, disable security checks, run
    opaque scripts, or automatically execute destructive commands.
-   Do not give the agent every skill at once if the tool has a context
    limit. Add the relevant skill before the corresponding
    implementation phase.
