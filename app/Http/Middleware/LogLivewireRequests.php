<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogLivewireRequests
{
    public function handle($request, Closure $next)
    {
        if (strpos($request->path(), 'livewire') !== false) {
            Log::info('Livewire Request', [
                'path' => $request->path(),
                'method' => $request->method(),
                'headers' => $request->headers->all(),
            ]);
        }
        return $next($request);
    }
}
