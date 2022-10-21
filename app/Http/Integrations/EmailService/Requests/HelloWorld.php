<?php

namespace App\Http\Integrations\EmailService\Requests;

use App\Http\Integrations\EmailService\Connector;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;

/**
 * @see https://docs.saloon.dev/the-basics/requests
 */
class HelloWorld extends SaloonRequest
{
    protected ?string $connector = Connector::class;

    protected ?string $method = Saloon::GET;

    public function defineEndpoint(): string
    {
        return '/';
    }
}
