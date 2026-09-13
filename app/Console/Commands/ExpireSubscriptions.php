<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Mark subscriptions as expired when their end date has passed';

    public function handle(SubscriptionService $subscriptions): int
    {
        $expired = 0;

        Subscription::query()
            ->whereIn('status', ['trialing', 'active'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->chunkById(200, function ($items) use ($subscriptions, &$expired) {
                foreach ($items as $subscription) {
                    if ($subscriptions->expireIfNeeded($subscription)) {
                        $expired++;
                    }
                }
            });

        $this->info("Expired {$expired} subscription(s).");

        return self::SUCCESS;
    }
}
