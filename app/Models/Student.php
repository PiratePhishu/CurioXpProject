<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'team',
        'position',
    ];

    /**
     * @return HasMany<XpEntry, $this>
     */
    public function xpEntries(): HasMany
    {
        return $this->hasMany(XpEntry::class);
    }

    public function totalPoints(): int
    {
        return $this->xpEntries->sum('points');
    }
}
