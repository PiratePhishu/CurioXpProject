<?php

use App\Models\Student;
use App\Notifications\StudentResetPasswordNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

it('sends a password reset link to a known student email', function () {
    Notification::fake();

    $student = Student::factory()->create();

    $this->post(route('student.password.email'), ['email' => $student->email])
        ->assertRedirect();

    Notification::assertSentTo($student, StudentResetPasswordNotification::class);
});

it('does not reveal whether a student email exists', function () {
    Notification::fake();

    $response = $this->post(route('student.password.email'), ['email' => 'unknown@curio-demo.test']);

    $response->assertRedirect();
    $response->assertSessionHas('status');
    Notification::assertNothingSent();
});

it('resets a student password using a valid emailed token and clears the forced change flag', function () {
    Notification::fake();

    $student = Student::factory()->create(['password' => 'old-password', 'must_change_password' => true]);

    $this->post(route('student.password.email'), ['email' => $student->email]);

    $token = null;
    Notification::assertSentTo($student, StudentResetPasswordNotification::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $response = $this->post(route('student.password.reset.update'), [
        'token' => $token,
        'email' => $student->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect(route('student.login'));

    expect(Auth::guard('student')->attempt(['email' => $student->email, 'password' => 'new-password']))->toBeTrue();
    expect($student->fresh()->must_change_password)->toBeFalse();
});

it('rejects a reset with an invalid token', function () {
    $student = Student::factory()->create();

    $response = $this->post(route('student.password.reset.update'), [
        'token' => 'invalid-token',
        'email' => $student->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Auth::guard('student')->attempt(['email' => $student->email, 'password' => 'new-password']))->toBeFalse();
});
