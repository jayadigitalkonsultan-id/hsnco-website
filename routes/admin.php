<?php

use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AccountSecurityController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('admin.access')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    foreach (config('admin.modules') as $module => $definition) {
        $parameter = $definition['parameter'];
        Route::prefix($module)->name($module.'.')->group(function () use ($module, $parameter) {
            Route::get('/', [ContentController::class, 'index'])->defaults('module', $module)->name('index');
            Route::get('/create', [ContentController::class, 'create'])->defaults('module', $module)->name('create');
            Route::post('/', [ContentController::class, 'store'])->defaults('module', $module)->name('store');
            Route::get('/{'.$parameter.'}/edit', [ContentController::class, 'edit'])->defaults('module', $module)->name('edit');
            Route::get('/{'.$parameter.'}/preview', [ContentController::class, 'showPreview'])->defaults('module', $module)->name('preview');
            Route::put('/{'.$parameter.'}', [ContentController::class, 'update'])->defaults('module', $module)->name('update');
            Route::post('/{'.$parameter.'}/publish', [ContentController::class, 'publish'])->defaults('module', $module)->name('publish');
            Route::post('/{'.$parameter.'}/archive', [ContentController::class, 'archive'])->defaults('module', $module)->name('archive');
            Route::delete('/{'.$parameter.'}', [ContentController::class, 'destroy'])->defaults('module', $module)->name('destroy');
        });
    }
    Route::post('/pages/{page}/sections', [PageSectionController::class, 'store'])->name('pages.sections.store');
    Route::put('/pages/{page}/sections/{section}', [PageSectionController::class, 'update'])->name('pages.sections.update');
    Route::delete('/pages/{page}/sections/{section}', [PageSectionController::class, 'destroy'])->name('pages.sections.destroy');
    Route::get('/media/{media}/file', [MediaController::class, 'file'])->name('media.file');
    Route::resource('media', MediaController::class)->parameters(['media' => 'media'])->except('show');
    Route::get('/enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('/enquiries/{enquiry}', [EnquiryController::class, 'show'])->name('enquiries.show');
    Route::put('/enquiries/{enquiry}', [EnquiryController::class, 'update'])->name('enquiries.update');
    Route::post('/enquiries/{enquiry}/notes', [EnquiryController::class, 'note'])->name('enquiries.notes.store');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences.update');
    Route::get('/account/security', [AccountSecurityController::class, 'show'])->name('account.security');
    Route::post('/account/security/recovery-codes', [AccountSecurityController::class, 'rotate'])->name('account.security.rotate');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
    Route::put('/users/{user}/status', [UserController::class, 'status'])->name('users.status');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
});
