<?php

namespace App\Providers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Observers\QuoteItemObserver;
use App\Observers\QuoteObserver;
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
    }
}
