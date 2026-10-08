<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::query()
            ->where('school_class_id', $request->user()->current_school_class_id)
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

    public function show(Request $request, string $team): View
    {
        $schoolClassId = $request->user()->current_school_class_id;

        $lessons = Lesson::query()->where('school_class_id', $schoolClassId)->orderBy('position')->get();
        $currentLesson = $lessons->firstWhere('code', $request->query('lesson')) ?? $lessons->first();

        $students = Student::query()
            ->where('school_class_id', $schoolClassId)
            ->where('team', $team)
            ->orderBy('position')
            ->with(['xpEntries' => fn ($query) => $query->where('lesson_id', $currentLesson?->id)])
            ->withSum('xpEntries as total_points', 'points')
            ->get()
            ->each(fn (Student $student) => $student->total_points ??= 0);

        abort_if($students->isEmpty(), 404);

        $total = $students->sum('total_points');

        return view('teams.show', [
            'team' => $team,
            'lessons' => $lessons,
            'currentLesson' => $currentLesson,
            'students' => $students,
            'totalPoints' => $total,
            'average' => (int) round($total / $students->count()),
        ]);
    }
}
