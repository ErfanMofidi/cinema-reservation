<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocaleController extends Controller
{
    /** @var list<string> */
    private array $availableLocales = ['fa', 'en'];

    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, $this->availableLocales, true)) {
            abort(Response::HTTP_NOT_FOUND, 'Unsupported language.');
        }

        $request->session()->put('locale', $locale);

        $referer = $request->headers->get('referer');
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? null;

        if ($referer !== false && $referer !== '') {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            if ($refererHost !== false && $refererHost === $appHost) {
                return redirect($referer);
            }
        }

        return redirect()->route('home');
    }
}
