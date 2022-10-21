<?php

use App\Http\Integrations\EmailService\Requests\HelloWorld;
use App\Http\Integrations\EmailService\Requests\SendEmail;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Response;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\SaloonLaravel\Facades\Saloon;

uses(Tests\TestCase::class);

it('success getting hello world response', function () {
    //
    Saloon::fake([
        MockResponse::make('Hello World!', Response::HTTP_OK),
    ]);

    $response = (new HelloWorld)->send();

    Saloon::assertSent(HelloWorld::class);

    expect($response->status())->toBe(Response::HTTP_OK);
    expect($response->body())->toBe('Hello World!');
});

it('success sending email given valid data', function () {
    //
    $fakeResponse = [
        'status' => 'sent',
        'sentTime' => now()->toISOString(),
    ];

    Saloon::fake([
        MockResponse::make($fakeResponse, Response::HTTP_OK),
    ]);

    $response = (new SendEmail(fake()->email(), fake()->sentence()))->send();

    expect($response->status())->toBe(Response::HTTP_OK);
    expect($response->json())->toBe($fakeResponse);
});

it('fails sending email given incomplete request data', function () {
    //.
    $fakeResponse = json_decode('
        {"value":{},"path":"message","type":"required",
        "errors":["message is a required field"],"params":{"path":"message"},
        "inner":[],"name":"ValidationError",
        "message":"message is a required field"}
    ', true);

    Saloon::fake([
        MockResponse::make($fakeResponse, Response::HTTP_BAD_REQUEST),
    ]);

    $response = (new SendEmail('', ''))->send();

    expect($response->status())->toBe(Response::HTTP_BAD_REQUEST);
    expect($response->json())->toBe($fakeResponse);
});

it('fails sending email given 10% canche server error', function () {
    //
    Saloon::fake([
        MockResponse::make('', Response::HTTP_INTERNAL_SERVER_ERROR),
    ]);

    $response = (new SendEmail(fake()->email(), fake()->sentence()))->send();

    expect($response->status())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR);
});

it('fails sending email given 10% canche server hang', function () {
    //
    test()->expectException(ConnectException::class);
    test()->expectExceptionMessage('Timeout!');

    Saloon::fake([
        MockResponse::make()->throw(
            fn ($guzzleRequest) => new ConnectException('Timeout!', $guzzleRequest)
        ),
    ]);

    (new SendEmail(fake()->email(), fake()->sentence()))->send();
});
