<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProfileController;

// ADMIN CONTROLLERS IMPORTATE CORECT
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminCategoryController;

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
Route::get('/contul-meu', fn() => view('account.index'))
    ->middleware('auth')
    ->name('account.index');

// AJAX Update Profil
Route::post('/profile/ajax-update', [ProfileController::class, 'ajaxUpdate'])
    ->middleware('auth')
    ->name('profile.ajaxUpdate');

// AJAX Check Username Availability
Route::post('/profile/check-name', [ProfileController::class, 'checkName'])
    ->middleware('auth')
    ->name('profile.checkName');


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Trebuie să fii logat)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/anunt/{id}/edit', [ServiceController::class, 'edit'])
        ->name('services.edit');

    Route::put('/anunt/{id}', [ServiceController::class, 'update'])
        ->name('services.update');

    Route::delete('/anunt/{id}', [ServiceController::class, 'destroy'])
        ->name('services.destroy');

    // DELETE IMAGE
    Route::delete('/services/{id}/image', [ServiceController::class, 'deleteImage'])
        ->name('services.deleteImage');

    // RENEW
    Route::post('/anunt/{id}', [ServiceController::class, 'renew'])
        ->name('services.renew');

    // FAVORITE
    Route::post('/favorite/toggle', [FavoriteController::class, 'toggle'])
        ->name('favorite.toggle');
});


/*
|--------------------------------------------------------------------------
| SHOW (ultimul!)
|--------------------------------------------------------------------------
*/
Route::get('/anunt/{id}/{slug}', [ServiceController::class, 'show'])
    ->name('services.show');


/*
|--------------------------------------------------------------------------
| ADMIN PANEL (SECURE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // DASHBOARD
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // USERS
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{id}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // SERVICES
        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');

        Route::delete('/services/{id}', [AdminServiceController::class, 'destroy'])
            ->name('services.destroy');

        Route::post('/services/{id}/toggle', [AdminServiceController::class, 'toggle'])
            ->name('services.toggle');

        Route::post('/services/bulk', [AdminServiceController::class, 'bulkAction'])
            ->name('services.bulk');

        // CATEGORIES
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        // COUNTIES (temporar)
        Route::get('/counties', fn() => 'counties page')->name('counties.index');
    });

require __DIR__.'/auth.php';
