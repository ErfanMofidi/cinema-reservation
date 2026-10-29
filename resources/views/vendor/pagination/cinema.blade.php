@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-line-soft pt-6 sm:flex-row">
        <p class="text-xs text-muted">
            {{ __('messages.pagination.showing') }}
            <span class="font-medium text-ink">{{ $paginator->firstItem() }}</span>
            {{ __('messages.pagination.to') }}
            <span class="font-medium text-ink">{{ $paginator->lastItem() }}</span>
            {{ __('messages.pagination.of') }}
            <span class="font-medium text-ink">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="flex h-9 items-center rounded-full border border-line px-3 text-xs text-muted/40" aria-disabled="true">{{ __('messages.pagination.previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex h-9 items-center rounded-full border border-line px-3 text-xs text-muted transition hover:border-gold/50 hover:text-gold">{{ __('messages.pagination.previous') }}</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1 text-xs text-muted/50">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="flex h-9 min-w-9 items-center justify-center rounded-full bg-gold px-2 text-xs font-semibold text-night">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="flex h-9 min-w-9 items-center justify-center rounded-full border border-line px-2 text-xs text-muted transition hover:border-gold/50 hover:text-gold" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex h-9 items-center rounded-full border border-line px-3 text-xs text-muted transition hover:border-gold/50 hover:text-gold">{{ __('messages.pagination.next') }}</a>
            @else
                <span class="flex h-9 items-center rounded-full border border-line px-3 text-xs text-muted/40" aria-disabled="true">{{ __('messages.pagination.next') }}</span>
            @endif
        </div>
    </nav>
@endif
