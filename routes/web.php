<?php

use App\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/short-urls', [ShortUrlController::class, 'store'])->name('short-urls.store');

// Keep the catch-all short-link route last so it never shadows other routes.
Route::get('/{shortUrl}', [ShortUrlController::class, 'show'])
    ->where('shortUrl', '[a-zA-Z0-9]{8}')
    ->name('short-urls.show');
