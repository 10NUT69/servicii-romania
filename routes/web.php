<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// HOME
Route::get('/', [ServiceController::class, 'index'])->name('services.index');

// FORMULAR ADĂUGARE
Route::get('/adauga-anunt', [ServiceController::class, 'create'])->name('services.create');
Route::post('/adauga-anunt', [ServiceController::class, 'store'])->name('services.store');

// CONTUL MEU
Route::get('/contul-meu', function () {
    return view('account.index');
})->middleware('auth')->name('account.index');

// AJAX Update Profil
Route::post('/profile/ajax-update', [ProfileController::class, 'ajaxUpdate'])
    ->middleware('auth')
    ->name('profile.ajaxUpdate');


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/anunt/{id}/edit', [ServiceController::class, 'edit'])
        ->where('id', '[0-9]+')
        ->name('services.edit');

    Route::put('/anunt/{id}', [ServiceController::class, 'update'])
        ->where('id', '[0-9]+')
        ->name('services.update');

    Route::delete('/anunt/{id}', [ServiceController::class, 'destroy'])
        ->where('id', '[0-9]+')
        ->name('services.destroy');

    Route::post('/anunt/{id}', [ServiceController::class, 'renew'])
        ->where('id', '[0-9]+')
        ->name('services.renew');

    Route::post('/favorite/toggle', [FavoriteController::class, 'toggle'])
        ->name('favorite.toggle');
});


/*
|--------------------------------------------------------------------------
| SHOW – last!
|--------------------------------------------------------------------------
*/
Route::get('/anunt/{id}/{slug}', [ServiceController::class, 'show'])
    ->where('id', '[0-9]+')
    ->name('services.show');


/*
|--------------------------------------------------------------------------
| ADMIN PANEL (DOAR auth)
|--------------------------------------------------------------------------
| Nu folosim middleware is_admin momentan.
*/
Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // DASHBOARD
        Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | ADMIN USERS (REAL ROUTES)
        |--------------------------------------------------------------------------
        */
        Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])
            ->name('users.index');

        Route::post('/users/{id}/toggle', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggle'])
            ->name('users.toggle');

        Route::delete('/users/{id}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])
            ->name('users.destroy');

        /*
        |--------------------------------------------------------------------------
        | ADMIN SERVICES
        |--------------------------------------------------------------------------
        */
        Route::get('/services', [\App\Http\Controllers\Admin\AdminServiceController::class, 'index'])
            ->name('services.index');

        Route::delete('/services/{id}', [\App\Http\Controllers\Admin\AdminServiceController::class, 'destroy'])
            ->name('services.destroy');

        Route::post('/services/{id}/toggle', [\App\Http\Controllers\Admin\AdminServiceController::class, 'toggle'])
            ->name('services.toggle');

        Route::post('/services/bulk', [\App\Http\Controllers\Admin\AdminServiceController::class, 'bulkAction'])
            ->name('services.bulk');

        /*
        |--------------------------------------------------------------------------
        | ADMIN CATEGORIES (nou - înlocuiește temporarul)
        |--------------------------------------------------------------------------
        */
        Route::get('/categories', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'index'])
            ->name('categories.index');

        Route::get('/categories/create', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'create'])
            ->name('categories.create');

        Route::post('/categories', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'store'])
            ->name('categories.store');

        Route::get('/categories/{id}/edit', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'edit'])
            ->name('categories.edit');

        Route::put('/categories/{id}', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'update'])
            ->name('categories.update');

        Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'destroy'])
            ->name('categories.destroy');

        
        /*
        |--------------------------------------------------------------------------
        | TEMP – Counties (rămâne temporar până îl facem)
        |--------------------------------------------------------------------------
        */
        Route::get('/counties', fn() => 'counties page')->name('counties.index');
    });


/*
|--------------------------------------------------------------------------
| AUTH (Laravel Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
