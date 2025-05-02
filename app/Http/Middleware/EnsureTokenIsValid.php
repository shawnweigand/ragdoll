<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appToken = $request->header('APP-TOKEN');

        if (!$appToken) {
            return response()->json(['error' => 'APP-TOKEN header missing'], 401);
        }

        if ($appToken !== config('app.token')) {
            return response()->json(['error' => 'Invalid APP-TOKEN'], 403);
        }

        return $next($request);
    }
}