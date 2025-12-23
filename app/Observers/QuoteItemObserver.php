<?php

namespace App\Observers;

use App\Models\QuoteItem;
use App\Services\QuoteCalculator;

class QuoteItemObserver
{
    public function __construct(protected QuoteCalculator $calculator)
    {
    }

    public function saving(QuoteItem $quoteItem): void
    {
        $quoteItem->line_total = round($quoteItem->qty * (float) $quoteItem->unit_price, 2);
    }

    public function saved(QuoteItem $quoteItem): void
    {
        $this->recalculate($quoteItem);
    }

    public function deleted(QuoteItem $quoteItem): void
    {
        $this->recalculate($quoteItem);
    }

    protected function recalculate(QuoteItem $quoteItem): void
    {
        $quote = $quoteItem->quote;

        if (! $quote) {
            $quote = $quoteItem->quote()->first();
        }

        if ($quote) {
            $this->calculator->refreshTotals($quote);
        }
    }
}
