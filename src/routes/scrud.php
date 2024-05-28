<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('dashboard', function ()
{
    return redirect('/admin/dashboard');
});

Route::middleware([
    'web',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function ()
{

    // Route for admin panel
    Route::group(['prefix' => 'admin'], function ()
    {
        Route::get('dashboard', function ()
        {
            return view('scrud::pages.backend.dashboard');
        })->name('dashboard');

        Route::namespace('App\Http\Controllers')->group(function ()
        {
            Route::get('profile', 'UserController@profile')->name('profile');
            ##--GENERATED ROUTES--##

            Route::resource('roles', 'RoleController');
            Route::resource('team-invitations', 'TeamInvitationController');
            Route::resource('teams', 'TeamController');
            Route::resource('sub-menus', 'SubMenuController');
            Route::resource('menus', 'MenuController');
            Route::resource('system-modules', 'SystemModuleController');
            Route::resource('users', 'UserController');
        });
    });
});
