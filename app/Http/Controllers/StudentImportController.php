<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', File::default()->extensions(['xlsx', 'xls', 'csv'])->max(2048)],
        ]);

        $import = new StudentsImport;

        Excel::import($import, $request->file('file'));

        return redirect()->route('students.index')->with(
            'status',
            "Import klaar: {$import->created} nieuwe student(en) toegevoegd, {$import->updated} al aanwezig."
        );
    }
}
