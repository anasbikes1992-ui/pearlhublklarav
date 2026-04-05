<?php

namespace App\Http\Middleware;

use App\Models\Listing;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnsListingOrAdmin
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeParam = $request->route('listing');
        if ($routeParam === null || $routeParam === '') {
            return $next($request);
        }

        $listing = $routeParam instanceof Listing
            ? $routeParam
            : Listing::query()->find((string) $routeParam);

        if (! $listing) {
            abort(404, 'Listing not found.');
        }

        $user = $request->user();
        if (! $user || ($listing->provider_id !== $user->id && $user->role !== 'admin')) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
