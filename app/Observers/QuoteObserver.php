<?php

namespace App\Observers;

use App\Models\Quote;
use App\Services\QuoteCalculator;
use App\Services\QuoteNumberGenerator;
use App\Services\Sms\SmsAutomation;

class QuoteObserver
{
    public function __construct(
        protected QuoteNumberGenerator $numberGenerator,
        protected QuoteCalculator $calculator,
        protected SmsAutomation $smsAutomation,
    ) {
    }

    public function creating(Quote $quote): void
    {
        if (empty($quote->quote_no)) {
            $quote->quote_no = $this->numberGenerator->generate();
        }
    }

    public function saved(Quote $quote): void
    {
        $statusChanged = $quote->wasChanged('status');
        $shouldSendForNewRecord = $quote->wasRecentlyCreated && in_array($quote->status, ['sent', 'accepted', 'rejected'], true);

        $this->calculator->refreshTotals($quote);

        if ($statusChanged) {
            $this->smsAutomation->sendQuoteStatusMessage($quote, $quote->status);
        } elseif ($shouldSendForNewRecord) {
            $this->smsAutomation->sendQuoteStatusMessage($quote, $quote->status);
        }
    }
}
