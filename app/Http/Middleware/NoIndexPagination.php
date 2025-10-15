<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoIndexPagination
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $isMovies = $request->routeIs('movies');
        $page     = (int) $request->query('page', 1);
        $hasSearch= trim((string) $request->query('search', '')) !== '';

        if ($isMovies && ($page > 1 || $hasSearch)) {
            $response->headers->set('X-Robots-Tag', 'noindex, follow');
        }

        return $response;
    }
}