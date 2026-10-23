<?php

use App\Http\Controllers\Site\ActivityController;
use App\Http\Controllers\Site\ArticleController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\HeroImageController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\StaticPageController;
use Illuminate\Support\Facades\Route;

// The same addresses as the Nuxt site.
Route::get('/', HomeController::class)->name('home');

Route::get('regular-activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('regular-activities/{slug}', [ActivityController::class, 'show'])->name('activities.show');

Route::controller(StaticPageController::class)->group(function () {
    Route::get('market', 'market')->name('market');
    Route::get('repair-cafe', 'repairCafe')->name('repair-cafe');
    Route::get('booking', 'booking')->name('booking');
    Route::get('about', 'about')->name('about');
    Route::get('hall-info', 'hallInfo')->name('hall-info');
    Route::get('history', 'history')->name('history');
    Route::get('the-committee', 'committee')->name('committee');
    Route::get('terms-and-conditions', 'terms')->name('terms');
});

Route::get('articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('contact', [ContactController::class, 'show'])->name('contact');
Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');

Route::get('images/uploads/{key}', HeroImageController::class)
    ->where('key', '[0-9a-f-]{36}\.(?:jpg|png|webp|gif|avif)');

// Admin-created pages. Must stay last; their slugs can't clash with the
// routes above (`site.reserved_slugs`).
Route::get('{slug}', PageController::class)->where('slug', '[a-z0-9-]+')->name('page');
