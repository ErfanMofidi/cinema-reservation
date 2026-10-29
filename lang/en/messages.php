<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public navigation
    |--------------------------------------------------------------------------
    */

    'nav' => [
        'all_films' => 'All films',
        'now_showing' => 'Now showing',
        'coming_soon' => 'Coming soon',
        'my_tickets' => 'My tickets',
        'admin' => 'Admin',
        'profile' => 'Profile',
        'my_reservations' => 'My reservations',
        'sign_in' => 'Sign in',
        'join' => 'Join the list',
        'sign_out' => 'Sign out',
        'back_to_cinema' => 'Back to the cinema',
        'browse_films' => 'Browse films',
        'create_account' => 'Create an account',
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale switcher
    |--------------------------------------------------------------------------
    */

    'locale' => [
        'switcher' => 'Language',
        'english' => 'English',
        'persian' => 'Persian',
    ],

    /*
    |--------------------------------------------------------------------------
    | Home page
    |--------------------------------------------------------------------------
    */

    'home' => 'Home',
    'home.badge.now_showing' => 'Now showing',
    'home.featured.choose_showtime' => 'Choose a showtime',
    'home.featured.browse_all_films' => 'Browse all films',
    'home.welcome.welcome' => 'Welcome',
    'home.welcome.browse_films' => 'Browse films',
    'home.tonight.title' => 'Tonight',
    'home.tonight.still_time' => 'Still time to catch a film',
    'home.tonight.seats_left' => 'seats left',
    'home.now_showing.on_screen' => 'On screen',
    'home.now_showing.all_showtimes' => 'All showtimes',
    'home.coming_soon.on_horizon' => 'On the horizon',
    'home.coming_soon.see_what_ahead' => "See what's ahead",
    'home.coming_soon.upcoming_note' => 'Upcoming films open ticket sales closer to their release date.',
    'home.plan_visit.title' => 'Plan your visit',
    'home.plan_visit.subtitle' => 'A cinema built around the film',
    'home.plan_visit.two_auditoriums' => 'Two auditoriums',
    'home.plan_visit.seven_days' => 'Seven days a week',
    'home.plan_visit.demo_checkout' => 'Demo checkout',

    /*
    |--------------------------------------------------------------------------
    | Programme page
    |--------------------------------------------------------------------------
    */

    'programme.the_programme' => 'The programme',
    'programme.films_at' => 'Films at :name',
    'programme.find_whats_playing' => "Find what's playing, filter by genre, or look ahead to the next release. Every screening has reserved seating.",
    'programme.search' => 'Search',
    'programme.genre' => 'Genre',
    'programme.availability' => 'Availability',
    'programme.sort_by' => 'Sort by',
    'programme.apply' => 'Apply',
    'programme.clear' => 'Clear',
    'programme.all_genres' => 'All genres',
    'programme.all_films' => 'All films',
    'programme.now_showing' => 'Now showing',
    'programme.coming_soon' => 'Coming soon',
    'programme.newest_release' => 'Newest release',
    'programme.title_a_z' => 'Title A–Z',
    'programme.shortest_runtime' => 'Shortest runtime',
    'programme.films_total' => ':count :plural',
    'programme.for_query' => 'for “:query”',
    'programme.no_films_match' => 'No films match those filters',
    'programme.try_different' => 'Try a different title or genre, or clear the filters to see the full programme.',
    'programme.clear_filters' => 'Clear filters',

    /*
    |--------------------------------------------------------------------------
    | Film page
    |--------------------------------------------------------------------------
    */

    'film.films' => 'Films',
    'film.story' => 'The story',
    'film.synopsis' => 'Synopsis',
    'film.reserve_a_seat' => 'Reserve a seat',
    'film.showtimes' => 'Showtimes',
    'film.all_times_local' => 'All times are local · Booking closes 15 minutes before showtime',
    'film.tickets_not_on_sale' => 'Tickets are not on sale yet',
    'film.opens_on' => 'This film opens on :date. Check back closer to the release date.',
    'film.no_upcoming_screenings' => 'There are no upcoming screenings for this film right now. Browse the programme for something else.',
    'film.see_whats_showing' => "See what's showing",
    'film.today' => 'Today',
    'film.tomorrow' => 'Tomorrow',
    'film.screening_count' => ':count :plural',
    'film.sold_out' => 'Sold out',
    'film.booking_closed' => 'Booking closed',
    'film.seats_left' => 'seats left',
    'film.from' => 'from',
    'film.details' => 'Details',
    'film.runtime' => 'Runtime',
    'film.language' => 'Language',
    'film.age_rating' => 'Age rating',
    'film.release_date' => 'Release date',
    'film.released_on' => 'Released :date',
    'film.genres' => 'Genres',
    'film.back_to_films' => 'Back to films',
    'film.demo_checkout' => 'Demo checkout',
    'film.no_card_details' => 'No card details are collected and no payment is charged. Your reservation is confirmed immediately for this portfolio demo.',

    /*
    |--------------------------------------------------------------------------
    | Screening / seat map
    |--------------------------------------------------------------------------
    */

    'screening.choose_seats' => 'Choose your seats',
    'screening.seats_available' => ':count :plural available',
    'screening.seats_available_lower' => 'available',
    'screening.refresh' => 'Refresh',
    'screening.screen' => 'Screen',
    'screening.select_seats' => 'Select seats. Unavailable seats are disabled.',
    'screening.availability_refreshes' => 'Availability refreshes automatically every 30 seconds.',
    'screening.available' => 'Available',
    'screening.selected' => 'Selected',
    'screening.premium' => 'Premium',
    'screening.accessible' => 'Accessible',
    'screening.unavailable' => 'Unavailable',
    'screening.other_times' => 'Other times for this film',
    'screening.no_later_screenings' => 'There are no later screenings scheduled right now.',
    'screening.no_seats_selected' => 'No seats selected yet',
    'screening.tickets' => 'Tickets',
    'screening.seats' => 'Seats',
    'screening.service_fee' => 'Service fee',
    'screening.total' => 'Total',
    'screening.continue_to_checkout' => 'Continue to checkout',
    'screening.seats_not_held' => 'Your seats are not held until the next step is confirmed.',
    'screening.youll_be_asked' => "You'll be asked to sign in. Your seat selection is kept for the next step.",
    'screening.sold_out' => 'This screening is sold out.',
    'screening.booking_closed' => 'Online booking is closed.',
    'screening.demo_checkout' => 'Demo checkout. No card details are collected and no payment is charged.',
    'screening.online_booking_closed' => 'Online booking is closed for this screening.',
    'screening.tickets_close' => 'Tickets close 15 minutes before showtime. You can still look at the auditorium, or choose a later screening below.',
    'screening.seats_reserved_after_checkout' => 'Seats are reserved only after checkout.',
    'screening.choose_up_to' => 'Choose up to :max seats. Prices update live, and availability is rechecked before your reservation is confirmed.',

    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    'checkout.films' => 'Films',
    'checkout.checkout' => 'Checkout',
    'checkout.almost_there' => 'Almost there',
    'checkout.review_your_reservation' => 'Review your reservation',
    'checkout.check_screening' => 'Check the screening and seats below. Your total is calculated on the server and seats are rechecked when you confirm.',
    'checkout.seats' => 'Seats',
    'checkout.your_screening' => 'Your screening',
    'checkout.date' => 'Date',
    'checkout.time' => 'Time',
    'checkout.auditorium' => 'Auditorium',
    'checkout.age_rating' => 'Age rating',
    'checkout.booking_details' => 'Booking details',
    'checkout.ticket_holder' => 'Ticket holder',
    'checkout.delivery' => 'Delivery',
    'checkout.digital_ticket' => 'Digital ticket in your account',
    'checkout.reference_appears' => 'Your reference appears immediately after confirmation.',
    'checkout.demo_checkout_payment' => 'Demo checkout — no payment is collected',
    'checkout.no_card_details' => 'This portfolio project does not ask for card details, does not contact a payment provider, and does not charge any money. Confirming creates a confirmed demo reservation immediately.',
    'checkout.before_you_confirm' => 'Before you confirm',
    'checkout.seats_secured' => 'Your seats are secured only after this confirmation succeeds. If another customer takes one first, you will be returned to the seat map with a clear message.',
    'checkout.cancel_online' => 'You can cancel online until :minutes minutes before showtime. Cancelling releases your seats.',
    'checkout.arrive_early' => 'Please arrive at least 15 minutes before the screening. Online booking closes 15 minutes before start time.',
    'checkout.row' => 'Row',
    'checkout.order_summary' => 'Order summary',
    'checkout.subtotal' => 'Subtotal',
    'checkout.fees' => 'Fees',
    'checkout.no_fee' => 'No fee',
    'checkout.total' => 'Total',
    'checkout.confirm_reservation' => 'Confirm reservation',
    'checkout.change_seats' => 'Change seats',
    'checkout.no_card_charge' => 'No card details. No charge. Demo reservation only.',

    /*
    |--------------------------------------------------------------------------
    | Reservations
    |--------------------------------------------------------------------------
    */

    'reservations.your_tickets' => 'Your tickets',
    'reservations.my_reservations' => 'My reservations',
    'reservations.upcoming_screenings' => 'Upcoming screenings, past visits, and cancelled bookings all live here. Open a ticket for seats and booking details.',
    'reservations.find_a_film' => 'Find a film',
    'reservations.no_reservations_yet' => 'No reservations yet',
    'reservations.choose_film' => 'Choose a film, pick your seats, and your first ticket will appear here.',
    'reservations.browse_programme' => 'Browse the programme',
    'reservations.upcoming' => 'Upcoming',
    'reservations.past_cancelled' => 'Past &amp; cancelled',
    'reservations.on_this_page' => ':count on this page',
    'reservations.no_upcoming_screenings' => 'No upcoming screenings on this page. Explore what\'s showing now.',
    'reservations.whats_showing_now' => "what's showing now",
    'reservations.view' => 'View',
    'reservations.cancel' => 'Cancel',
    'reservations.cancel_this_reservation' => 'Cancel this reservation?',
    'reservations.seats_released' => 'Seats :seats will be released immediately. This cannot be undone.',
    'reservations.total' => 'Total',
    'reservations.my_reservations_title' => 'My reservations',
    'reservations.viewing_as_admin' => 'You are viewing this reservation as an administrator. The ticket holder is :name (:email).',
    'reservations.reference' => 'REF',
    'reservations.screening_cancelled' => 'This screening was cancelled by the cinema.',
    'reservations.kept_for_records' => 'Your reservation is kept for your records. No payment was collected in this demo.',
    'reservations.admit' => 'Admit :count',
    'reservations.payment_summary' => 'Payment summary',
    'reservations.tickets' => 'Tickets',
    'reservations.fees' => 'Fees',
    'reservations.demo_checkout_records' => 'Demo checkout — no payment was collected. Prices are stored with this reservation.',
    'reservations.need_to_change_plans' => 'Need to change plans?',
    'reservations.cancel_online_until' => 'You can cancel online until :deadline. Cancelling releases your seats immediately.',
    'reservations.seats_released_immediately' => 'Cancelling releases your seats immediately.',
    'reservations.contact_cinema' => 'Please contact the cinema if you need help.',
    'reservations.cancel_reservation' => 'Cancel reservation',
    'reservations.screening_not_scheduled' => 'Online cancellation is not available because this screening is no longer scheduled.',
    'reservations.started_finished' => 'This screening has already started or finished, so online cancellation is closed.',
    'reservations.closed_at' => 'Online cancellation closed at :deadline. Please contact the cinema if you need help.',
    'reservations.your_visit' => 'Your visit',
    'reservations.arrive_early' => 'Arrive at least 15 minutes early. Booking closes 15 minutes before showtime.',
    'reservations.show_reference' => 'Show this reference at the door: :reference.',
    'reservations.all_reservations' => 'All reservations',
    'reservations.cancelled' => 'Cancelled :date',
    'reservations.this_reservation_was_cancelled' => 'This reservation was cancelled.',

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    'profile.your_profile' => 'Your profile',
    'profile.account' => 'Account',
    'profile.keep_contact' => 'Keep your contact details current and update the password used to protect your reservations.',
    'profile.my_reservations' => 'My reservations',
    'profile.profile_details' => 'Profile details',
    'profile.used_on_booking' => 'Used on your booking confirmations and account menu.',
    'profile.password' => 'Password',
    'profile.use_at_least' => 'Use at least 8 characters with a letter and a number.',
    'profile.save_details' => 'Save details',
    'profile.update_password' => 'Update password',
    'profile.current_password' => 'Current password',
    'profile.new_password' => 'New password',
    'profile.confirm_new_password' => 'Confirm new password',
    'profile.signed_in_as' => 'Signed in as',
    'profile.need_to_leave' => 'Need to leave?',
    'profile.sign_out' => 'Sign out',

    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    */

    'component.review_fields' => 'Please review the highlighted fields.',
    'component.dismiss_message' => 'Dismiss message',

    /*
    |--------------------------------------------------------------------------
    | Confirm dialog
    |--------------------------------------------------------------------------
    */

    'confirm.title' => 'Please confirm',
    'confirm.message' => 'This action cannot be undone.',
    'confirm.keep' => 'Keep it',
    'confirm.confirm' => 'Confirm',

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'pagination.showing' => 'Showing',
    'pagination.to' => 'to',
    'pagination.of' => 'of',
    'pagination.previous' => 'Previous',
    'pagination.next' => 'Next',

    /*
    |--------------------------------------------------------------------------
    | Footer
    |--------------------------------------------------------------------------
    */

    'footer.discover' => 'Discover',
    'footer.your_visit' => 'Your visit',
    'footer.demo_checkout' => 'Demo checkout — no payment is collected.',
    'footer.portfolio' => 'A portfolio project.',
    'footer.single_screen' => 'A single-screen neighbourhood cinema experience.',

    /*
    |--------------------------------------------------------------------------
    | Errors
    |--------------------------------------------------------------------------
    */

    'errors.page_not_found' => 'Page not found',
    'errors.nothing_in_this_frame' => 'Nothing in this frame',
    'errors.this_page_is_missing' => 'This page is missing',
    'errors.link_out_of_date' => 'The link may be out of date, or the film, screening, or reservation may no longer exist. The programme is still playing.',
    'errors.access_denied' => 'Access denied',
    'errors.members_only' => 'Members only',
    'errors.door_locked' => 'That door is locked',
    'errors.no_permission' => 'Your account does not have permission to open this page. If you believe this is a mistake, sign in with an administrator account.',
    'errors.sign_in_different' => 'Sign in with a different account',
    'errors.page_expired' => 'Page expired',
    'errors.session_timed_out' => 'The session timed out',
    'errors.page_expired_heading' => 'This page expired',
    'errors.forms_expire' => 'For your security, forms expire after a while. Go back, refresh the page, and submit again.',
    'errors.return_to_sign_in' => 'Return to sign in',
    'errors.too_many_requests' => 'Too many requests',
    'errors.one_moment' => 'One moment, please',
    'errors.moving_quickly' => 'You are moving quickly',
    'errors.too_many_attempts' => 'Too many attempts were made in a short time. Wait a minute, then refresh the page and try again.',
    'errors.server_error' => 'Server error',
    'errors.backstage_interruption' => 'Backstage interruption',
    'errors.slipped_reel' => 'Something slipped a reel',
    'errors.unexpected_error' => 'An unexpected error stopped this page. Nothing was charged, and no payment details are ever collected here. Please try again in a moment.',
    'errors.temporarily_unavailable' => 'Temporarily unavailable',
    'errors.short_intermission' => 'A short intermission',
    'errors.right_back' => 'We are right back',
    'errors.maintenance' => 'The cinema is briefly unavailable for maintenance. Please check back in a few minutes.',

];
