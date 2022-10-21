<?php

use App\Http\Integrations\EmailService\Requests\SendEmail;
use App\Jobs\SendGreetingToEmailServiceJob;
use App\Models\Greeting;
use App\Models\User;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Response;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\SaloonLaravel\Facades\Saloon;

uses(Tests\TestCase::class);

it('should send to email service given valid condition', function () {
    //
    $user = User::factory()->create(['birthdate' => today('UTC'), 'timezone' => 'UTC']);
    $greeting = Greeting::factory()->withUser($user)->create();

    expect($user->greetings()->count())->toBe(1);

    Saloon::fake([
        MockResponse::make([
            'status' => 'sent',
            'sentTime' => now()->toISOString(),
        ], Response::HTTP_OK),
    ]);

    expect($greeting)
        ->sending_at->toBeEmpty()
        ->sent_at->toBeEmpty();

    expect(Greeting::ready()->count())->toBe(1);

    SendGreetingToEmailServiceJob::dispatch($greeting);

    Saloon::assertSent(function (SendEmail $request) use ($user, $greeting) {
        return $request->email == $user->email
            && $request->message = $greeting->message;
    });

    expect(Greeting::sent()->count())->toBe(1);

    expect($greeting->refresh())
        ->sending_at->not->toBeEmpty()
        ->sent_at->not->toBeEmpty()
        ->metadata->toHaveKey('email-service.status', 'sent')
        ->metadata->toHaveKey('email-service.sentTime');
});

it('should not send to email service given greeting already sent', function () {
    //
    $user = User::factory()->create(['birthdate' => today('UTC'), 'timezone' => 'UTC']);
    $greeting = Greeting::factory()->withUser($user)->create([]);
    $greeting->update(['sending_at' => now(), 'sent_at' => now()]);

    Saloon::fake([]);

    expect($greeting)
        ->sending_at->not->toBeEmpty()
        ->sent_at->not->toBeEmpty();

    SendGreetingToEmailServiceJob::dispatch($greeting);

    Saloon::assertNothingSent();

    expect($greeting->refresh())
        ->metadata->not->toHaveKey('email-service.status', 'sent')
        ->metadata->not->toHaveKey('email-service.sentTime');
});

it('should not send to email service given birthday was changed', function () {
    //
    $user = User::factory()->create(['birthdate' => today('UTC'), 'timezone' => 'UTC']);
    $greeting = Greeting::factory()->withUser($user)->create([]);
    $user->update(['birthdate' => today('UTC')->addMonth()]);

    Saloon::fake([]);

    expect($greeting)
        ->sending_at->toBeEmpty()
        ->sent_at->toBeEmpty();

    SendGreetingToEmailServiceJob::dispatch($greeting);

    Saloon::assertNothingSent();

    expect($greeting->refresh())
        ->sending_at->toBeEmpty()
        ->sent_at->toBeEmpty()
        ->metadata->not->toHaveKey('email-service.status', 'sent')
        ->metadata->not->toHaveKey('email-service.sentTime');
});

it('should retry sending to email service given the server returns an error', function () {
    //
    $user = User::factory()->create(['birthdate' => today('UTC'), 'timezone' => 'UTC']);
    $greeting = Greeting::factory()->withUser($user)->create([]);

    Saloon::fake([
        MockResponse::make('', Response::HTTP_INTERNAL_SERVER_ERROR),
        MockResponse::make()->throw(
            fn ($guzzleRequest) => new ConnectException('Timeout!', $guzzleRequest)
        ),
        MockResponse::make([
            'status' => 'sent',
            'sentTime' => now()->toISOString(),
        ], Response::HTTP_OK),
    ]);

    expect($greeting)
        ->sending_at->toBeEmpty()
        ->sent_at->toBeEmpty();

    // TODO: automate auto-try on failed job
    //      problem: in test, laravel queue work on sync

    try {
        // 1st job try
        SendGreetingToEmailServiceJob::dispatch($greeting);
        //
    } catch(Throwable $e) {
        //
        expect(Greeting::sending()->count())->toBe(1);

        expect($e::class)->toBe(Exception::class);
        expect($e->getCode())->toBe(1917);
        expect($e->getMessage())->toContain('Email service not returning correct response.');

        expect($greeting->refresh())
            ->sending_at->not->toBeEmpty()
            ->sent_at->toBeEmpty();

        try {
            // 2nd job try
            SendGreetingToEmailServiceJob::dispatch($greeting);
            //
        } catch(Throwable $e) {
            //
            expect(Greeting::sending()->count())->toBe(1);

            expect($e::class)->toBe(ConnectException::class);
            expect($e->getMessage())->toContain('Timeout!');

            expect($greeting->refresh())
                ->sending_at->not->toBeEmpty()
                ->sent_at->toBeEmpty();

            SendGreetingToEmailServiceJob::dispatch($greeting);

            Saloon::assertSentCount(2); // timeout is not equal to sent

            expect($greeting->refresh())
                ->sending_at->not->toBeEmpty()
                ->sent_at->not->toBeEmpty()
                ->metadata->toHaveKey('email-service.status', 'sent')
                ->metadata->toHaveKey('email-service.sentTime');
        }
    }
});

it('should create new greeting for next year on emailServiceJob was sent', function () {
    //
    $user = User::factory()->create(['birthdate' => today('UTC'), 'timezone' => 'UTC']);
    $greeting = Greeting::factory()->withUser($user)->create();

    Saloon::fake([MockResponse::make('null')]);

    SendGreetingToEmailServiceJob::dispatch($greeting);

    Saloon::assertSentCount(1);

    expect($greeting->refresh())
        ->sending_at->not->toBeEmpty()
        ->sent_at->not->toBeEmpty();

    expect(Greeting::latest('id')->first())
        ->user_id->toBe($greeting->user_id)
        ->type->toBe($greeting->type)
        ->for_date->format('Y-m-d')
            ->toBe($greeting->for_date->addYear()->format('Y-m-d'))
        ->available_at->format('Y-m-d H:i:s')
            ->toBe($greeting->available_at->addYear()->format('Y-m-d H:i:s'))
        ->sending_at->toBeEmpty()
        ->sent_at->toBeEmpty();
});
