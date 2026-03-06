<?php

namespace App\Providers;

use App\Models\ContractProgressItem;
use App\Observers\ContractProgressItemObserver;
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
            ContractProgressItem::observe(ContractProgressItemObserver::class);
    }
}