<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

         // Content Security Policy: keep development tolerant, enforce stricter production scripts.
         $scriptSource = config('app.env') === 'production'
             ? "script-src 'self' https://cdn.jsdelivr.net; "
             : "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; ";

         $csp = "default-src 'self'; " .
             $scriptSource .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https: blob:; " .
               "connect-src 'self' https://api.pearlhub.lk; " .
               "frame-ancestors 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        // Strict Transport Security (HTTPS only)
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
