<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\TopicController;
use App\Http\Controllers\Api\Admin\QuestionController;
use App\Http\Controllers\Api\Student\QuizController;

// Admin endpoints (protected with Sanctum token)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Topics
    Route::get('/topics', [TopicController::class, 'index']);
    Route::post('/topics', [TopicController::class, 'store']);
    Route::get('/topics/{id}', [TopicController::class, 'show']);
    Route::put('/topics/{id}', [TopicController::class, 'update']);
    Route::delete('/topics/{id}', [TopicController::class, 'destroy']);
    Route::post('/topics/{id}/generate-qr', [TopicController::class, 'generateQr']);

    // Questions
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);
    
    // Users
    Route::get('/users', [\App\Http\Controllers\Api\Admin\UserController::class, 'index']);
    Route::post('/users/{id}/ban', [\App\Http\Controllers\Api\Admin\UserController::class, 'ban']);
    Route::post('/users/{id}/unban', [\App\Http\Controllers\Api\Admin\UserController::class, 'unban']);
});

// Public endpoints
Route::get('/public/topics', [TopicController::class, 'publicIndex']);

// Student endpoints (public - no authentication required for taking quiz)
Route::get('/quiz/{topic_id}', [QuizController::class, 'show']);
Route::post('/quiz/{topic_id}/submit', [QuizController::class, 'submit']);

