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
        'name', 'email', 'password',
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

    // TODO: split user name into first_name and last_name
    //       and use virtual column for full_name
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Birthdate stored in database must be in UTC.
     * But displayed to user in their local timezone.
     */
    public function setBirthdateAttribute(mixed $value): void
    {
        $this->attributes['birthdate'] = Carbon::parse($value)->setTimezone('UTC');
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
