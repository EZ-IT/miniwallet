<?php

namespace App\Providers;

use App\Services\Factories\TransferServiceFactory;
use App\Services\TransferService;
use Illuminate\Support\ServiceProvider;
use Override;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton(TransferService::class);

        $this->app->tag([
            TransferService::class,
        ], 'transfer.services');
    }

    /**
     *  Bootstrap any application services.
     *
     *  We bind the TransferServiceFactory as a singleton in the service container.
     *  This factory is responsible for resolving the appropriate transfer service implementation
     *  based on a transfer type. We inject all services tagged with 'transfer.services' into the factory.
     *
     *  This approach enables scalable, flexible handling of multiple transfer types (e.g., internal, external, etc.)
     *  without hard-coding dependencies, and allows for easy extension by simply tagging new services.
     * /
     */
    public function boot(): void
    {
        $this->app->singleton(TransferServiceFactory::class, fn ($app): TransferServiceFactory =>
            // Resolves all services tagged with 'transfer.services' and passes them to the factory
            new TransferServiceFactory($app->tagged('transfer.services')));
    }
}
