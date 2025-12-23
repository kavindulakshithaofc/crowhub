<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\QuoteCalculator;
use Illuminate\Http\JsonResponse;

class QuoteTotalsController extends Controller
{
    public function __invoke(Quote $quote, QuoteCalculator $calculator): JsonResponse
    {
        $quote = $calculator->refreshTotals($quote);

        return response()->json([
            'quote_id' => $quote->id,
            'subtotal' => $quote->subtotal,
            'discount' => $quote->discount,
            'total' => $quote->total,
        ]);
    }
}
