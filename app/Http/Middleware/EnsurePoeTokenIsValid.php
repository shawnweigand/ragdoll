<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnsurePoeTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $route = $request->route()->getName();

            $authHeader = $request->header('Authorization');
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                abort(401, 'Unauthorized, missing or invalid Authorization header.');
            }
            $token = substr($authHeader, 7);

            if ($token !== config('services.poe.' . $route . '.token')) {
                abort(403, 'Forbidden, invalid token.');
            }

            return $next($request);
        } catch (\Exception $e) {
            return new StreamedResponse(function () use ($e) {
                // Disable buffering
                if (ob_get_level()) ob_end_clean();
                ini_set('zlib.output_compression', 0);
                ini_set('output_buffering', 'off');
                ini_set('implicit_flush', 1);
                while (ob_get_level() > 0) {
                    ob_end_flush();
                }
                flush();

                // Send the initial event metadata
                echo "event: meta\n";
                echo "data: " . json_encode([
                    'content_type' => 'text/markdown',
                    'suggested_replies' => false,
                ]) . "\n\n";

                echo "event: text\n";
                echo "data: " . json_encode(['text' => $e->getMessage()]) . "\n\n";
                flush();

                // Add this to tell Poe the response is finished
                echo "event: done\n";
                echo "data: {}\n\n";
                flush(); // Ensure it's sent in chunks
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
            ]);
        }
    }
}
