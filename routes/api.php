<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SearchController;

Route::prefix('teacher')->group(function () {
    Route::post('/login', [TeacherController::class, 'login']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [TeacherController::class, 'logout']);
        Route::post('/change-password', [TeacherController::class, 'changePassword']);
        Route::get('/profile', [TeacherController::class, 'getProfile']);

        Route::get('/lessons', [LessonController::class, 'getLessons']);
        Route::post('/lessons', [LessonController::class, 'store']);
        Route::get('/lessons/{id}', [LessonController::class, 'show']);
        Route::post('/lessons/{id}', [LessonController::class, 'update']);

        Route::get('/search/fakultets', [SearchController::class, 'fakultets']);
        Route::get('/search/groups', [SearchController::class, 'groups']);
        Route::get('/search/rooms', [SearchController::class, 'rooms']);
        Route::get('/search/subjects', [SearchController::class, 'subjects']);
        Route::get('/search/paras', [SearchController::class, 'paras']);
        Route::get('/search/lesson-types', [SearchController::class, 'lessonTypes']);
    });
});