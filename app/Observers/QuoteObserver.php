<?php

namespace App\Observers;

use App\Models\Quote;
use App\Services\QuoteCalculator;
use App\Services\QuoteNumberGenerator;

class QuoteObserver
{
    public function __construct(
        protected QuoteNumberGenerator $numberGenerator,
        protected QuoteCalculator $calculator,
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
        $this->calculator->refreshTotals($quote);
    }
}
