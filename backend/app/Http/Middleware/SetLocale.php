<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = config('app.locale', 'fr');

        App::setLocale($locale);
        Carbon::setLocale($locale);
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

        return $next($request);
    }
}
