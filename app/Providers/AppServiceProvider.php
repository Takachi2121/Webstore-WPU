<?php

namespace App\Providers;

use App\Actions\ValidateCartStock;
use App\Contract\CartServiceInterface;
use App\Contract\PaymentDriverInterface;
use App\Driver\Payment\PaymentMethodQueryService;
use App\Models\User;
use App\Services\RegionQueryService;
use App\Services\SessionCartService;
use App\Services\ShippingMethodService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CartServiceInterface::class, SessionCartService::class);
        $this->app->bind(RegionQueryService::class, RegionQueryService::class);
        $this->app->bind(ShippingMethodService::class, ShippingMethodService::class);
        $this->app->bind(PaymentMethodQueryService::class, PaymentMethodQueryService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Gate::define('is_stock_available', function(?User $user = null){
            try{
                ValidateCartStock::run();
                return true;
            } catch (Exception $e) {
                session()->flash('error', $e->getMessage());
                return false;
            }
        });
    }
}
