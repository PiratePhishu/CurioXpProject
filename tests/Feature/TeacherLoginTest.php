<?php

use App\Models\User;

it('redirects guests away from the overview to the teacher login page', function () {
    $this->get('/')->assertRedirect(route('teacher.login'));
});

it('logs a teacher in with correct credentials', function () {
    $teacher = User::factory()->create(['password' => 'password']);

    $response = $this->post(route('teacher.login.store'), [
        'email' => $teacher->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('scoreboard'));
    $this->assertAuthenticatedAs($teacher, 'web');
});

it('rejects a teacher login with incorrect credentials', function () {
    $teacher = User::factory()->create(['password' => 'password']);

    $response = $this->from(route('teacher.login'))->post(route('teacher.login.store'), [
        'email' => $teacher->email,
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect(route('teacher.login'));
    $this->assertGuest('web');
});

it('logs a teacher out', function () {
    $teacher = User::factory()->create();

    $this->actingAs($teacher)
        ->post(route('teacher.logout'))
        ->assertRedirect(route('teacher.login'));

    $this->assertGuest('web');
});
