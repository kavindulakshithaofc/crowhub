<?php

namespace App\Observers;

use App\Models\Client;
use App\Services\Sms\SmsAutomation;

class ClientObserver
{
    public function __construct(protected SmsAutomation $automation)
    {
    }

    public function created(Client $client): void
    {
        $this->automation->sendProjectScheduled($client);
    }

    public function updated(Client $client): void
    {
        if ($client->wasChanged('status') && $client->status === 'completed') {
            $this->automation->sendSatisfactionCheck($client);
        }
    }
}
