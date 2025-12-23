<?php

namespace App\Services;

use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class QuoteNumberGenerator
{
    public function generate(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = sprintf('CROW-%s-', $year);

            $latestQuote = Quote::whereYear('created_at', $year)
                ->lockForUpdate()
                ->orderByDesc('quote_no')
                ->first();

            $lastNumber = $latestQuote
                ? (int) substr($latestQuote->quote_no, -4)
                : 0;

            $next = $lastNumber + 1;

            return sprintf('%s%04d', $prefix, $next);
        });
    }
}
