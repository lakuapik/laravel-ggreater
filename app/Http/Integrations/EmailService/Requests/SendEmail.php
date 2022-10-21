<?php

namespace App\Http\Integrations\EmailService\Requests;

use App\Http\Integrations\EmailService\Connector;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Traits\Plugins\HasJsonBody;

/**
 * @see https://docs.saloon.dev/the-basics/requests
 */
class SendEmail extends SaloonRequest
{
    use HasJsonBody;

    protected ?string $connector = Connector::class;

    protected ?string $method = Saloon::POST;

    public function __construct(
        public string $email,
        public string $message,
    ) {
        // pass
    }

    public function boot(SaloonRequest $request): void
    {
        $request->mergeHeaders(['Content-Type' => 'application/json']);
    }

    public function defineEndpoint(): string
    {
        return '/send-email';
    }

    public function defaultData(): array
    {
        return [
            'email' => $this->email,
            'message' => $this->message,
        ];
    }
}
