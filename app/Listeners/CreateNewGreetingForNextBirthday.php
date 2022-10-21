<?php

namespace App\Listeners;

use App\Events\OnEmailServiceJobWasSentGreeting;
use App\Models\Greeting;
use Carbon\Carbon;

class CreateNewGreetingForNextBirthday
{
    public function __construct(
        public Greeting $greeting
    ) {
        // pass
    }

    public function handle(OnEmailServiceJobWasSentGreeting $event): void
    {
        [$greeting, $user] = [$event->greeting, $event->greeting->user];

        $localtime = Carbon::parse($greeting->metadata['original']['localtime']);
        $utctime = Carbon::parse($greeting->metadata['original']['utctime']);

        Greeting::create([
            'type' => $greeting->type,
            'user_id' => $user->id,
            'message' => $greeting->message,
            'for_date' => $greeting->for_date->addYear(),
            'available_at' => $greeting->available_at->addYear(),
            'metadata' => [
                'original' => [
                    'email' => $user->email,
                    'timezone' => $user->timezone,
                    'localtime' => $localtime->format('Y-m-d H:i:s'),
                    'utctime' => $utctime->format('Y-m-d H:i:s'),
                ],
            ],
        ]);
    }
}
