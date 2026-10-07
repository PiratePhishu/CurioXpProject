<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Support\XpLevel;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function __invoke(): View
    {
        $student = Auth::guard('student')->user();

        $lessons = Lesson::query()->orderBy('position')->get();

        $student->load('xpEntries');
        $totalPoints = $student->totalPoints();

        return view('student.dashboard', [
            'student' => $student,
            'lessons' => $lessons,
            'totalPoints' => $totalPoints,
            'level' => XpLevel::for($totalPoints),
            'progress' => XpLevel::progress($totalPoints) * 100,
            'levels' => XpLevel::all(),
        ]);
    }
}
