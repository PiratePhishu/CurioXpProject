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
            ->withSum('xpEntries as total_points', 'points')
            ->orderBy('position')
            ->get()
            ->each(fn (Student $student) => $student->total_points ??= 0);

        $students = (match ($sort) {
            'team' => $students->sortBy('team', descending: $direction === 'desc'),
            'tot' => $students->sortBy('total_points', descending: $direction === 'desc'),
            default => $students->sortBy('name', descending: $direction === 'desc'),
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

        $nextPosition = ((int) Student::query()->max('position')) + 1;

        Student::query()->create([
            'name' => $validated['name'],
            'team' => $validated['team'] ?: null,
            'position' => $nextPosition,
        ]);

        return redirect()->route('students.index');
    }

    public function update(Request $request, Student $student): JsonResponse
    {
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

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index');
    }
}
