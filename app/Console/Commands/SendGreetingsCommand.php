<?php

namespace App\Console\Commands;

use App\Jobs\SendGreetingToEmailServiceJob;
use App\Models\Greeting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendGreetingsCommand extends Command
{
    protected $signature = 'app:send-greetings';

    protected $description = 'Get ready-to-be-send greetings then send to email service';

    public function handle(): int
    {
        $startTime = microtime(true);
        $startTimeFmt = Carbon::parse((string) microtime(true))->toISOString();

        $this->newLine();
        $this->info("|> [${startTimeFmt}] Start send greetings...");

        $now = now()->setSecond(0);

        $minute = $now->minute >= 30 ? 30 : 0;

        $forTime = $now->setMinute($minute)->format('Y-m-d H:i:s');

        $gQuery = Greeting::active()->with('user')->where('available_at', '<=', $forTime);

        [$greetings, $greetingsCount] = [$gQuery->get(), $gQuery->count()];

        foreach ($greetings as $greeting) {
            //
            SendGreetingToEmailServiceJob::dispatch($greeting)
                ->onQueue('greetings')->delay($greeting->available_at);
        }

        $gQuery->update(['sending_at' => now()]);
        $endTime = round(microtime(true) - $startTime, 2);
        $endTimeFmt = Carbon::parse((string) microtime(true))->toISOString();

        $this->info("|>> (for-utc-time: {$forTime}) (queued: $greetingsCount)");
        $this->info("|> [{$endTimeFmt}] ✔ OK: Took {$endTime} seconds.");
        $this->newLine();

        return Command::SUCCESS;
    }
}
