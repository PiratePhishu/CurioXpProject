<?php

namespace App\Models;

use Database\Factories\SchoolYearFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model
{
    /** @use HasFactory<SchoolYearFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<SchoolClass, $this>
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * The school year label (e.g. "2026/2027") that today falls in, using August
     * as the cutoff between one academic year and the next.
     */
    public static function currentAcademicYearLabel(): string
    {
        $now = now();
        $startYear = $now->month >= 8 ? $now->year : $now->year - 1;

        return "{$startYear}/".($startYear + 1);
    }

    /**
     * Labels to offer when picking a school year for a new class: the current
     * academic year plus a run of upcoming ones, merged with any year that
     * already exists (e.g. one created on the fly in the past), sorted
     * chronologically.
     *
     * @return array<int, string>
     */
    public static function selectableLabels(int $yearsAhead = 9): array
    {
        $now = now();
        $startYear = $now->month >= 8 ? $now->year : $now->year - 1;

        $generated = [];

        for ($i = 0; $i <= $yearsAhead; $i++) {
            $generated[] = ($startYear + $i).'/'.($startYear + $i + 1);
        }

        return collect($generated)
            ->merge(self::query()->pluck('name'))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
