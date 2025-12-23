<?php

namespace App\Services;

use App\Models\Lead;

class LeadSummary
{
    public function forLead(Lead $lead): array
    {
        $totalQuoted = (float) $lead->quotes()
            ->whereIn('status', ['sent', 'accepted'])
            ->sum('total');

        $totalPaid = (float) $lead->payments()->sum('amount');

        $pending = max($totalQuoted - $totalPaid, 0);

        return [
            'total_quoted' => $totalQuoted,
            'total_paid' => $totalPaid,
            'pending' => $pending,
        ];
    }
}
