<?php

namespace App\Providers;

use App\Support\UiData;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $request = request();
            $attribute = 'opplex_ui_data';

            if (! $request->attributes->has($attribute)) {
                $request->attributes->set($attribute, resolve(UiData::class)->build());
            }

            $shared = $request->attributes->get($attribute, []);
            $data = $view->getData();
            $toShare = array_diff_key($shared, $data);

            if (! empty($toShare)) {
                $view->with($toShare);
            }
        });
    }
}
