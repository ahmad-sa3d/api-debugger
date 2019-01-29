<?php

namespace Lanin\Laravel\ApiDebugger;

use Illuminate\Routing\Router;
use Lanin\Laravel\ApiDebugger\Middleware\RequestHandledMiddleware;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap application service.
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/api-debugger.php';
        $this->publishes([$configPath => config_path('api-debugger.php')]);
        $this->mergeConfigFrom($configPath, 'api-debugger');

        // Register collections only for debug environment.
        $config = $this->app['config'];
        if ($config['app.debug']) {
            $this->registerCollections($config['api-debugger.collections']);
        }

        // Register
        if ($this->app instanceof \Illuminate\Foundation\Application) {
            // Laravel
            /** @var \Illuminate\Foundation\Http\Kernel $kernel */
            $kernel = $this->app['Illuminate\Contracts\Http\Kernel'];//->addMiddleware(RequestHandledMiddleware::class);
            $kernel->pushMiddleware(RequestHandledMiddleware::class);
        } else {
            // Lumen
            $this->app->middleware(RequestHandledMiddleware::class);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Debugger::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Debugger::class,
        ];
    }

    /**
     * Register requested collections within debugger.
     *
     * @param Collection[] $collections
     */
    protected function registerCollections(array $collections)
    {
        $debugger = $this->app->make(Debugger::class);

        foreach ($collections as $collection) {
            $debugger->populateWith($this->app->make($collection));
        }
    }
}
