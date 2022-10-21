<?php

namespace App\Providers;

use App\Events\OnEmailServiceJobWasSentGreeting;
use App\Listeners\CreateNewGreetingForNextBirthday;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OnEmailServiceJobWasSentGreeting::class => [
            CreateNewGreetingForNextBirthday::class,
        ],
    ];

    public function boot(): void
    {
        // pass
    }
}
