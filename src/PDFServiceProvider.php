<?php

namespace Leemarkwood\PDF;

use Illuminate\Support\ServiceProvider;

class PDFServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/pdf.php', 'pdf');

        $this->app->singleton('pdf', function ($app) {
            return new PDFGenerator($app['config']['pdf']);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/pdf.php' => config_path('pdf.php'),
        ], 'config');
    }
}
