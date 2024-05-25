<?php

namespace Sensy\Crud\Providers;

use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Sensy\Crud\Commands\CreateUser;
use Sensy\Crud\Commands\CrudScafold;
use Sensy\Crud\Commands\Deploy;
use Sensy\Crud\Commands\Extractor;
use Sensy\Crud\Commands\ModuleScaffold;

class CrudServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(ViewServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // $this->app->register(ViewServiceProvider::class);

        # Loading routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        # Loading views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'scrud');

        # Components
        Blade::componentNamespace('Sensy\\Crud\\View\\Components', 'scrud');

        # Loading migrations
        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        #Components
        $this->loadViewsFrom(__DIR__ . '/../components', 'CrudComponent');


        # Loading assets(Publishable assets)
        // php artisan vendor:publish --tag=scrud --force
        $this->loadAssets();
        // // php artisan vendor:publish --tag=inspire-config --force
        // $this->publishes([
        //     __DIR__ . '/../config/inspire.php' => config_path('inspire.php'),
        // ], 'inspire-config');

        // // php artisan vendor:publish --tag=inspire-views --force
        // $this->publishes([
        //     __DIR__ . '/../views' => resource_path('views/vendor/inspire'),
        // ], 'inspire-views');

        # Loading Commands
        $this->commands([
            CrudScafold::class,
            CreateUser::class,
            Deploy::class,
            Extractor::class,
            ModuleScaffold::class
        ]);
    }

    public function loadAssets()
    {
        $this->publishes([__DIR__ . '/../public' => public_path('/')], 'scrud');
    }
}
