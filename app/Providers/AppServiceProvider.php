<?php

namespace App\Providers;

use App\Support\OpsAlert;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();

        Queue::failing(function (JobFailed $event) {
            OpsAlert::send(
                'Queue Job Failed',
                'A background job has failed.',
                [
                    'connection' => $event->connectionName,
                    'queue' => $event->job?->getQueue(),
                    'job' => $event->job?->resolveName(),
                    'exception' => $event->exception->getMessage(),
                ]
            );
        });
    }
}
