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

Route::get('dashboard', function () {
    return redirect('/admin/dashboard');
});

Route::middleware([
    'web',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::impersonate();

    // Route for admin panel
    Route::prefix('admin')->group(function () {
        Route::get('dashboard', function () {
            return view('scrud::pages.backend.dashboard');
        })->name('dashboard');

        Route::namespace('App\Http\Controllers')->group(function () {
            Route::get('profile', 'UserController@profile')->name('profile');
            //#--GENERATED ROUTES--##
            Route::resource('menus', 'MenuController');
            Route::resource('search-tests', 'SearchTestController');
            Route::resource('memberships', 'MembershipController');
            Route::resource('settings', 'SettingController');
            Route::resource('testings', 'TestingController');
            Route::resource('testins', 'TestinController');
            Route::resource('tests', 'TestController');

            //# IMPERSONATIONS ##
            Route::middleware(['impersonate'])->group(function () {
                Route::get('/{user}/impersonate', [\Sensy\Scrud\app\Controller\ImpersonateController::class, 'impersonate'])->name('users.impersonate');
            });
            Route::get('/leave-impersonate', [\Sensy\Scrud\app\Controller\ImpersonateController::class, 'leaveImpersonate'])->name('users.leave-impersonate');
            //# IMPERSONATIONS ##

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
