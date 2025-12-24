<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\Quote;
use App\Services\Sms\SmsAutomation;
use Illuminate\Console\Command;

class RunSmsAutomations extends Command
{
    protected $signature = 'sms:run-automations';

    protected $description = 'Trigger scheduled SMS automations for quotes and payments.';

    public function handle(SmsAutomation $automation): int
    {
        $this->remindExpiringQuotes($automation);
        $this->followUpExpiredQuotes($automation);
        $this->remindOutstandingPayments($automation);

        $this->info('SMS automations processed successfully.');

        return self::SUCCESS;
    }

    protected function remindExpiringQuotes(SmsAutomation $automation): void
    {
        $days = (int) config('sms.reminders.quote_expiring_days', 2);

        if ($days <= 0) {
            return;
        }

        $now = now();
        $end = $now->copy()->addDays($days);

        Quote::query()
            ->where('status', 'sent')
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '>=', $now->toDateString())
            ->whereDate('valid_until', '<=', $end->toDateString())
            ->orderBy('id')
            ->chunkById(100, function ($quotes) use ($automation): void {
                foreach ($quotes as $quote) {
                    $automation->sendQuoteExpiring($quote);
                }
            });
    }

    protected function followUpExpiredQuotes(SmsAutomation $automation): void
    {
        $now = now();

        Quote::query()
            ->where('status', 'sent')
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', $now->toDateString())
            ->orderBy('id')
            ->chunkById(100, function ($quotes) use ($automation): void {
                foreach ($quotes as $quote) {
                    $automation->sendQuoteExpired($quote);
                }
            });
    }

    protected function remindOutstandingPayments(SmsAutomation $automation): void
    {
        Lead::query()
            ->whereNotNull('phone')
            ->whereHas('quotes', function ($query): void {
                $query->whereIn('status', ['sent', 'accepted']);
            })
            ->orderBy('id')
            ->chunkById(100, function ($leads) use ($automation): void {
                foreach ($leads as $lead) {
                    $automation->sendPaymentReminder($lead);
                }
            });
    }
}
