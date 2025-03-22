<?php

namespace App\Observers;

use App\Models\Merchant;
use Illuminate\Support\Facades\Cache;

class MerchantObserver
{
    /**
     * Handle the Merchant "created" event.
     */
    public function created(Merchant $merchant): void
    {
        Cache::tags(['merchants'])->flush();
    }

    /**
     * Handle the Merchant "updated" event.
     */
    public function updated(Merchant $merchant): void
    {
        Cache::tags(['merchants'])->flush();
    }

    /**
     * Handle the Merchant "deleted" event.
     */
    public function deleted(Merchant $merchant): void
    {
        Cache::tags(['merchants'])->flush();
    }
}
