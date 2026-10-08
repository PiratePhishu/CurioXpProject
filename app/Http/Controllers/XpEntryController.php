<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\XpEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class XpEntryController extends Controller
{
    public function index(Request $request): View
    {
        $schoolClassId = $request->user()->current_school_class_id;

        $lessons = Lesson::query()->where('school_class_id', $schoolClassId)->orderBy('position')->get();
        $currentLesson = $lessons->firstWhere('code', $request->query('lesson')) ?? $lessons->first();

        $students = Student::query()
            ->where('school_class_id', $schoolClassId)
            ->orderBy('position')
            ->with(['xpEntries' => fn ($query) => $query->where('lesson_id', $currentLesson?->id)])
            ->withSum('xpEntries as total_points', 'points')
            ->get()
            ->each(fn (Student $student) => $student->total_points ??= 0);

        return view('xp.index', [
            'lessons' => $lessons,
            'currentLesson' => $currentLesson,
            'students' => $students,
        ]);
    }

    public function overview(Request $request): View
    {
        $schoolClassId = $request->user()->current_school_class_id;

        $lessons = Lesson::query()->where('school_class_id', $schoolClassId)->orderBy('position')->get();

        $students = Student::query()
            ->where('school_class_id', $schoolClassId)
            ->orderBy('position')
            ->with('xpEntries')
            ->withSum('xpEntries as total_points', 'points')
            ->get()
            ->each(fn (Student $student) => $student->total_points ??= 0);

        return view('xp.overview', [
            'lessons' => $lessons,
            'students' => $students,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $schoolClassId = $request->user()->current_school_class_id;

        $validated = $request->validate([
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where('school_class_id', $schoolClassId)],
            'lesson_id' => ['required', 'integer', Rule::exists('lessons', 'id')->where('school_class_id', $schoolClassId)],
            'points' => ['nullable', 'integer', 'min:0'],
        ]);

        $lesson = Lesson::query()->findOrFail($validated['lesson_id']);

        if ($validated['points'] !== null && $validated['points'] > $lesson->max_points) {
            throw ValidationException::withMessages([
                'points' => ["Maximaal {$lesson->max_points} punten voor deze les."],
            ]);
        }

        if ($validated['points'] === null) {
            XpEntry::query()
                ->where('student_id', $validated['student_id'])
                ->where('lesson_id', $validated['lesson_id'])
                ->delete();
        } else {
            XpEntry::query()->updateOrCreate(
                [
                    'student_id' => $validated['student_id'],
                    'lesson_id' => $validated['lesson_id'],
                ],
                ['points' => $validated['points']]
            );
        }

        $total = XpEntry::query()->where('student_id', $validated['student_id'])->sum('points');

        return response()->json(['saved' => true, 'total_points' => $total]);
    }

    public function fillAll(Request $request): JsonResponse
    {
        $schoolClassId = $request->user()->current_school_class_id;

        $validated = $request->validate([
            'lesson_id' => ['required', 'integer', Rule::exists('lessons', 'id')->where('school_class_id', $schoolClassId)],
            'points' => ['required', 'integer', 'min:0'],
        ]);

        $lesson = Lesson::query()->findOrFail($validated['lesson_id']);

        if ($validated['points'] > $lesson->max_points) {
            throw ValidationException::withMessages([
                'points' => ["Maximaal {$lesson->max_points} punten voor deze les."],
            ]);
        }

        $now = now();

        $rows = Student::query()
            ->where('school_class_id', $schoolClassId)
            ->pluck('id')
            ->map(fn (int $studentId) => [
                'student_id' => $studentId,
                'lesson_id' => $lesson->id,
                'points' => $validated['points'],
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

        if ($rows !== []) {
            XpEntry::query()->upsert($rows, ['student_id', 'lesson_id'], ['points', 'updated_at']);
        }

        return response()->json(['saved' => true]);
    }
}
