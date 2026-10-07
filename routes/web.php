<?php

use App\Http\Controllers\ScoreboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\StudentLoginController;
use App\Http\Controllers\StudentPasswordController;
use App\Http\Controllers\TeacherLoginController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\XpEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/inloggen/docent', [TeacherLoginController::class, 'show'])->name('teacher.login');
Route::post('/inloggen/docent', [TeacherLoginController::class, 'store'])->name('teacher.login.store');
Route::post('/uitloggen/docent', [TeacherLoginController::class, 'destroy'])->name('teacher.logout');

Route::get('/inloggen/student', [StudentLoginController::class, 'show'])->name('student.login');
Route::post('/inloggen/student', [StudentLoginController::class, 'store'])->name('student.login.store');
Route::post('/uitloggen/student', [StudentLoginController::class, 'destroy'])->name('student.logout');

Route::middleware('auth.teacher:web')->group(function () {
    Route::get('/', ScoreboardController::class)->name('scoreboard');
    Route::get('/teams', TeamController::class)->name('teams');

    Route::get('/xp', [XpEntryController::class, 'index'])->name('xp.index');
    Route::get('/xp/overzicht', [XpEntryController::class, 'overview'])->name('xp.overview');
    Route::put('/xp', [XpEntryController::class, 'update'])->name('xp.update');
    Route::put('/xp/fill-all', [XpEntryController::class, 'fillAll'])->name('xp.fill-all');

    Route::get('/studenten', [StudentController::class, 'index'])->name('students.index');
    Route::post('/studenten', [StudentController::class, 'store'])->name('students.store');
    Route::patch('/studenten/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/studenten/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::post('/studenten/importeren', [StudentImportController::class, 'store'])->name('students.import');
});

Route::middleware('auth.student:student')->group(function () {
    Route::get('/wachtwoord-wijzigen', [StudentPasswordController::class, 'edit'])->name('student.password.edit');
    Route::put('/wachtwoord-wijzigen', [StudentPasswordController::class, 'update'])->name('student.password.update');

    Route::get('/mijn-score', StudentDashboardController::class)
        ->middleware('student.password')
        ->name('student.dashboard');
});
