<?php

use App\Models\User;
use App\Notifications\TeacherResetPasswordNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

it('sends a password reset link to a known teacher email', function () {
    Notification::fake();

    $teacher = User::factory()->create();

    $this->post(route('teacher.password.email'), ['email' => $teacher->email])
        ->assertRedirect();

    Notification::assertSentTo($teacher, TeacherResetPasswordNotification::class);
});

it('does not reveal whether a teacher email exists', function () {
    Notification::fake();

    $response = $this->post(route('teacher.password.email'), ['email' => 'unknown@curio-demo.test']);

    $response->assertRedirect();
    $response->assertSessionHas('status');
    Notification::assertNothingSent();
});

it('resets a teacher password using a valid emailed token', function () {
    Notification::fake();

    $teacher = User::factory()->create(['password' => 'old-password']);

    $this->post(route('teacher.password.email'), ['email' => $teacher->email]);

    $token = null;
    Notification::assertSentTo($teacher, TeacherResetPasswordNotification::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $response = $this->post(route('teacher.password.reset.update'), [
        'token' => $token,
        'email' => $teacher->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect(route('teacher.login'));

    expect(Auth::guard('web')->attempt(['email' => $teacher->email, 'password' => 'new-password']))->toBeTrue();
});

it('rejects a reset with an invalid token', function () {
    $teacher = User::factory()->create();

    $response = $this->post(route('teacher.password.reset.update'), [
        'token' => 'invalid-token',
        'email' => $teacher->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Auth::guard('web')->attempt(['email' => $teacher->email, 'password' => 'new-password']))->toBeFalse();
});
