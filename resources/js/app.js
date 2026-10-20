const onReady = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
};

onReady(() => {
    initMobileMenu();
    initAlerts();
    initPasswordToggles();
    initConfirmDialog();
    initSubmitLock();
    initSeatMap();
    initScreeningForm();
});

function initMobileMenu() {
    const toggle = document.querySelector('[data-mobile-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');

    if (!toggle || !menu) {
        return;
    }

    const close = () => {
        menu.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.querySelector('[data-menu-open-icon]')?.classList.remove('hidden');
        toggle.querySelector('[data-menu-close-icon]')?.classList.add('hidden');
    };

    toggle.addEventListener('click', () => {
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        menu.classList.toggle('hidden', expanded);
        toggle.setAttribute('aria-expanded', String(!expanded));
        toggle.querySelector('[data-menu-open-icon]')?.classList.toggle('hidden', !expanded);
        toggle.querySelector('[data-menu-close-icon]')?.classList.toggle('hidden', expanded);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            close();
            toggle.focus();
        }
    });
}

function initAlerts() {
    document.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('div[role="alert"], div[role="status"]')?.remove();
        });
    });
}

function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const wrapper = button.parentElement;
            const input = wrapper?.querySelector('[data-password-input]');

            if (!input) {
                return;
            }

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(show));
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            button.querySelector('[data-password-icon-show]')?.classList.toggle('hidden', show);
            button.querySelector('[data-password-icon-hide]')?.classList.toggle('hidden', !show);
        });
    });
}

function initConfirmDialog() {
    const dialog = document.getElementById('confirm-dialog');

    if (!dialog) {
        return;
    }

    const title = dialog.querySelector('[data-confirm-title]');
    const message = dialog.querySelector('[data-confirm-message]');
    let pendingForm = null;

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();
            pendingForm = form;

            if (title) {
                title.textContent = form.dataset.confirmTitle || 'Please confirm';
            }

            if (message) {
                message.textContent = form.dataset.confirmMessage || 'This action cannot be undone.';
            }

            if (typeof dialog.showModal === 'function') {
                dialog.showModal();
            } else if (window.confirm(message?.textContent || 'Please confirm this action.')) {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            }
        });
    });

    dialog.addEventListener('close', () => {
        if (!pendingForm) {
            return;
        }

        const form = pendingForm;
        pendingForm = null;

        if (dialog.returnValue === 'confirm') {
            form.dataset.confirmed = 'true';
            form.requestSubmit();
        }
    });
}

function initSubmitLock() {
    document.querySelectorAll('form').forEach((form) => {
        // Confirmation forms are re-submitted after the dialog closes, so the
        // first (prevented) submit must not lock them permanently.
        if (form.matches('[data-no-submit-lock], [data-confirm]')) {
            return;
        }

        form.addEventListener('submit', (event) => {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';

            form.querySelectorAll('button[type="submit"]').forEach((button) => {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');

                if (!button.dataset.idleLabel) {
                    button.dataset.idleLabel = button.textContent.trim();
                }

                button.textContent = button.dataset.pendingLabel || 'Working…';
            });
        });
    });
}

function initSeatMap() {
    const root = document.querySelector('[data-seat-map]');

    if (!root) {
        return;
    }

    const inputs = Array.from(root.querySelectorAll('[data-seat-input]'));
    const summary = document.querySelector('[data-seat-summary]');
    const countNode = summary?.querySelector('[data-seat-count]');
    const listNode = summary?.querySelector('[data-seat-list]');
    const totalNode = summary?.querySelector('[data-seat-total]');
    const subtotalNode = summary?.querySelector('[data-seat-subtotal]');
    const messageNode = summary?.querySelector('[data-seat-message]');
    const continueLink = summary?.querySelector('[data-continue]');
    const maxSeats = Number(root.dataset.maxSeats || 8);
    const currency = root.dataset.currency || 'IRR';
    const endpoint = root.dataset.endpoint;
    const checkoutUrl = root.dataset.checkoutUrl;
    const bookable = root.dataset.bookable === '1';
    const availabilityStatus = document.querySelector('[data-availability-status]');
    const refreshButton = document.querySelector('[data-refresh-seats]');
    const formatter = new Intl.NumberFormat('en-US');

    if (!summary) {
        return;
    }

    const selected = () => inputs.filter((input) => input.checked);

    const announce = (message) => {
        if (messageNode) {
            messageNode.textContent = message;
        }
    };

    const update = () => {
        const chosen = selected();
        const total = chosen.reduce((sum, input) => sum + Number(input.dataset.price || 0), 0);
        const labels = chosen
            .map((input) => input.dataset.label)
            .sort((a, b) => a.localeCompare(b, 'en', { numeric: true }));

        if (countNode) {
            countNode.textContent = String(chosen.length);
        }

        if (listNode) {
            listNode.textContent = labels.length > 0 ? labels.join(', ') : 'No seats selected yet';
            listNode.classList.toggle('text-muted', labels.length === 0);
            listNode.classList.toggle('text-ink', labels.length > 0);
        }

        if (totalNode) {
            totalNode.textContent = `${formatter.format(total)} ${currency}`;
        }

        if (subtotalNode) {
            subtotalNode.textContent = `${formatter.format(total)} ${currency}`;
        }

        if (continueLink) {
            const hasSeats = chosen.length > 0;
            const url = new URL(checkoutUrl, window.location.origin);
            url.searchParams.set('seats', chosen.map((input) => input.value).join(','));

            continueLink.href = hasSeats ? url.toString() : checkoutUrl;
            continueLink.classList.toggle('pointer-events-none', !hasSeats);
            continueLink.classList.toggle('opacity-45', !hasSeats);
            continueLink.setAttribute('aria-disabled', String(!hasSeats));
            continueLink.tabIndex = hasSeats ? 0 : -1;
        }

        if (chosen.length < maxSeats) {
            announce('');
        }
    };

    inputs.forEach((input) => {
        input.addEventListener('change', () => {
            if (input.checked && selected().length > maxSeats) {
                input.checked = false;
                announce(`You can reserve up to ${maxSeats} seats at a time.`);
            }

            update();
        });
    });

    continueLink?.addEventListener('click', (event) => {
        if (continueLink.getAttribute('aria-disabled') === 'true') {
            event.preventDefault();
        }
    });

    if (!bookable || !endpoint) {
        update();
        return;
    }

    let refreshing = false;

    const refreshAvailability = async (manual = false) => {
        if (refreshing || document.visibilityState === 'hidden') {
            return;
        }

        refreshing = true;

        if (manual && availabilityStatus) {
            availabilityStatus.textContent = 'Checking availability…';
        }

        try {
            const response = await fetch(endpoint, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Availability request failed.');
            }

            const payload = await response.json();
            const seats = Array.isArray(payload?.data?.seats) ? payload.data.seats : [];
            const availability = new Map(seats.map((seat) => [Number(seat.id), Boolean(seat.available)]));
            const lost = [];

            inputs.forEach((input) => {
                const seatId = Number(input.value);
                const label = document.querySelector(`label[for="${input.id}"]`);
                const available = availability.get(seatId);

                if (available === false) {
                    if (input.checked) {
                        input.checked = false;
                        lost.push(input.dataset.label);
                    }

                    input.disabled = true;
                    label?.classList.add('seat-label--unavailable');
                    label?.setAttribute('title', `${input.dataset.label} — unavailable`);
                } else if (available === true && input.disabled) {
                    input.disabled = false;
                    label?.classList.remove('seat-label--unavailable');
                    label?.setAttribute('title', `${input.dataset.label} — available`);
                }
            });

            if (lost.length > 0) {
                announce(`These seats were just taken: ${lost.join(', ')}. Please choose again.`);
            }

            update();

            if (availabilityStatus) {
                availabilityStatus.textContent = manual ? 'Availability refreshed.' : 'Availability is up to date.';
            }
        } catch {
            if (availabilityStatus) {
                availabilityStatus.textContent = 'Could not refresh availability. Your seats will be rechecked at checkout.';
            }
        } finally {
            refreshing = false;
        }
    };

    refreshButton?.addEventListener('click', () => refreshAvailability(true));

    window.setInterval(() => refreshAvailability(false), 30000);

    update();
}

function initScreeningForm() {
    const form = document.querySelector('[data-screening-form]');

    if (!form) {
        return;
    }

    const movie = form.querySelector('[data-movie-select]');
    const start = form.querySelector('[data-screening-start]');
    const end = form.querySelector('[data-screening-end]');

    if (!movie || !start || !end) {
        return;
    }

    const syncEndTime = () => {
        if (!start.value || !movie.value) {
            return;
        }

        const runtime = Number(movie.selectedOptions[0]?.dataset.runtime || 0);

        if (!runtime) {
            return;
        }

        const startDate = new Date(start.value);

        if (Number.isNaN(startDate.getTime())) {
            return;
        }

        const endDate = new Date(startDate.getTime() + runtime * 60000);
        const pad = (value) => String(value).padStart(2, '0');

        end.value = [
            endDate.getFullYear(),
            '-',
            pad(endDate.getMonth() + 1),
            '-',
            pad(endDate.getDate()),
            'T',
            pad(endDate.getHours()),
            ':',
            pad(endDate.getMinutes()),
        ].join('');
    };

    movie.addEventListener('change', syncEndTime);
    start.addEventListener('change', syncEndTime);
}
