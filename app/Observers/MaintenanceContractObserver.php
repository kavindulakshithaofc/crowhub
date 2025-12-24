<?php

namespace App\Observers;

use App\Models\MaintenanceContract;
use App\Services\Sms\SmsAutomation;

class MaintenanceContractObserver
{
    public function __construct(protected SmsAutomation $automation)
    {
    }

    public function created(MaintenanceContract $contract): void
    {
        $this->automation->sendSupportWelcome($contract);
    }
}
