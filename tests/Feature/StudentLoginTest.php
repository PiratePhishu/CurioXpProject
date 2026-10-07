<?php

use App\Models\Lesson;
use App\Models\Student;
use App\Models\XpEntry;

it('redirects guests away from the dashboard to the student login page', function () {
    $this->get('/mijn-score')->assertRedirect(route('student.login'));
});

it('logs a student in and forces a password change on first login', function () {
    $student = Student::factory()->create(['password' => 'password', 'must_change_password' => true]);

    $response = $this->post(route('student.login.store'), [
        'email' => $student->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('student.dashboard'));
    $this->assertAuthenticatedAs($student, 'student');

    $this->get(route('student.dashboard'))->assertRedirect(route('student.password.edit'));
});

it('rejects a student login with incorrect credentials', function () {
    $student = Student::factory()->create(['password' => 'password']);

    $response = $this->from(route('student.login'))->post(route('student.login.store'), [
        'email' => $student->email,
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect(route('student.login'));
    $this->assertGuest('student');
});

it('lets a student change their password and then reach the dashboard', function () {
    $student = Student::factory()->create(['must_change_password' => true]);

    $this->actingAs($student, 'student')
        ->put(route('student.password.update'), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('student.dashboard'));

    $student->refresh();
    expect($student->must_change_password)->toBeFalse();

    $this->get(route('student.dashboard'))->assertOk();
});

it('only shows the logged in student their own score', function () {
    $lesson = Lesson::factory()->create(['max_points' => 300]);
    $me = Student::factory()->create(['name' => 'Eigen Student', 'must_change_password' => false]);
    $other = Student::factory()->create(['name' => 'Andere Student', 'must_change_password' => false]);

    XpEntry::factory()->create(['student_id' => $me->id, 'lesson_id' => $lesson->id, 'points' => 120]);
    XpEntry::factory()->create(['student_id' => $other->id, 'lesson_id' => $lesson->id, 'points' => 999]);

    $response = $this->actingAs($me, 'student')->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('120');
    $response->assertDontSee('999');
    $response->assertDontSee('Andere Student');
});

it('logs a student out', function () {
    $student = Student::factory()->create();

    $this->actingAs($student, 'student')
        ->post(route('student.logout'))
        ->assertRedirect(route('student.login'));

    $this->assertGuest('student');
});
