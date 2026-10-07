<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model implements AuthenticatableContract
{
    /** @use HasFactory<StudentFactory> */
    use Authenticatable, HasFactory;

    protected $fillable = [
        'name',
        'team',
        'position',
        'email',
        'password',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'encrypted',
            'email' => 'encrypted',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

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
