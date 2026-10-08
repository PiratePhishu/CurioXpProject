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
}
