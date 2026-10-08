<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolClassIsSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $teacher = Auth::guard('web')->user();

        if (! $teacher?->current_school_class_id) {
            return redirect()->route('classes.index')->with('status', 'Maak eerst een klas aan om verder te gaan.');
        }

        return $next($request);
    }
}
