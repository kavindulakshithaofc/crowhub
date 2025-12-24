<?php

namespace App\Observers;

use App\Models\Lead;
use App\Services\Sms\SmsAutomation;

class LeadObserver
{
    public function __construct(protected SmsAutomation $automation)
    {
    }

    public function created(Lead $lead): void
    {
        $this->automation->sendLeadWelcome($lead);
    }
}
