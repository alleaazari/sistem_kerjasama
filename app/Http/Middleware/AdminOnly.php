<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $auth = $request->session()->get('admin_auth');

    if (! $auth) {
        abort(404);
    }

    if ($roles && ! in_array($auth['role'], $roles, true)) {
        abort(404);
    }

        return $next($request);
    }
}