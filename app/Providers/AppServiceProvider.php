<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\SmsTemplate;
use App\Observers\ClientObserver;
use App\Observers\LeadObserver;
use App\Observers\MaintenanceContractObserver;
use App\Observers\PaymentObserver;
use App\Observers\QuoteItemObserver;
use App\Observers\QuoteObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Quote::observe(QuoteObserver::class);
        QuoteItem::observe(QuoteItemObserver::class);
        Lead::observe(LeadObserver::class);
        Payment::observe(PaymentObserver::class);
        Client::observe(ClientObserver::class);
        MaintenanceContract::observe(MaintenanceContractObserver::class);

        if (Schema::hasTable('sms_templates') && Schema::hasColumn('sms_templates', 'is_enabled')) {
            SmsTemplate::syncFromConfig();
        }
    }
}
