<?php

namespace Sensy\Scrud\Providers;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Sensy\Scrud\app\Middleware\ImpersonatorMiddleware;
use Sensy\Scrud\Commands\CreateUser;
use Sensy\Scrud\Commands\CrudScaffold;
use Sensy\Scrud\Commands\Deploy;
use Sensy\Scrud\Commands\Extractor;
use Sensy\Scrud\Commands\Install;
use Sensy\Scrud\Commands\ModuleScaffold;

class ScrudServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(ViewServiceProvider::class);
        //helper

        //        $this->app->singleton('helper', function ($app) {
        //            return require '../app/Http/Helpers/helper.php';
        //        });

        //        #config
        //        $this->app->singleton('config', function ($app) {
        //            return require __DIR__ . '/../config/scrud.php';
        //        });

        // Paginate with bootstrap instead o tailwind
        \Illuminate\Pagination\Paginator::useBootstrap();
        Blade::component('scrud::guest-layout', \Sensy\Scrud\View\Components\GuestLayout::class);
        Blade::component('scrud::admin-layout', \Sensy\Scrud\View\Components\AdminLayout::class);

    }

    public function map()
    {
        Route::middleware('impersonate')->group(function (Router $router) {
            $router->impersonate();
        });

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        AboutCommand::add('Scrud by Sensy', fn () => ['Version' => '1.0.5']);

        //Middlewares
        $this->app['router']->aliasMiddleware('impersonate', ImpersonatorMiddleware::class);

        // Loading routes
        if (file_exists(base_path('routes/scrud.php'))) {
            $this->loadRoutesFrom(base_path('routes/scrud.php'));
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/scrud.php');

        // Loading views
        if (file_exists(base_path('resources/views/scrud'))) {
            // Components
            $this->loadViewsFrom(base_path('resources/views/scrud'), 'scrud');
            Blade::componentNamespace('App\\Scrud\\View', 'scrud');
        } else {
            // Components
            Blade::componentNamespace('Sensy\\Scrud\\View\\Components', 'scrud');
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'scrud');
        }

        // Loading migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Loading Configs
        //####

        $layout_path = app_path('/Scrud/View');

        // Loading assets(Publishable assets)
        $this->loadAssets($layout_path);

        // Loading Commands
        $this->commands([
            CrudScaffold::class,
            CreateUser::class,
            Deploy::class,
            Extractor::class,
            ModuleScaffold::class,
            Install::class,
        ]);
    }

    public function loadAssets($layout_path)
    {

        $this->publishes([__DIR__.'/../public' => public_path('/')], 'scrud');
        $this->publishes([__DIR__.'/../routes/scrud.php' => base_path('/routes/scrud.php')], 'scrud');
        if (! file_exists(base_path('/resources/views/scrud/'))) {
            $this->publishes([__DIR__.'/../resources/views' => base_path('/resources/views/scrud/')], 'scrud');
        }
        $this->publishes([__DIR__.'/../View/Components' => $layout_path], 'scrud');

        //        foreach (glob(__DIR__ . '/../View/Components/*.php') as $file) {
        //            //get file
        //            # open file
        //            $file_content = file_get_contents($file);
        //            //replace namespace
        //            $file_content = str_replace('Sensy\Scrud\View\Components', 'App\Scrud\View', $file_content);
        //            //save file
        //            file_put_contents($layout_path . '/' . basename($file), $file_content);
        //        }

        //        echo "Overriding Auth Views\n";
        //delete the auth views

        $this->publishes([__DIR__.'/../resources/views/auth' => resource_path('views/auth')], 'scrud');

        // $this->publishes([
        //     __DIR__ . '/../config/scrud.php' => config_path('scrud.php'),
        // ], 'scrud');
    }

    public function deleteDirectory($dir)
    {
        if (! file_exists($dir)) {
            return true;
        }

        if (! is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (! $this->deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) {
                return false;
            }

        }

        return rmdir($dir);
    }
}
