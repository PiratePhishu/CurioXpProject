<?php

namespace App\Http\Controllers;

use App\Exports\StudentImportTemplateExport;
use App\Imports\StudentsImport;
use App\Models\PendingStudentImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentImportController extends Controller
{
    public function template(): BinaryFileResponse
    {
        return Excel::download(new StudentImportTemplateExport, 'studenten-sjabloon.xlsx');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', File::default()->extensions(['xlsx', 'xls', 'csv'])->max(2048)],
        ]);

        $hasOpenConflicts = PendingStudentImport::query()
            ->where('school_class_id', $request->user()->current_school_class_id)
            ->exists();

        if ($hasOpenConflicts) {
            return redirect()->route('students.import.conflicts')->withErrors([
                'file' => 'Er staan nog importconflicten open voor deze klas. Los die eerst op voordat je opnieuw importeert.',
            ]);
        }

        $import = new StudentsImport($request->user()->current_school_class_id, $request->user()->id);

        Excel::import($import, $request->file('file'));

        $status = "Import klaar: {$import->created} nieuwe student(en) toegevoegd, {$import->updated} al aanwezig, {$import->skipped} overgeslagen (ontbrekend studentnummer of naam).";

        if ($import->conflicts > 0) {
            return redirect()->route('students.import.conflicts')->with(
                'status',
                "{$status} {$import->conflicts} dubbel(e) studentnummer(s) gevonden: kies hieronder per student wat er moet gebeuren."
            );
        }

        return redirect()->route('students.index')->with('status', $status);
    }
}
