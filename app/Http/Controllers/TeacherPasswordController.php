<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherPasswordController extends Controller
{
    public function edit(): View
    {
        return view('teacher.change-password', [
            'teacher' => Auth::guard('web')->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $teacher = Auth::guard('web')->user();

        $teacher->update([
            'password' => $validated['password'],
            'must_change_password' => false,
        ]);

        return redirect()->route('scoreboard')->with('status', 'Je wachtwoord is gewijzigd.');
    }
}
