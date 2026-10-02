<?php

namespace App\Models;

use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'week',
        'name',
        'max_points',
        'position',
    ];

    /**
     * @return HasMany<XpEntry, $this>
     */
    public function xpEntries(): HasMany
    {
        return $this->hasMany(XpEntry::class);
    }
}
