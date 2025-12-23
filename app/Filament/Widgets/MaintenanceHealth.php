<?php

namespace App\Filament\Widgets;

use App\Models\MaintenanceContract;
use App\Services\MaintenanceStatus;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MaintenanceHealth extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $contracts = MaintenanceContract::with('payments')->get();
        $statusService = app(MaintenanceStatus::class);

        $dueSoon = 0;
        $overdue = 0;

        foreach ($contracts as $contract) {
            if ($contract->status !== 'active') {
                continue;
            }

            $status = $statusService->forContract($contract);

            if ($status['is_overdue']) {
                $overdue++;
            } elseif ($status['is_due_soon']) {
                $dueSoon++;
            }
        }

        return [
            Stat::make('Maintenance due soon', number_format($dueSoon))
                ->description('Next 7 days')
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make('Maintenance overdue', number_format($overdue))
                ->description('Past due without payment')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
