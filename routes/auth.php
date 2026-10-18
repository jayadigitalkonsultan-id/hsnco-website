<?php

use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/activate', [ActivationController::class, 'create'])->name('activation.create');
    Route::post('/activate', [ActivationController::class, 'send'])->name('activation.send');
    Route::get('/activate/{token}', [ActivationController::class, 'edit'])->where('token', '[A-Za-z0-9]+')->name('activation.edit');
    Route::post('/activate/{token}', [ActivationController::class, 'update'])->where('token', '[A-Za-z0-9]+')->name('activation.update');
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/verify-email-otp', [TwoFactorController::class, 'create'])->name('two-factor.challenge');
    Route::post('/verify-email-otp', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/verify-email-otp/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend');
    Route::get('/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('two-factor.recovery-codes');
});
