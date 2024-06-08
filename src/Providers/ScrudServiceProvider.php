<?php

namespace Sensy\Scrud\Providers;

use App\Models\Menu;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Sensy\Scrud\Commands\CreateUser;
use Sensy\Scrud\Commands\CrudScafold;
use Sensy\Scrud\Commands\Deploy;
use Sensy\Scrud\Commands\Extractor;
use Sensy\Scrud\Commands\Install;
use Sensy\Scrud\Commands\ModuleScaffold;

class ScrudServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register() : void
    {
        $this->app->register(ViewServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot() : void
    {
        AboutCommand::add('Scrud by Sensy', fn () => ['Version' => '1.0.5']);


        # Loading routes
        if (file_exists(base_path('routes/scrud.php')))
            $this->loadRoutesFrom(base_path('routes/scrud.php'));
        $this->loadRoutesFrom(__DIR__ . '/../routes/scrud.php');

        # Loading views
        if (file_exists(base_path('resources/views/scrud'))) {
            # Components

            $this->loadViewsFrom(base_path('resources/views/scrud'), 'scrud');
            Blade::componentNamespace('App\\Scrud\\View', 'scrud');
        } else {
            # Components
            Blade::componentNamespace('Sensy\\Scrud\\View\\Components', 'scrud');
            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'scrud');
        }

        # Loading migrations
        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $layout_path = app_path('/Scrud/View/AdminLayout.php');

        # Loading assets(Publishable assets)
        $this->loadAssets($layout_path);

        # Loading Commands
        $this->commands([
            CrudScafold::class,
            CreateUser::class,
            Deploy::class,
            Extractor::class,
            ModuleScaffold::class,
            Install::class
        ]);
    }

    public function loadAssets($layout_path)
    {

        $this->publishes([__DIR__ . '/../public' => public_path('/')], 'scrud');
        $this->publishes([__DIR__ . '/../routes/scrud.php' => base_path('/routes/scrud.php')], 'scrud');
        if (! file_exists(base_path('/resources/views/scrud/')))
            $this->publishes([__DIR__ . '/../resources/views' => base_path('/resources/views/scrud/')], 'scrud');
        $this->publishes([__DIR__ . '/../View/Components/AdminLayout.php' => $layout_path], 'scrud');

        // $this->publishes([
        //     __DIR__ . '/../config/scrud.php' => config_path('scrud.php'),
        // ], 'scrud');
    }
}
