<?php

use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\AssessmentResultController;
use Modules\Admin\Http\Controllers\CustomerController;
use Modules\Admin\Http\Controllers\DashboardController;
use Modules\Admin\Http\Controllers\PasswordResetController;
use Modules\Admin\Http\Controllers\Settings\ProfileController;
use Modules\Admin\Http\Controllers\Settings\SecurityController;
use Modules\Admin\Http\Middleware\EnsureTwoFactorEnabled;

// Didaftarkan oleh AdminServiceProvider (middleware `web`, prefix `admin`, nama `admin.`).
// Login, logout, tantangan 2FA, konfirmasi sandi & endpoint 2FA: route Fortify (config/fortify.php, prefix `admin`).

// Lupa sandi (broker `admins`, pesan sama untuk email terdaftar/tidak).
Route::middleware('guest:admin')->group(function () {
    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:admin-password')->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:admin-password')->name('password.store');
});

// `auth.session`: session lain keluar setelah sandi diganti (reset atau halaman Keamanan).
Route::middleware(['auth:admin', 'auth.session'])->group(function () {
    // Boleh dibuka sebelum 2FA aktif (untuk mengaktifkannya).
    Route::get('settings/security', [SecurityController::class, 'edit'])->middleware(RequirePassword::class)->name('security.edit');
    Route::put('settings/password', [SecurityController::class, 'update'])->middleware('throttle:6,1')->name('password.update');

    Route::middleware(EnsureTwoFactorEnabled::class)->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::middleware('can:viewCustomers')->group(function () {
            Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
            Route::get('assessments', AssessmentResultController::class)->name('assessments.index');
        });

        Route::get('admins', AdminController::class)->middleware('can:viewAdmins')->name('admins.index');

        Route::redirect('settings', '/admin/settings/profile')->name('settings');
        Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});
