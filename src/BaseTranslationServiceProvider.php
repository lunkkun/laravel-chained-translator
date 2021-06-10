<?php

namespace Statikbe\LaravelChainedTranslator;

use Illuminate\Translation\FileLoader;
use Illuminate\Translation\TranslationServiceProvider as LaravelTranslationServiceProvider;

class BaseTranslationServiceProvider extends LaravelTranslationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-chained-translator.php' => config_path('laravel-chained-translator.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-chained-translator.php', 'laravel-chained-translator'
        );

        parent::register();
    }

    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader.default', function ($app) {
            return new FileLoader($app['files'], $app['path.lang']);
        });

        $this->app->singleton('translation.loader.custom', function ($app) {
            return new FileLoader($app['files'], $app['chained-translator.path.lang.custom']);
        });

        //override the Laravel translation loader singleton:
        $this->app->singleton('translation.loader', function ($app) {
            $loader = new ChainLoader();
            $loader->addLoader($app['translation.loader.custom']);
            $loader->addLoader($app['translation.loader.default']);

            return $loader;
        });

        $this->app->alias('translation.loader', ChainLoader::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array_merge(parent::provides(), [
            'translation.loader.custom',
            'translation.loader.default',
            'translation.manager',
        ]);
    }
}
