<?php

namespace App\Providers;

use App\Repositories\Contracts\ListingRepositoryInterface;
use App\Repositories\Eloquent\ListingRepository;
use App\Services\Payments\DialogGenieGateway;
use App\Services\Payments\PayHereGateway;
use App\Services\Payments\PaymentService;
use App\Services\Payments\WebXPayGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ListingRepositoryInterface::class, ListingRepository::class);

        $this->app->singleton(PaymentService::class, function () {
            return new PaymentService([
                'payhere' => new PayHereGateway(),
                'webxpay' => new WebXPayGateway(),
                'dialog_genie' => new DialogGenieGateway(),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
