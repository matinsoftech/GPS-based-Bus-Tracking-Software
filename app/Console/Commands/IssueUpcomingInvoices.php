<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\InvoiceService;
use Illuminate\Console\Command;

class IssueUpcomingInvoices extends Command
{
    protected $signature = 'invoices:issue-upcoming';

    protected $description = 'Issue unpaid invoices for the next billing period of active subscriptions expiring within two days';

    private const WITHIN_DAYS = 2;

    public function handle(InvoiceService $invoices): int
    {
        $issued = 0;
        $start = now();
        $end = $start->copy()->addDays(self::WITHIN_DAYS);

        Subscription::query()
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [$start, $end])
            ->chunkById(200, function ($items) use ($invoices, &$issued) {
                foreach ($items as $subscription) {
                    if ($invoices->findForNextPeriod($subscription) !== null) {
                        continue;
                    }

                    if ($invoices->generateForNextPeriod($subscription) !== null) {
                        $issued++;
                    }
                }
            });

        $this->info("Issued {$issued} upcoming invoice(s).");

        return self::SUCCESS;
    }
}
