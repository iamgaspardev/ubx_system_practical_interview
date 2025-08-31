<?php

use App\Http\Controllers\BigDataController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/image', [ProfileController::class, 'updateImage'])->name('profile.image');
    Route::delete('/profile/image', [ProfileController::class, 'deleteImage'])->name('profile.image.delete');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth'])->group(function () {
    // Big Data Upload Routes
    Route::prefix('bigdata')->name('bigdata.')->group(function () {
        Route::get('/', [BigDataController::class, 'index'])->name('index');
        Route::get('/upload', [BigDataController::class, 'create'])->name('create');
        Route::post('/upload', [BigDataController::class, 'store'])->name('store');
        Route::get('/uploads', [BigDataController::class, 'uploads'])->name('uploads');
        Route::get('/upload/{upload}', [BigDataController::class, 'show'])->name('show');
        Route::get('/upload/{upload}/progress', [BigDataController::class, 'getProgress'])->name('progress');
        Route::delete('/upload/{upload}', [BigDataController::class, 'destroy'])->name('destroy');
        Route::get('/export', [BigDataController::class, 'export'])->name('export');
        Route::get('/diamond/{diamond}/details', [BigDataController::class, 'getDiamondDetails'])->name('diamond.details');
    });
});