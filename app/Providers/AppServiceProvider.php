<?php

namespace App\Providers;

use App\Models\ContractProgressItem;
use App\Observers\ContractProgressItemObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ContractProgressItem::observe(ContractProgressItemObserver::class);
    }
}