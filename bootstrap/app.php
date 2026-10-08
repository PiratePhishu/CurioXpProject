<?php

use App\Http\Middleware\AuthenticateStudent;
use App\Http\Middleware\AuthenticateTeacher;
use App\Http\Middleware\EnsureSchoolClassIsSelected;
use App\Http\Middleware\EnsureStudentPasswordIsChanged;
use App\Http\Middleware\EnsureTeacherPasswordIsChanged;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.teacher' => AuthenticateTeacher::class,
            'auth.student' => AuthenticateStudent::class,
            'student.password' => EnsureStudentPasswordIsChanged::class,
            'teacher.password' => EnsureTeacherPasswordIsChanged::class,
            'class.selected' => EnsureSchoolClassIsSelected::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
