<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $timeout = config('dashboard.session_timeout', 15) * 60; // Convert to seconds

        if ($request->session()->has('last_activity')) {
            $elapsed = time() - $request->session()->get('last_activity');

            if ($elapsed > $timeout) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['timeout' => 'Your session has expired due to inactivity.']);
            }
        }

        $request->session()->put('last_activity', time());

        return $next($request);
    }
}
