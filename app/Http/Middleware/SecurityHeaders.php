<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menambahkan header keamanan dasar pada setiap respons web.
 *
 * CSP masih bersifat Report-Only (tidak memblokir) karena UI memakai banyak
 * <script>/<style> inline. Penegakan (enforce) dilakukan di fase hardening.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Content-Security-Policy-Report-Only',
            $this->contentSecurityPolicy(),
        );

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $directives = [
            "default-src 'self'",
            // 'unsafe-inline'/'unsafe-eval' masih perlu selama JS & CSS inline ada.
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https://images.unsplash.com",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        return implode('; ', $directives);
    }
}
