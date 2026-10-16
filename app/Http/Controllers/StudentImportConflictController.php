<?php

namespace App\Http\Controllers;

use App\Imports\StudentRoster;
use App\Models\PendingStudentImport;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentImportConflictController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $conflicts = $this->conflictsFor($request);

        if ($conflicts->isEmpty()) {
            return redirect()->route('students.index');
        }

        return view('students.import-conflicts', [
            'conflicts' => $conflicts,
            'targetClass' => $request->user()->currentSchoolClass,
        ]);
    }

    public function resolve(Request $request): RedirectResponse
    {
        $conflicts = $this->conflictsFor($request);

        $rules = [];

        foreach ($conflicts as $conflict) {
            if ($conflict->hasNameConflict()) {
                $rules["resolutions.{$conflict->id}.name"] = ['required', 'string', Rule::in($conflict->candidate_names)];
            }

            if ($conflict->hasClassConflict()) {
                $rules["resolutions.{$conflict->id}.placement"] = ['required', Rule::in(['keep', 'move'])];
            }
        }

        $validated = $request->validate($rules, [
            'resolutions.*.name.required' => 'Kies voor elke student de juiste naam.',
            'resolutions.*.placement.required' => 'Kies voor elke student of die moet blijven of verplaatst moet worden.',
        ]);

        $roster = new StudentRoster($request->user()->current_school_class_id);
        $counts = ['created' => 0, 'updated' => 0, 'moved' => 0, 'kept' => 0];

        DB::transaction(function () use ($conflicts, $validated, $roster, &$counts) {
            foreach ($conflicts as $conflict) {
                $resolution = $validated['resolutions'][$conflict->id] ?? [];
                $name = $resolution['name'] ?? $conflict->candidate_names[0];
                $existing = $conflict->existingStudent;

                if ($conflict->hasClassConflict()) {
                    if ($resolution['placement'] === 'move') {
                        $roster->moveIn($existing, $name);
                        $counts['moved']++;
                    } else {
                        $counts['kept']++;
                    }
                } elseif ($existing) {
                    $roster->refresh($existing, $name);
                    $counts['updated']++;
                } else {
                    $roster->create($conflict->student_number, $name);
                    $counts['created']++;
                }

                $conflict->delete();
            }
        });

        return redirect()->route('students.index')->with(
            'status',
            "Conflicten opgelost: {$counts['created']} nieuwe student(en) toegevoegd, {$counts['updated']} bijgewerkt, {$counts['moved']} verplaatst naar deze klas, {$counts['kept']} in hun huidige klas gelaten."
        );
    }

    /**
     * Throw away the open conflicts of the current class without saving any of
     * them, e.g. after uploading the wrong file, so a new import can be started.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $discarded = PendingStudentImport::query()
            ->where('school_class_id', $request->user()->current_school_class_id)
            ->delete();

        return redirect()->route('students.index')->with(
            'status',
            "{$discarded} importconflict(en) verworpen. Je kunt nu opnieuw importeren."
        );
    }

    /**
     * The open conflicts for the teacher's current class, with the existing
     * student's class, school year and XP total loaded up front.
     *
     * @return Collection<int, PendingStudentImport>
     */
    private function conflictsFor(Request $request): Collection
    {
        return PendingStudentImport::query()
            ->where('school_class_id', $request->user()->current_school_class_id)
            ->with([
                'existingStudent' => fn ($query) => $query
                    ->withSum('xpEntries as total_points', 'points')
                    ->with('schoolClass.schoolYear'),
            ])
            ->oldest('id')
            ->get();
    }
}
