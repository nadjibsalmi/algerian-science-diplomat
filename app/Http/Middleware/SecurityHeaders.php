<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injects essential HTTP security headers on every response.
 *
 * Added per audit finding: the application shipped zero protective headers,
 * leaving it open to MIME-sniffing, clickjacking, and information leakage.
 * These headers are the minimum recommended by OWASP for a government-grade
 * platform. A full CSP policy should be added once the asset inventory is
 * stable (inline script hashes / nonces need to be coordinated with Vite).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME-type sniffing attacks.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Block the page from being embedded in an iframe on another origin
        // (clickjacking protection).
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Control how much referrer information is sent with requests.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict access to browser features not needed by this application.
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Tell browsers to only allow HTTPS for the next year (only active in
        // production — do not send HSTS on HTTP or local dev, it can lock
        // the domain out of plain-HTTP access for the duration of the
        // max-age value).
        if (config('app.env') === 'production') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
