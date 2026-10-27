<?php

use App\Http\Controllers\PastebinController;
use App\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Short urls: shared controller and route names, but no prefix — the
// short-link URL must stay at the root (/{code}) like a real shortener.
Route::controller(ShortUrlController::class)
    ->name('short-urls.')
    ->group(function () {
        Route::post('/short-urls', 'store')->name('store');

        // The catch-all short-link route last inside its group so it
        // never shadows other routes.
        Route::get('/{shortUrl}', 'show')
            ->where('shortUrl', '[a-zA-Z0-9]{8}')
            ->name('show');
    });

Route::controller(PastebinController::class)
    ->prefix('pastebins')
    ->name('pastebins.')
    ->group(function () {
        Route::post('/', 'store')->name('store');
        Route::get('/{paste}', 'show')
            ->where('paste', '[a-zA-Z0-9]{8}')
            ->name('show');
        Route::delete('/{paste}', 'destroy')
            ->where('paste', '[a-zA-Z0-9]{8}')
            ->name('destroy');
    });
