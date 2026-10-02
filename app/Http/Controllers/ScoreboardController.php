<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\XpLevel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ScoreboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $sort = $request->string('sort', 'tot')->toString();
        $direction = $request->string('dir', 'desc')->toString();

        $students = Student::query()
            ->withSum('xpEntries as total_points', 'points')
            ->get()
            ->each(fn (Student $student) => $student->total_points ??= 0);

        $students = (match ($sort) {
            'naam' => $students->sortBy('name', descending: $direction === 'desc'),
            'team' => $students->sortBy('team', descending: $direction === 'desc'),
            default => $students->sortBy('total_points', descending: $direction === 'desc'),
        })->values();

        return view('scoreboard.index', [
            'students' => $students,
            'sort' => $sort,
            'direction' => $direction,
            'levels' => XpLevel::all(),
        ]);
    }
}
