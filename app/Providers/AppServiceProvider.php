<?php

namespace App\Providers;

use App\Services\JwtService;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // resolver for datetime interface
        $this->app->when(JwtService::class)
            ->needs(DateTimeInterface::class)
            ->give(function () {
                return new DateTimeImmutable();
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
