<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\Quote;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $newLeads = Lead::where('status', 'new')->count();

        $unquoted = Lead::whereIn('status', ['new', 'contacted'])
            ->doesntHave('quotes')
            ->count();

        $totalQuoted = (float) Quote::whereIn('status', ['sent', 'accepted'])->sum('total');
        $totalPaid = (float) Payment::sum('amount');
        $pending = max($totalQuoted - $totalPaid, 0);

        return [
            Stat::make('New leads', number_format($newLeads))
                ->description('Awaiting first touch')
                ->icon('heroicon-o-user-plus'),
            Stat::make('Unquoted leads', number_format($unquoted))
                ->description('Need quotes')
                ->icon('heroicon-o-envelope'),
            Stat::make('Pending amount', '$' . number_format($pending, 2))
                ->description('Outstanding on sent quotes')
                ->color('warning')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
