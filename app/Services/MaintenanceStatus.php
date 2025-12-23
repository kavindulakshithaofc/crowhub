<?php

namespace App\Services;

use App\Models\MaintenanceContract;
use Carbon\Carbon;

class MaintenanceStatus
{
    public function forContract(MaintenanceContract $contract): array
    {
        $today = now()->startOfDay();
        $billingDay = max(1, (int) $contract->billing_day);

        $period = $today->copy()->startOfMonth();
        $startMonth = $contract->start_date
            ? Carbon::parse($contract->start_date)->startOfMonth()
            : $today->copy()->startOfMonth();

        if ($startMonth->gt($period)) {
            $period = $startMonth->copy();
        }

        if ($this->targetDateForPeriod($period, $billingDay)->lt($today)) {
            $period = $period->copy()->addMonthNoOverflow()->startOfMonth();
        }

        $period = $this->skipPaidPeriods($contract, $period, $billingDay);

        $nextDueDate = $this->targetDateForPeriod($period, $billingDay);
        $hasPayment = $this->hasPaymentForPeriod($contract, $period);

        $isOverdue = $nextDueDate->lt($today) && ! $hasPayment;
        $isDueSoon = ! $hasPayment && ! $isOverdue && $nextDueDate->lte($today->copy()->addDays(7));

        return [
            'next_due_date' => $nextDueDate,
            'is_overdue' => $isOverdue,
            'is_due_soon' => $isDueSoon,
            'is_paid' => $hasPayment,
            'period_start' => $period->copy(),
        ];
    }

    protected function targetDateForPeriod(Carbon $period, int $billingDay): Carbon
    {
        $daysInMonth = $period->daysInMonth;
        $day = min($billingDay, $daysInMonth);

        return $period->copy()->day($day);
    }

    protected function hasPaymentForPeriod(MaintenanceContract $contract, Carbon $period): bool
    {
        if ($contract->relationLoaded('payments')) {
            return $contract->payments
                ->contains(fn ($payment) => $payment->for_month?->isSameDay($period));
        }

        return $contract->payments()
            ->whereDate('for_month', $period->toDateString())
            ->exists();
    }

    protected function skipPaidPeriods(MaintenanceContract $contract, Carbon $period, int $billingDay): Carbon
    {
        $iterations = 0;

        while ($this->hasPaymentForPeriod($contract, $period) && $iterations < 24) {
            $period = $period->copy()->addMonthNoOverflow()->startOfMonth();
            $iterations++;
        }

        return $period;
    }
}
