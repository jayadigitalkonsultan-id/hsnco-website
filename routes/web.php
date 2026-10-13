<?php

use Illuminate\Support\Facades\Route;

// Frontend HSNCO: static Blade views; no database or CMS endpoint.
Route::view('/', 'frontend.home')->name('home');
Route::view('/about', 'frontend.about')->name('about');
Route::view('/practice-areas', 'frontend.practice-areas')->name('practice-areas');
Route::view('/team', 'frontend.team')->name('team');
Route::view('/insights', 'frontend.insights')->name('insights');
Route::view('/contact', 'frontend.contact')->name('contact');
Route::view('/sitemap', 'frontend.sitemap')->name('sitemap');

