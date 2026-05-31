<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language', 'en');
        $supported = ['en', 'ru', 'tm'];

        app()->setLocale(in_array($locale, $supported) ? $locale : 'en');

        return $next($request);
    }
}
