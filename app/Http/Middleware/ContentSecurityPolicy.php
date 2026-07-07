<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only add headers to standard HTTP responses, not BinaryFileResponses etc.
        if (method_exists($response, 'header')) {
            // Allow Vite in local development
            $viteUrl = '';
            if (app()->environment('local')) {
                $viteUrl = ' http://localhost:5173 ws://localhost:5173';
            }

            $csp = "default-src 'self';" .
                   "script-src 'self' 'unsafe-inline' 'unsafe-eval'{$viteUrl};" .
                   "style-src 'self' 'unsafe-inline'{$viteUrl};" .
                   "img-src 'self' data: blob:;" .
                   "font-src 'self' data:;" .
                   "connect-src 'self'{$viteUrl};" .
                   "object-src 'none';" .
                   "base-uri 'self';";

            $response->header('Content-Security-Policy', $csp);
        }

        return $response;
    }
}
