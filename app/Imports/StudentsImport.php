<?php

namespace App\Imports;

use App\Models\PendingStudentImport;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentsImport implements ToCollection
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public int $conflicts = 0;

    public function __construct(
        private readonly int $schoolClassId,
        private readonly int $userId,
    ) {}

    public function collection(Collection $rows): void
    {
        $roster = new StudentRoster($this->schoolClassId);

        // Email is an encrypted column, so it can't be matched with a database-level
        // WHERE (the same plaintext never encrypts to the same ciphertext twice).
        // Every student in every class is loaded once and compared on the decrypted
        // value in PHP, which is fine at this scale. All classes are searched because
        // the email is the student's login: the same student number in two classes
        // would make that login ambiguous. Trashed students are included so a student
        // who was accidentally removed is restored instead of getting a second account.
        $studentsByEmail = Student::withTrashed()->get()
            ->keyBy(fn (Student $student) => Str::lower($student->email ?? ''));

        foreach ($this->namesByStudentNumber($rows) as $studentNumber => $names) {
            $existing = $studentsByEmail->get(StudentRoster::emailFor($studentNumber));
            $isInOtherClass = $existing !== null && $existing->school_class_id !== $this->schoolClassId;

            if (count($names) > 1 || $isInOtherClass) {
                PendingStudentImport::query()->create([
                    'user_id' => $this->userId,
                    'school_class_id' => $this->schoolClassId,
                    'existing_student_id' => $existing?->id,
                    'student_number' => (string) $studentNumber,
                    'candidate_names' => $names,
                ]);

                $this->conflicts++;

                continue;
            }

            if ($existing) {
                $roster->refresh($existing, $names[0]);

                $this->updated++;

                continue;
            }

            $roster->create((string) $studentNumber, $names[0]);

            $this->created++;
        }
    }

    /**
     * Group the sheet's data rows by student number, keeping every distinct name
     * a number appears with (in file order). Rows repeating the exact same number
     * and name collapse into one.
     *
     * @return array<string, list<string>>
     */
    private function namesByStudentNumber(Collection $rows): array
    {
        $namesByStudentNumber = [];

        foreach ($rows as $row) {
            $studentNumber = $this->normalizeStudentNumber($row[0] ?? '');
            $naam = trim((string) ($row[1] ?? ''));

            if ($studentNumber === '' && $naam === '') {
                continue;
            }

            if (! ctype_digit($studentNumber)) {
                // Header row (e.g. "Studentnummer") or other non-data row.
                continue;
            }

            if ($naam === '') {
                $this->skipped++;

                continue;
            }

            $name = $this->formatName($naam);

            if (! in_array($name, $namesByStudentNumber[$studentNumber] ?? [], true)) {
                $namesByStudentNumber[$studentNumber][] = $name;
            }
        }

        return $namesByStudentNumber;
    }

    /**
     * Excel/PhpSpreadsheet reads a plain numeric cell as a float (e.g. 318218.0),
     * so strip that back down to a clean digit string.
     */
    private function normalizeStudentNumber(mixed $value): string
    {
        return is_numeric($value) ? (string) (int) $value : trim((string) $value);
    }

    /**
     * The source sheet lists names as "Achternaam, Voornaam [tussenvoegsel]".
     * Reformat to "Voornaam [tussenvoegsel] Achternaam" for display.
     */
    private function formatName(string $naam): string
    {
        if (! str_contains($naam, ',')) {
            return $naam;
        }

        [$lastName, $firstName] = array_map('trim', explode(',', $naam, 2));

        return trim("{$firstName} {$lastName}");
    }
}
