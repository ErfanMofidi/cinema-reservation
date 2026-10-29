<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    private array $availableLocales = ['fa', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        if (! in_array($locale, $this->availableLocales, true)) {
            $locale = 'fa';
        }

        App::setLocale($locale);
        config(['app.locale' => $locale]);
        $request->session()->put('locale', $locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        if ($request->session()->has('locale')) {
            return (string) $request->session()->get('locale');
        }

        if ($request->hasHeader('Accept-Language')) {
            $preferred = $this->preferredFromAcceptLanguage(
                $request->header('Accept-Language'),
                $this->availableLocales,
            );

            if ($preferred !== null) {
                return $preferred;
            }
        }

        return 'fa';
    }

    /**
     * Pick the first accepted locale that exists on the explicit allowlist.
     *
     * @param  list<string>  $availableLocales
     */
    private function preferredFromAcceptLanguage(string $acceptLanguage, array $availableLocales): ?string
    {
        $candidates = [];

        foreach (explode(',', $acceptLanguage) as $item) {
            $item = trim((string) $item);
            if ($item === '') {
                continue;
            }

            [$value] = explode(';', $item, 2);
            $base = str_replace('_', '-', strtolower(explode('-', trim($value))[0]));

            if (! in_array($base, $availableLocales, true)) {
                continue;
            }

            $quality = 1.0;
            if (isset($item[1]) && str_contains($item, ';')) {
                preg_match('/q=([0-9.]+)/', $item, $matches);
                $quality = (float) ($matches[1] ?? 1.0);
            }

            $candidates[] = [$base, $quality];
        }

        usort($candidates, fn (array $a, array $b): int => $b[1] <=> $a[1]);

        return $candidates[0][0] ?? null;
    }
}
