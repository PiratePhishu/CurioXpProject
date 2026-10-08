<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class StudentForgotPasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.student-forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'string', 'email']]);

        Password::broker('students')->sendResetLink($request->only('email'));

        return back()->with('status', 'Als dit e-mailadres bekend is, is er een resetlink naartoe gestuurd.');
    }
}
