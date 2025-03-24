<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAge
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip age verification for authenticated users
        if (auth()->check()) {
            return $next($request);
        }

        // Check if age verification cookie exists
        if (!$request->cookie('age_verified')) {
            // Store the intended URL to redirect back after verification
            session()->put('intended_url', $request->url());
            
            // Redirect to age verification page
            return redirect()->route('verify.age');
        }

        return $next($request);
    }
}