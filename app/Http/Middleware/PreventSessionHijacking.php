<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class PreventSessionHijacking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $ip = $request->ip();
            $userAgent = $request->userAgent();

            if (!Session::has('client_ip')) {
                Session::put('client_ip', $ip);
            }

            if (!Session::has('client_user_agent')) {
                Session::put('client_user_agent', $userAgent);
            }

            if (Session::get('client_ip') !== $ip || Session::get('client_user_agent') !== $userAgent) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'form.email' => __('Your session was terminated because your IP address or User-Agent changed.'),
                ]);
            }
        }

        return $next($request);
    }
}
