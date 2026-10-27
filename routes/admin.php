<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [AdminAuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AdminAuthController::class, 'login'])
        ->name('login.submit');

    Route::middleware('admin')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/rooms', [RoomController::class, 'index'])
            ->name('rooms');

        Route::get('/participants', [ParticipantController::class, 'index'])
            ->name('participants');

        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports');

        Route::get('/settings', [SettingsController::class, 'index'])
            ->name('settings');

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->name('logout');
    });
});