<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;


// Grup rute untuk pengguna dengan middleware auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Rute untuk logout pengguna
    Route::post('/logout', [UserController::class, 'logout']);

    // Rute untuk memperbarui profil pengguna
    Route::put('/user/profile', [UserController::class, 'profileUpdate']);

    // Rute untuk mengambil data profil pengguna
    Route::get('/user/profile', [UserController::class, 'fetch']);

    // Rute untuk memperbarui foto profil pengguna
    Route::post('/user/profile/photo', [UserController::class, 'updatePhoto']);
});

// Rute untuk registrasi pengguna
Route::post('/register', [UserController::class, 'register']);

// Rute untuk login pengguna
Route::post('/login', [UserController::class, 'login']);
