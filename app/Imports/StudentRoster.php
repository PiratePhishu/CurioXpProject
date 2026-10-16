<?php

namespace App\Imports;

use App\Models\Student;

/**
 * Writes imported students into a class roster. Shared by the Excel import
 * (for rows without conflicts) and the import conflicts page (for rows the
 * teacher resolved by hand), so both paths save a student the same way.
 */
class StudentRoster
{
    /**
     * Preview password for newly imported student accounts. Students are required
     * to change this on first login.
     */
    private const PREVIEW_PASSWORD = 'Welkom123!';

    public function __construct(private readonly int $schoolClassId) {}

    public static function emailFor(string $studentNumber): string
    {
        return strtolower("D{$studentNumber}@edu.curio.nl");
    }

    /**
     * Add a new student to the class.
     */
    public function create(string $studentNumber, string $name): Student
    {
        return Student::query()->create([
            'school_class_id' => $this->schoolClassId,
            'name' => $name,
            'email' => self::emailFor($studentNumber),
            'password' => self::PREVIEW_PASSWORD,
            'must_change_password' => true,
            'position' => $this->nextPosition(),
        ]);
    }

    /**
     * Refresh a student who is already in this class. Trashed students are
     * restored so an accidentally removed student gets their account and XP
     * history back instead of a second, blank account.
     */
    public function refresh(Student $student, string $name): void
    {
        if ($student->trashed()) {
            $student->restore();
        }

        if ($student->name !== $name) {
            $student->update(['name' => $name]);
        }
    }

    /**
     * Move a student from another class into this one. The account (and so the
     * login) is kept, but XP earned in the old class is deleted and the team
     * label is cleared, since both belong to the old class.
     */
    public function moveIn(Student $student, string $name): void
    {
        if ($student->trashed()) {
            $student->restore();
        }

        $student->xpEntries()->delete();

        $student->update([
            'school_class_id' => $this->schoolClassId,
            'name' => $name,
            'team' => null,
            'position' => $this->nextPosition(),
        ]);
    }

    private function nextPosition(): int
    {
        return ((int) Student::withTrashed()->where('school_class_id', $this->schoolClassId)->max('position')) + 1;
    }
}
