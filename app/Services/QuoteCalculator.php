<?php

namespace App\Services;

use App\Models\Quote;

class QuoteCalculator
{
    public function refreshTotals(Quote $quote): Quote
    {
        $quote->loadMissing('items');

        $subtotal = 0;

        foreach ($quote->items as $item) {
            $lineTotal = round($item->qty * (float) $item->unit_price, 2);

            if ((float) $item->line_total !== $lineTotal) {
                $item->line_total = $lineTotal;
                $item->saveQuietly();
            }

            $subtotal += $lineTotal;
        }

        $discount = (float) $quote->discount;
        $total = max($subtotal - $discount, 0);

        $quote->fill([
            'subtotal' => $subtotal,
            'total' => $total,
        ])->saveQuietly();

        return $quote->fresh();
    }
}
