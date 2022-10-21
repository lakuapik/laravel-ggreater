<?php

namespace App\Enums;

use Exception;

enum GreetingType: string
{
    case BIRTHDAY = 'Happy Birthday';

    case ANNIVERSARY = 'Happy Anniversary'; // for future

    public function getMessage(?string $fullName = null): string
    {
        $message = match ($this) {
            self::BIRTHDAY => 'Hey, :fullName its your birthday',
            default => throw new Exception('Not Implemented!'),
        };

        if (filled($fullName)) {
            $message = str_replace(':fullName', $fullName, $message);
        }

        return $message;
    }
}
