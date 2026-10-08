<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentsImport implements ToCollection
{
    /**
     * Preview password for newly imported student accounts. Students are required
     * to change this on first login.
     */
    private const PREVIEW_PASSWORD = 'Welkom123!';

    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public function __construct(private readonly int $schoolClassId) {}

    public function collection(Collection $rows): void
    {
        $nextPosition = ((int) Student::withTrashed()->where('school_class_id', $this->schoolClassId)->max('position')) + 1;

        // Email is an encrypted column, so it can't be matched with a database-level
        // WHERE (the same plaintext never encrypts to the same ciphertext twice).
        // Loading the class's roster once and comparing the decrypted values in PHP
        // is fine at this scale (a single class roster). Trashed students are
        // included so a student who was accidentally removed is restored by a
        // re-import instead of getting a second, blank account.
        $students = Student::withTrashed()->where('school_class_id', $this->schoolClassId)->get();

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
            $email = Str::lower("D{$studentNumber}@edu.curio.nl");

            $existing = $students->first(fn (Student $student) => Str::lower($student->email ?? '') === $email);

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                if ($existing->name !== $name) {
                    $existing->update(['name' => $name]);
                }

                $this->updated++;

                continue;
            }

            $students->push(Student::query()->create([
                'school_class_id' => $this->schoolClassId,
                'name' => $name,
                'email' => $email,
                'password' => self::PREVIEW_PASSWORD,
                'must_change_password' => true,
                'position' => $nextPosition++,
            ]));

            $this->created++;
        }
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
