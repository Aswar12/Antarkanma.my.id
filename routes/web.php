<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatisticsController;

Route::get('/statistics', [StatisticsController::class, 'getHomeStatistics']);

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
