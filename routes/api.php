<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\LessonController;

Route::prefix('teacher')->group(function () {
    Route::post('/login', [TeacherController::class, 'login']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [TeacherController::class, 'logout']);
        Route::post('/change-password', [TeacherController::class, 'changePassword']);
        Route::get('/profile', [TeacherController::class, 'getProfile']);

        Route::get('/lessons', [LessonController::class, 'getLessons']);
        Route::post('/lessons', [LessonController::class, 'store']);
        Route::get('/lessons/{id}', [LessonController::class, 'show']);
    });
});