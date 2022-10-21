<?php

use Illuminate\Http\Response;
use function Pest\Laravel\getJson;

uses(\Tests\TestCase::class);

it('success showing app version', function () {
    //
    getJson(route('api.version'))
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.version', config('app.version'));
});
