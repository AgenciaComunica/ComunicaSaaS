<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::get('/uploads/create', [UploadController::class, 'create'])->name('uploads.create');
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
    Route::delete('/uploads/{batch}', [UploadController::class, 'destroy'])->name('uploads.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{batch}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{batch}/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/reports/{batch}/remarketing.csv', [ReportController::class, 'remarketingCsv'])->name('reports.remarketing');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users', [UsersController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [UsersController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UsersController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UsersController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UsersController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/deactivate', [UsersController::class, 'deactivate'])->name('admin.users.deactivate');
});

require __DIR__.'/auth.php';
