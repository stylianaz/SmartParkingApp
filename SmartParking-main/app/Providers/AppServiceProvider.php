<?php

namespace App\Providers;

use App\Passport\Passport;

use App\Geocoding\MatrixRequester;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Prediction\PredictionManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton(MatrixRequester::class, function($app){
            return new MatrixRequester();
        });
        $this->app->singleton(PredictionManager::class, function($app){
            return new PredictionManager();
        });


    }
}
