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

    public function collection(Collection $rows): void
    {
        $nextPosition = ((int) Student::query()->max('position')) + 1;

        // Name and email are encrypted columns, so they can't be matched with a
        // database-level WHERE (the same plaintext never encrypts to the same
        // ciphertext twice). Loading the full table once and comparing the
        // decrypted values in PHP is fine at this scale (a single class roster).
        $students = Student::query()->get();
        $emails = $students->pluck('email')->filter()->all();

        foreach ($rows as $row) {
            $name = trim((string) ($row[0] ?? ''));

            if ($name === '' || in_array(Str::lower($name), ['naam', 'name'], true)) {
                continue;
            }

            $existing = $students->first(fn (Student $student) => Str::lower($student->name) === Str::lower($name));

            if ($existing) {
                if ($existing->name !== $name) {
                    $existing->update(['name' => $name]);
                }

                $this->updated++;

                continue;
            }

            $email = $this->uniqueEmail($name, $emails);
            $emails[] = $email;

            $students->push(Student::query()->create([
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
     * @param  array<int, string>  $existingEmails
     */
    private function uniqueEmail(string $name, array $existingEmails): string
    {
        $base = Str::slug($name, '.') ?: 'student';
        $email = "{$base}@curio-demo.test";
        $suffix = 1;

        while (in_array($email, $existingEmails, true)) {
            $suffix++;
            $email = "{$base}{$suffix}@curio-demo.test";
        }

        return $email;
    }
}
