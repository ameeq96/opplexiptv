<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Support\UiData;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            static $shared = null;

            if ($shared === null) {
                $shared = resolve(UiData::class)->build();
            }

            $data = $view->getData();
            $toShare = array_diff_key($shared, $data);

            if (!empty($toShare)) {
                $view->with($toShare);
            }
        });
    }
}
