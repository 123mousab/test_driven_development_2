<?php

namespace App\Providers;

use App\Billing\HashidsTicketCodeGenerator;
use App\Billing\PaymentGateway;
use App\Billing\RandomOrderConfirmationNumberGenerator;
use App\Billing\StripePaymentGateway;
use App\OrderConfirmationNumberGenerator;
use App\TicketCodeGenerator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private $stripe_api_key = 'sk_test_51IFf0EA6UNYC18RsKfYotpmcK9yhm95pUiLHTL8Ushu5m2D4n8THRp5AaYam5wPmYeidStqF5LKuMmqkbPh76NZn00tUx0CV64';
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(StripePaymentGateway::class, function (){
            return new StripePaymentGateway($this->stripe_api_key);
        });

        $this->app->bind(HashidsTicketCodeGenerator::class, function () {
            return new HashidsTicketCodeGenerator(config('app.ticket_code_salt'));
        });

        $this->app->bind(PaymentGateway::class, StripePaymentGateway::class);
        $this->app->bind(OrderConfirmationNumberGenerator::class, RandomOrderConfirmationNumberGenerator::class);
        $this->app->bind(TicketCodeGenerator::class, HashidsTicketCodeGenerator::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
