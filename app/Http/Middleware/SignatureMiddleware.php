<?php

namespace App\Http\Middleware;

use Closure;

class SignatureMiddleware
{
    public function handle($request, Closure $next, $headerName = 'X-Name') {

        $response = $next($request);

        $response->headers->set($headerName, config('app.name'));

        return $response;

    }
}
