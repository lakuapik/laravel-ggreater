<?php

namespace App\Events;

use App\Models\Greeting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnEmailServiceJobWasSentGreeting
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Greeting $greeting,
    ) {
        //
    }
}
