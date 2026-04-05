<?php

namespace App\Providers;

use App\Repositories\Contracts\ListingRepositoryInterface;
use App\Repositories\Eloquent\ListingRepository;
use App\Services\Payments\GenieGateway;
use App\Services\Payments\KokoPayGateway;
use App\Services\Payments\MintPayGateway;
use App\Services\Payments\PaymentService;
use App\Services\Payments\WebXPayGateway;
use App\Services\VerticalPolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ListingRepositoryInterface::class, ListingRepository::class);
        $this->app->singleton(VerticalPolicy::class);

        $this->app->singleton(PaymentService::class, function () {
            return new PaymentService([
                'webxpay' => new WebXPayGateway(),
                'genie' => new GenieGateway(),
                'koko_pay' => new KokoPayGateway(),
                'mint_pay' => new MintPayGateway(),
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
