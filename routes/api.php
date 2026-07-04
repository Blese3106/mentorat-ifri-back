<?php

use App\Http\Controllers\Api\AcademicController;
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

    Route::prefix('academic')->group(function () {
 
        Route::get('/{requestId}/dossier', [AcademicController::class, 'getDossier']);
    
        // Diagnostic
        Route::get( '/{requestId}/diagnostic', [AcademicController::class, 'getDiagnostic']);
        Route::post('/{requestId}/diagnostic', [AcademicController::class, 'saveDiagnostic']);
    
        // Objectifs
        Route::get(   '/{requestId}/objectives',  [AcademicController::class, 'getObjectives']);
        Route::post(  '/{requestId}/objectives',  [AcademicController::class, 'createObjective']);
        Route::put(   '/objectives/{id}',         [AcademicController::class, 'updateObjective']);
        Route::delete('/objectives/{id}',         [AcademicController::class, 'deleteObjective']);
    
        // Performances
        Route::get(   '/{requestId}/performances', [AcademicController::class, 'getPerformances']);
        Route::post(  '/{requestId}/performances', [AcademicController::class, 'savePerformance']);
        Route::delete('/performances/{id}',        [AcademicController::class, 'deletePerformance']);
    
        // Compétences
        Route::get(   '/{requestId}/competences', [AcademicController::class, 'getCompetences']);
        Route::post(  '/{requestId}/competences', [AcademicController::class, 'saveCompetence']);
        Route::delete('/competences/{id}',        [AcademicController::class, 'deleteCompetence']);
    
        // Tâches
        Route::get(   '/{requestId}/tasks',  [AcademicController::class, 'getTasks']);
        Route::post(  '/{requestId}/tasks',  [AcademicController::class, 'createTask']);
        Route::put(   '/tasks/{id}',         [AcademicController::class, 'updateTask']);
        Route::delete('/tasks/{id}',         [AcademicController::class, 'deleteTask']);
    
        // Difficultés
        Route::get(   '/{requestId}/difficulties', [AcademicController::class, 'getDifficulties']);
        Route::post(  '/{requestId}/difficulties', [AcademicController::class, 'createDifficulty']);
        Route::put(   '/difficulties/{id}',        [AcademicController::class, 'updateDifficulty']);
        Route::delete('/difficulties/{id}',        [AcademicController::class, 'deleteDifficulty']);
    
        
        Route::get(   '/{requestId}/reports', [AcademicController::class, 'getReports']);
        Route::post(  '/{requestId}/reports', [AcademicController::class, 'createReport']);
        Route::put(   '/reports/{id}',        [AcademicController::class, 'updateReport']);
        Route::delete('/reports/{id}',        [AcademicController::class, 'deleteReport']);
    });
 
});