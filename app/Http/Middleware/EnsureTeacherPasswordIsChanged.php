<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacherPasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $teacher = Auth::guard('web')->user();

        if ($teacher?->must_change_password && ! $request->routeIs('teacher.password.*', 'teacher.logout')) {
            return redirect()->route('teacher.password.edit');
        }

        return $next($request);
    }
}
