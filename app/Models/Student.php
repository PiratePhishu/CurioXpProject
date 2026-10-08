<?php

namespace App\Models;

use App\Notifications\StudentResetPasswordNotification;
use Database\Factories\StudentFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Student extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    /** @use HasFactory<StudentFactory> */
    use Authenticatable, CanResetPassword, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'school_class_id',
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

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function totalPoints(): int
    {
        return $this->xpEntries->sum('points');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new StudentResetPasswordNotification($token));
    }
}
