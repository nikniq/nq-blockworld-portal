<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\ReleaseController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SharedGameController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/download', [DownloadController::class, 'index'])->name('downloads.index');
Route::get('/download/{release}', [DownloadController::class, 'download'])->name('downloads.file');

Route::get('/games', [SharedGameController::class, 'index'])->name('games.index');
Route::get('/games/{game}', [SharedGameController::class, 'show'])->name('games.show')->where('game', '[a-z0-9-]+');
Route::get('/games/{game}/download', [SharedGameController::class, 'download'])->name('games.download');

Route::get('/players', [ProfileController::class, 'index'])->name('profile.index');
Route::get('/u/{user}', [ProfileController::class, 'show'])->name('profile.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/share', [SharedGameController::class, 'create'])->name('games.create');
    Route::post('/share', [SharedGameController::class, 'store'])->name('games.store');
    Route::get('/games/{game}/edit', [SharedGameController::class, 'edit'])->name('games.edit');
    Route::put('/games/{game}', [SharedGameController::class, 'update'])->name('games.update');
    Route::delete('/games/{game}', [SharedGameController::class, 'destroy'])->name('games.destroy');
});

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('index');
    Route::get('/players', [UserController::class, 'index'])->name('users');
    Route::post('/players/{user}/admin', [UserController::class, 'toggleAdmin'])->name('users.admin');
    Route::delete('/players/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/worlds', [GameController::class, 'index'])->name('games');
    Route::delete('/worlds/{game}', [GameController::class, 'destroy'])->name('games.destroy');
    Route::get('/releases', [ReleaseController::class, 'index'])->name('releases');
    Route::put('/releases/{release}', [ReleaseController::class, 'update'])->name('releases.update');
    Route::delete('/releases/{release}', [ReleaseController::class, 'destroy'])->name('releases.destroy');
});
