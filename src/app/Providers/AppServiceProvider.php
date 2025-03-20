<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AuthService;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\DatabaseService;
use App\Services\Interfaces\DatabaseServiceInterface;
use App\Services\Interfaces\PredictionScoringServiceInterface;
use App\Services\PredictionScoringService;
use App\Services\Interfaces\ResponseFormatterInterface;
use App\Services\ResponseFormatter;
use App\Services\Interfaces\SearchServiceInterface;
use App\Services\SearchService;
use App\Services\Interfaces\StockDataServiceInterface;
use App\Services\StockDataService;
use App\Services\Interfaces\ValidationServiceInterface;
use App\Services\ValidationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(DatabaseServiceInterface::class, DatabaseService::class);
        $this->app->bind(PredictionScoringServiceInterface::class, PredictionScoringService::class);
        $this->app->bind(ResponseFormatterInterface::class, ResponseFormatter::class);
        $this->app->bind(SearchServiceInterface::class, SearchService::class);
        $this->app->bind(StockDataServiceInterface::class, StockDataService::class);
        $this->app->bind(ValidationServiceInterface::class, ValidationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
