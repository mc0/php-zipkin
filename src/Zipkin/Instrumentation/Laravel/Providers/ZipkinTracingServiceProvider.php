<?php
namespace Drefined\Zipkin\Instrumentation\Laravel\Providers;

use Drefined\Zipkin\Instrumentation\Laravel\Services\ZipkinTracingService;
use Illuminate\Support\ServiceProvider;

class ZipkinTracingServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->publishes([
            base_path('vendor/takatost/php-zipkin/src/Zipkin/Instrumentation/Laravel/config.php') => base_path('config/zipkin.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(base_path('vendor/takatost/php-zipkin/src/Zipkin/Instrumentation/Laravel/config.php'), 'zipkin');

        $this->app->singleton(
            ZipkinTracingService::class,
            function ($app) {
                return new ZipkinTracingService($app);
            }
        );
    }
}
