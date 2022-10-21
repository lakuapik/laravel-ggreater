<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        TestResponse::macro('assertApiResponseNotFound', function () {
            /** @var TestResponse $this */
            $this->assertStatus(Response::HTTP_NOT_FOUND)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('message', '404 Not Found.');
        });

        return $app;
    }
}
