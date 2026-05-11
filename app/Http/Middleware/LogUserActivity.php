<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $now = now();
            // Update if last_seen_at is empty, older than 1 minute, or IP changed
            if (!$user->last_seen_at || \Carbon\Carbon::parse($user->last_seen_at)->diffInMinutes($now) >= 1 || $user->last_seen_ip !== $request->ip()) {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'last_seen_at' => $now,
                        'last_seen_ip' => $request->ip(),
                    ]);
            }
        }

        return $next($request);
    }
}
