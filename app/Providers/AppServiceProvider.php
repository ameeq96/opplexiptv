<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Jenssegers\Agent\Agent;


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
        require_once app_path('Helpers/helpers.php');
        Schema::defaultStringLength(191);

        View::composer('*', function ($view) {
            $agent = app(Agent::class);

            $data = $view->getData();

            if (!array_key_exists('isMobile', $data)) {
                $view->with('isMobile', $agent->isMobile());
            }
            if (!array_key_exists('isRtl', $data)) {
                $view->with(
                    'isRtl',
                    app()->bound('locale') && method_exists(app('locale'), 'isRtl')
                        ? app('locale')->isRtl()
                        : false
                );
            }
        });
    }
}
