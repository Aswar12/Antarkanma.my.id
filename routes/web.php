<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TeamController;

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
