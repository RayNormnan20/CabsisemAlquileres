<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictAccessHours
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user) {
            // Assuming optional fields `access_start_hour` and `access_end_hour` (0-23) on users
            $start = $user->access_start_hour ?? null;
            $end = $user->access_end_hour ?? null;

            if ($start !== null && $end !== null) {
                $currentHour = now()->format('H');
                $currentHour = (int) $currentHour;

                $allowed = false;
                if ($start <= $end) {
                    $allowed = $currentHour >= $start && $currentHour < $end;
                } else {
                    // Overnight window (e.g., 22 -> 6)
                    $allowed = $currentHour >= $start || $currentHour < $end;
                }

                if (!$allowed) {
                    return response()->view('errors.access_hours_blocked', [
                        'start' => $start,
                        'end' => $end,
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}