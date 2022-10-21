<?php

namespace App\Http\Integrations\EmailService;

use Illuminate\Support\Facades\App;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\Plugins\AcceptsJson;

/**
 * @see https://docs.saloon.dev/the-basics/connectors
 */
class Connector extends SaloonConnector
{
    use AcceptsJson;

    public function defineBaseUrl(): string
    {
        return config('services.email-service.url');
    }

    public function defaultHeaders(): array
    {
        $headers = [
            'User-Agent' => config('app.name').' via Saloon',
            'X-APP-VERSION' => config('app.version'),
        ];

        if (App::runningUnitTests()) {
            $headers['X-APP-FROM-TESTING'] = true;
        }

        return $headers;
    }

    public function defaultConfig(): array
    {
        return [
            'timeout' => config('services.email-service.timeout'),
        ];
    }
}
