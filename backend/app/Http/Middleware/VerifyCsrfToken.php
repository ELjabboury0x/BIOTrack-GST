<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Str;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        //
    ];

    protected function shouldPassThrough($request)
    {
        if (
            app()->environment('local')
            && $request->isMethod('POST')
            && $request->is('login')
            && Str::endsWith((string) $request->getHost(), '.loca.lt')
        ) {
            return true;
        }

        return parent::shouldPassThrough($request);
    }
}
