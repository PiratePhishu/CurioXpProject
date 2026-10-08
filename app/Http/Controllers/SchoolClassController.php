<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        return view('classes.index', [
            'classes' => SchoolClass::query()->with('schoolYear')->withCount('students')->orderBy('name')->get(),
            'defaultYear' => SchoolYear::currentAcademicYearLabel(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'string', 'max:50'],
        ]);

        $schoolYear = SchoolYear::query()->firstOrCreate(['name' => $validated['year']]);

        $nameTaken = SchoolClass::query()
            ->where('school_year_id', $schoolYear->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($nameTaken) {
            throw ValidationException::withMessages([
                'name' => ["Er bestaat al een klas \"{$validated['name']}\" in {$schoolYear->name}."],
            ]);
        }

        $schoolClass = SchoolClass::query()->create([
            'school_year_id' => $schoolYear->id,
            'name' => $validated['name'],
        ]);

        $currentClassId = $request->user()->current_school_class_id;

        if ($currentClassId) {
            Lesson::query()
                ->where('school_class_id', $currentClassId)
                ->orderBy('position')
                ->get()
                ->each(fn (Lesson $lesson) => $schoolClass->lessons()->create([
                    'code' => $lesson->code,
                    'week' => $lesson->week,
                    'name' => $lesson->name,
                    'max_points' => $lesson->max_points,
                    'position' => $lesson->position,
                ]));
        }

        return redirect()->route('classes.index')->with('status', "Klas \"{$schoolClass->name}\" aangemaakt in {$schoolYear->name}.");
    }

    public function update(Request $request, SchoolClass $schoolClass): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')->where('school_year_id', $schoolClass->school_year_id)->ignore($schoolClass->id)],
        ]);

        $schoolClass->update($validated);

        return response()->json(['saved' => true]);
    }

    public function activate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
        ]);

        $request->user()->update(['current_school_class_id' => $validated['school_class_id']]);

        return redirect()->back();
    }
}
