<?php

use App\Http\Controllers\ConfigController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Public routes

Route::get('/config',[ConfigController::class,'index']);

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/quiz/{topic_id}', function () {
    return view('quiz');
})->name('quiz');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Logout route (accessible when authenticated)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth:web');

// Admin routes (protected)
Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin');
    })->name('admin');
});
