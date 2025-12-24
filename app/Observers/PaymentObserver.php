<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\Sms\SmsAutomation;

class PaymentObserver
{
    public function __construct(protected SmsAutomation $automation)
    {
    }

    public function created(Payment $payment): void
    {
        $this->automation->sendAdvanceOrProgressPayment($payment);
    }
}
