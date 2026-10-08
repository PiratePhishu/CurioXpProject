<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->string('sort', 'naam')->toString();
        $direction = $request->string('dir', 'asc')->toString();

        $students = Student::query()
            ->where('school_class_id', $request->user()->current_school_class_id)
            ->withSum('xpEntries as total_points', 'points')
            ->orderBy('position')
            ->get()
            ->each(fn (Student $student) => $student->total_points ??= 0);

        $students = (match ($sort) {
            'team' => $students->sortBy('team', descending: $direction === 'desc'),
            'tot' => $students->sortBy('total_points', descending: $direction === 'desc'),
            default => $students->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc'),
        })->values();

        return view('students.index', [
            'students' => $students,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'team' => ['nullable', 'string', 'max:10'],
        ]);

        $schoolClassId = $request->user()->current_school_class_id;

        $nextPosition = ((int) Student::query()->where('school_class_id', $schoolClassId)->max('position')) + 1;

        Student::query()->create([
            'school_class_id' => $schoolClassId,
            'name' => $validated['name'],
            'team' => $validated['team'] ?: null,
            'position' => $nextPosition,
        ]);

        return redirect()->route('students.index');
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        abort_unless($student->school_class_id === $request->user()->current_school_class_id, 404);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'team' => ['sometimes', 'nullable', 'string', 'max:10'],
        ]);

        if (array_key_exists('team', $validated)) {
            $validated['team'] = $validated['team'] ?: null;
        }

        $student->update($validated);

        return response()->json(['saved' => true]);
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        abort_unless($student->school_class_id === $request->user()->current_school_class_id, 404);

        $student->delete();

        return redirect()->route('students.index');
    }
}
