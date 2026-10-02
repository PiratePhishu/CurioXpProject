<?php

use App\Http\Controllers\ScoreboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\XpEntryController;
use Illuminate\Support\Facades\Route;

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
