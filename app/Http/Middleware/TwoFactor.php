<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (Auth::check() && $user->code) {
            if (! $request->is('verify', 'verify/*')) {
                return redirect()->route('verify.index');
            }
        }

        return $next($request);
    }
}
