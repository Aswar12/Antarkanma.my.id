<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\DownloadAppController;

Route::get('/statistics', [StatisticsController::class, 'getHomeStatistics']);

// Team routes
Route::get('/team', [TeamController::class, 'getTeamMembers']);
Route::post('/team/upload-photos', [TeamController::class, 'uploadTeamPhotos']);

Route::get('/', function () {
    $statistics = app(StatisticsController::class)->getHomeStatistics();
    return view('welcome', compact('statistics'));
});

Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->middleware('guest')->name('register');

Route::get('/download-app', [DownloadAppController::class, 'show'])
    ->middleware(['auth'])
    ->name('download-app');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');
