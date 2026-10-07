<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentPasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $student = Auth::guard('student')->user();

        if ($student?->must_change_password && ! $request->routeIs('student.password.*', 'student.logout')) {
            return redirect()->route('student.password.edit');
        }

        return $next($request);
    }
}
