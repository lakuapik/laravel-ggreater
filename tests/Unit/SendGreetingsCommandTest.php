<?php

use App\Jobs\SendGreetingToEmailServiceJob;
use App\Models\Greeting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use function Pest\Laravel\travelTo;

uses(Tests\TestCase::class);

it('should queue sendGreetingToEmailServiceJob given birthday is today', function () {
    //
    $users = User::factory(5)->create(['birthdate' => today('UTC'), 'timezone' => 'UTC']);
    $users->each(fn (User $user) => Greeting::factory()->withUser($user)->create());

    Bus::fake();

    travelTo(today('UTC')->setTime(9, 0));

    Bus::assertDispatchedTimes(SendGreetingToEmailServiceJob::class, 0);

    travelTo(today('UTC')->setTime(9, 0));

    Artisan::call('app:send-greetings');

    Bus::assertDispatchedTimes(SendGreetingToEmailServiceJob::class, 5);
});

it('should not queue sendGreetingToEmailServiceJob given birthday is yesterday or tomorrow', function () {
    //
    $users1 = User::factory(2)->create(['birthdate' => today('UTC')->subDay(), 'timezone' => 'UTC']);
    $users2 = User::factory(3)->create(['birthdate' => today('UTC')->addDay(), 'timezone' => 'UTC']);
    $users = $users1->merge($users2);
    $users->each(fn (User $user) => Greeting::factory()->withUser($user)->create());

    Bus::fake();

    Artisan::call('app:send-greetings');

    Bus::assertNothingDispatched();
    Bus::assertDispatchedTimes(SendGreetingToEmailServiceJob::class, 0);
});
