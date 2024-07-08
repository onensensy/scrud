<?php

namespace Sensy\Scrud\Providers;

use App\Models\Menu;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\SystemModule;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // View::composer(
        //   '*',
        // function ($view) {


        //    ]);
        //  }
        //);

        ## Load Backend Sidebar Menus
        View::composer(
            ['scrud::components.backend.layouts.sidebar'],
            function ($view) {
                #Loop modules
                #Get menus
                $menus = Menu::whereHas('systemModule', function ($query) {
                    $query->where('is_active', true);
                })->get();

                $view->with('menus', $menus);
            }
        );
    }
}
