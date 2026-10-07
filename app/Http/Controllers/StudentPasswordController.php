<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPasswordController extends Controller
{
    public function edit(): View
    {
        return view('student.change-password', [
            'student' => Auth::guard('student')->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $student = Auth::guard('student')->user();

        $student->update([
            'password' => $validated['password'],
            'must_change_password' => false,
        ]);

        return redirect()->route('student.dashboard')->with('status', 'Je wachtwoord is gewijzigd.');
    }
}
