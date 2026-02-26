<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('sidebar', 'App\Http\ViewComposers\ProfileComposer');
        View::composer('sidebar', 'App\Http\ViewComposers\SidebarComposer');
        View::composer('header', 'App\Http\ViewComposers\HeaderComposer');

        // view()->composer(
        //     'profile', 'App\Http\ViewComposers\ProfileComposer'
        // );
        // view()->composer(
        //     'sidebar', 'App\Http\ViewComposers\SidebarComposer'
        // );

        // View::composer(
        //         ['sidebar' , 'App\Http\ViewComposers\SidebarComposer'],
        //         ['profile' , 'App\Http\ViewComposers\ProfileComposer']
        // );
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
