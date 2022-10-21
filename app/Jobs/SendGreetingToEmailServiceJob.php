<?php

namespace App\Jobs;

use App\Events\OnEmailServiceJobWasSentGreeting;
use App\Http\Integrations\EmailService\Requests\SendEmail;
use App\Models\Greeting;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Throwable;

class SendGreetingToEmailServiceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Greeting $greeting
    ) {
        // pass
    }

    public function handle(): bool
    {
        [$greeting, $user] = [$this->greeting, $this->greeting->user];

        if (filled($greeting->refresh()->sent_at)) {
            return true; // already sent, no need to send again
        }

        if (! $user->nextBirthday()->eq($greeting->for_date)) {
            return true; // birthdate was changed, no need to send
        }

        $greeting->update(['sending_at' => $greeting->sending_at ?: now()]);

        $request = new SendEmail($user->email, $greeting->message);

        $response = $request->send();

        [$status, $body] = [$response->status(), $response->body() ?: 'null'];

        if ($status != Response::HTTP_OK && $response->json('status') != 'sent') {
            throw new Exception("
                Email service not returning correct response.
                Status Code: {$status}, Response Body: {$body}.
            ", 1917);
        }

        $greeting->update([
            'sent_at' => now(),
            'metadata' => array_merge($greeting->metadata, [
                'email-service' => $response->json(),
            ]),
        ]);

        Event::dispatch(new OnEmailServiceJobWasSentGreeting($greeting));

        return true;
    }

    public function retryUntil(): Carbon
    {
        return now()->addSeconds(5);
    }

    // public function failed(Throwable $exception)
    // {
    //     // TODO: do something when the job failed
    // }
}
