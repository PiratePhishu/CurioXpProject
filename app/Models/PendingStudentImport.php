<?php

namespace App\Models;

use Database\Factories\PendingStudentImportFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An imported row that couldn't be saved automatically: its student number
 * appeared more than once in the file with different names, and/or it belongs
 * to a student who is already in another class. A teacher resolves it on the
 * import conflicts page.
 */
class PendingStudentImport extends Model
{
    /** @use HasFactory<PendingStudentImportFactory> */
    use HasFactory, MassPrunable;

    protected $fillable = [
        'user_id',
        'school_class_id',
        'existing_student_id',
        'student_number',
        'candidate_names',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'student_number' => 'encrypted',
            'candidate_names' => 'encrypted:array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * Includes trashed students: a soft-deleted student still owns their
     * student number and is restored when moved.
     *
     * @return BelongsTo<Student, $this>
     */
    public function existingStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'existing_student_id')->withTrashed();
    }

    /**
     * Whether the teacher has to choose between the candidate names.
     */
    public function hasNameConflict(): bool
    {
        return count($this->candidate_names) > 1;
    }

    /**
     * Whether the student number already belongs to a student in another class.
     */
    public function hasClassConflict(): bool
    {
        return $this->existingStudent !== null
            && $this->existingStudent->school_class_id !== $this->school_class_id;
    }

    /**
     * Abandoned conflicts are cleaned up after a week.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<=', now()->subDays(7));
    }
}
