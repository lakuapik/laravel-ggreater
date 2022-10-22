<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
        'birthdate', 'location', 'timezone',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'email_verified_at' => 'datetime',
    ];

    public function greetings(): HasMany
    {
        return $this->hasMany(Greeting::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Birthdate stored in database must be in UTC.
     * But displayed to user in their local timezone.
     */
    public function setBirthdateAttribute(mixed $value): void
    {
        $this->attributes['birthdate'] = Carbon::parse($value)
            ->setTimezone($this->timezone ?: 'UTC')
            ->setTimezone('UTC');
    }

    public function nextBirthday(): Carbon
    {
        if ($this->birthdate->isBirthday()) {
            return $this->birthdate;
        }

        $date = (clone $this->birthdate)->setYear((int) date('Y'));

        return $date->isFuture() ? $date : $date->addYear();
    }

    public function nextBirthdayShouldBeGreetedAt(): Carbon
    {
        return Carbon::createFromFormat(
            'Y-m-d H:i',
            $this->nextBirthday()->format('Y-m-d').config('app.send_greeting_at'),
            $this->timezone,
        );
    }

    public function nextBirthdayInLocalTimeConvertedToUTC(): Carbon
    {
        return $this->nextBirthdayShouldBeGreetedAt()->setTimezone('UTC');
    }
}
