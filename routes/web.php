<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-s3', function () {
    try {
        // Get S3 configuration
        $config = config('filesystems.disks.s3');
        
        // Test file operations
        $disk = Storage::disk('s3');
        $testFile = 'test-' . time() . '.txt';
        
        // Try to put a file
        $disk->put($testFile, 'Hello S3 Test');
        
        // Check if file exists
        $exists = $disk->exists($testFile);
        
        // Get file URL
        $url = $disk->url($testFile);
        
        // List all files in bucket
        $files = $disk->files();
        
        return [
            'success' => true,
            'message' => 'S3 connection successful',
            'config' => [
                'driver' => $config['driver'],
                'bucket' => $config['bucket'],
                'region' => $config['region'],
                'endpoint' => $config['endpoint'],
                'use_path_style_endpoint' => $config['use_path_style_endpoint'],
            ],
            'test_results' => [
                'file_created' => $testFile,
                'file_exists' => $exists,
                'file_url' => $url,
                'files_in_bucket' => $files
            ]
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'S3 connection failed',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
});


Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

// Debug route to check authentication
Route::get('/debug-auth', function () {
    $user = auth()->user();
    if ($user) {
        return [
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles,
            ]
        ];
    }
    return ['authenticated' => false];
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
