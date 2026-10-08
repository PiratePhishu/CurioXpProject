<?php

namespace App\Http\Controllers;

use App\Exports\StudentImportTemplateExport;
use App\Imports\StudentsImport;
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

        $import = new StudentsImport($request->user()->current_school_class_id);

        Excel::import($import, $request->file('file'));

        return redirect()->route('students.index')->with(
            'status',
            "Import klaar: {$import->created} nieuwe student(en) toegevoegd, {$import->updated} al aanwezig, {$import->skipped} overgeslagen (ontbrekend studentnummer of naam)."
        );
    }
}
