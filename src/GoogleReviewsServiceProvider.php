<?php

namespace Nicxonsolutions\GoogleReviews;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Nicxonsolutions\GoogleReviews\Api\BusinessProfileClient;
use Nicxonsolutions\GoogleReviews\Api\NicxonConnectorClient;
use Nicxonsolutions\GoogleReviews\Api\PlacesClient;
use Nicxonsolutions\GoogleReviews\View\Components\Widget;

class GoogleReviewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/google-reviews.php', 'google-reviews');

        $this->app->singleton(GoogleReviews::class, function ($app) {
            return new GoogleReviews(
                [
                    'business_profile' => new BusinessProfileClient(
                        $app['config']->get('google-reviews.business_profile', []),
                        $app['config']->get('google-reviews', []),
                        $app['cache.store']
                    ),
                    'places' => new PlacesClient(array_merge(
                        $app['config']->get('google-reviews', []),
                        $app['config']->get('google-reviews.places', [])
                    )),
                    'nicxon_connector' => new NicxonConnectorClient(
                        $app['config']->get('google-reviews.nicxon_connector', []),
                        $app['config']->get('google-reviews', [])
                    ),
                ],
                $app['cache.store'],
                $app['config']->get('google-reviews', [])
            );
        });

        $this->app->alias(GoogleReviews::class, 'google-reviews');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'google-reviews');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->publishes([
            __DIR__ . '/../config/google-reviews.php' => config_path('google-reviews.php'),
        ], 'google-reviews-config');

        $this->publishes([
            __DIR__ . '/../resources/dist/nicxon-google-reviews.css' => public_path('vendor/nicxon-google-reviews/nicxon-google-reviews.css'),
            __DIR__ . '/../resources/js' => resource_path('js/vendor/nicxon-google-reviews'),
        ], 'google-reviews-assets');

        $this->callAfterResolving(BladeCompiler::class, function (BladeCompiler $blade) {
            $blade->component(Widget::class, 'google-reviews-widget');
        });

        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('google-reviews-widget', \Nicxonsolutions\GoogleReviews\Livewire\GoogleReviewsWidget::class);
        }

        View::share('googleReviewsAssetUrl', route('google-reviews.assets.css'));
    }
}
