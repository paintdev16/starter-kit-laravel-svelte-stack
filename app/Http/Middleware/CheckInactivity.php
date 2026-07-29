<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckInactivity
{
    public function handle(Request $request, Closure $next, int $minutes = 30): Response
    {
        if (Auth::check()) {
            $sessionId = session()->getId();
            $session = DB::table('sessions')->where('id', $sessionId)->first();

            if ($session) {
                $idleMinutes = (time() - $session->last_activity) / 60;

                if ($idleMinutes >= $minutes) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->with('status', 'Sesión cerrada por inactividad.');
                }
            }
        }

        return $next($request);
    }
}
