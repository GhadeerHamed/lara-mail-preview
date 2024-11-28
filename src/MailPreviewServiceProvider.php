<?php
namespace Ghadeer\LaraMailPreview;

use Illuminate\Support\ServiceProvider;

class MailPreviewServiceProvider extends ServiceProvider
{

    public function register()
    {
    }
    public function boot()
    {
        // Load the views from the package
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'email-preview');

        // Publish config and views
        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/email-preview'),
        ], 'views');

        // Load package routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}
