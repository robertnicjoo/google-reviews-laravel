<?php

use Illuminate\Support\Facades\Route;
use Nicxonsolutions\GoogleReviews\Http\Controllers\GoogleReviewsController;

Route::prefix('nicxon-google-reviews')
    ->name('google-reviews.')
    ->group(function () {
        Route::get('/assets/widget.css', [GoogleReviewsController::class, 'css'])->name('assets.css');
        Route::get('/data/{source?}', [GoogleReviewsController::class, 'show'])->where('source', '.*')->name('data');
    });
