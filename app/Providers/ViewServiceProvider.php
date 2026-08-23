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
        View::composer(['pages.*', 'blogs.*', 'policies.*', 'layouts.default'], function ($view) {
            $request = request();
            $shared = $request->attributes->get('_opplex_ui_data');

            if (!is_array($shared)) {
                $shared = resolve(UiData::class)->build();
                $request->attributes->set('_opplex_ui_data', $shared);
            }

            $data = $view->getData();
            $toShare = array_diff_key($shared, $data);

            if (!empty($toShare)) {
                $view->with($toShare);
            }
        });
    }
}
