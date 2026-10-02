<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $students = Student::query()
            ->withSum('xpEntries as total_points', 'points')
            ->get()
            ->each(fn (Student $student) => $student->total_points ??= 0);

        $teams = $students
            ->filter(fn (Student $student) => filled($student->team))
            ->groupBy('team')
            ->map(function ($members, $team) {
                $total = $members->sum('total_points');

                return [
                    'team' => $team,
                    'members' => $members->sortByDesc('total_points')->values(),
                    'count' => $members->count(),
                    'total_points' => $total,
                    'average' => $members->isEmpty() ? 0 : (int) round($total / $members->count()),
                ];
            })
            ->sortByDesc('total_points')
            ->values();

        return view('teams.index', [
            'teams' => $teams,
        ]);
    }
}
