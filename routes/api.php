<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EpreuveController;
use App\Http\Controllers\Api\AdminMentorController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\OffreController;
use App\Http\Controllers\Api\MentoringRequestController;
use Illuminate\Support\Facades\Route;

Route::post('/check-identifier',      [AuthController::class, 'checkIdentifier']);
Route::post('/auth/register/student', [AuthController::class, 'registerStudent']);
Route::post('/auth/register/mentor',  [AuthController::class, 'registerMentor']);
Route::post('/login',                 [AuthController::class, 'login']);
Route::post('/forgot-password',       [AuthController::class, 'forgotPassword']);
Route::post('/reset-password',        [AuthController::class, 'resetPassword']);


    Route::get('/mentors', [MentoringRequestController::class, 'mentors']);

    Route::get('/epreuves/stats',         [EpreuveController::class, 'stats']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::get('/epreuves',               [EpreuveController::class, 'index']);
    Route::get('/epreuves/{id}',          [EpreuveController::class, 'show']);
    Route::get('/epreuves/{id}/download', [EpreuveController::class, 'download']);
    Route::post('/epreuves',              [EpreuveController::class, 'store']);
    Route::put('/epreuves/{id}',          [EpreuveController::class, 'update']);
    Route::delete('/epreuves/{id}',       [EpreuveController::class, 'destroy']);

    Route::get('/offres',      [OffreController::class, 'index']);
    Route::get('/offres/{id}', [OffreController::class, 'show']);

    //Route::get('/mentors', [MentoringRequestController::class, 'mentors']);

    Route::get('/student/stats', [MentoringRequestController::class, 'studentStats']);

    Route::get('/mentoring-requests',                [MentoringRequestController::class, 'studentIndex']);
    Route::post('/mentoring-requests',               [MentoringRequestController::class, 'store']);
    Route::get('/mentoring-requests/{id}/messages',  [MentoringRequestController::class, 'messages']);
    Route::post('/mentoring-requests/{id}/messages', [MentoringRequestController::class, 'sendMessage']);
    Route::get('/mentoring-requests/{id}/sessions',  [MentoringRequestController::class, 'sessions']);

    Route::prefix('mentor')->group(function () {
        Route::get('/profile',                 [MentoringRequestController::class, 'myProfile']);
        Route::get('/requests',                [MentoringRequestController::class, 'mentorIndex']);
        Route::post('/requests/{id}/accept',   [MentoringRequestController::class, 'accept']);
        Route::post('/requests/{id}/reject',   [MentoringRequestController::class, 'reject']);
        Route::post('/requests/{id}/sessions', [MentoringRequestController::class, 'createSession']);
    });

    Route::put('/sessions/{id}', [MentoringRequestController::class, 'updateSession']);

    Route::prefix('admin')->group(function () {
        Route::get('/users',         [AdminUserController::class, 'index']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
        Route::get('/mentors',               [AdminMentorController::class, 'index']);
        Route::get('/mentors/{id}',          [AdminMentorController::class, 'show']);
        Route::post('/mentors/{id}/approve', [AdminMentorController::class, 'approve']);
        Route::post('/mentors/{id}/reject',  [AdminMentorController::class, 'reject']);
        Route::post('/offres',        [OffreController::class, 'store']);
        Route::put('/offres/{id}',    [OffreController::class, 'update']);
        Route::delete('/offres/{id}', [OffreController::class, 'destroy']);
    });
});