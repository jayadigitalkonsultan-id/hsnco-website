<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnquirySubmissionController;
use App\Http\Controllers\PublicSeoController;

require __DIR__.'/auth.php';

Route::get('/robots.txt', [PublicSeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [PublicSeoController::class, 'sitemap'])->name('sitemap.xml');

// Frontend HSNCO: static Blade views; no database or CMS endpoint.
Route::view('/', 'frontend.home')->name('home');
Route::view('/about', 'frontend.about')->name('about');
Route::view('/practice-areas', 'frontend.practice-areas')->name('practice-areas');
Route::view('/team', 'frontend.team')->name('team');
Route::view('/insights', 'frontend.insights')->name('insights');
Route::view('/contact', 'frontend.contact')->name('contact');
Route::view('/sitemap', 'frontend.sitemap')->name('sitemap');
Route::post('/enquiries', EnquirySubmissionController::class)
    ->middleware('throttle:public-enquiries')
    ->name('enquiries.store');

require __DIR__.'/admin.php';

