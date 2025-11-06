<?php

use Illuminate\Support\Facades\Route;

// === Controladores ===
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Rutas API
|--------------------------------------------------------------------------
| Estructura:
| - Públicas: login/register, ping, lectura básica (subjects, topics, exercises)
| - Autenticadas (auth:sanctum): /me, logout, progreso de usuario, responder ejercicios
| - Admin (auth:sanctum + admin): CRUD de subjects/topics/exercises, users, charts, reports
|--------------------------------------------------------------------------
*/



// --- CORS preflight opcional (útil en dev SPA) ---
Route::options('/{any}', fn () => response()->noContent())->where('any', '.*');

// ===================================================
// 🔓 RUTAS PÚBLICAS
// ===================================================
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register')->name('register');
    Route::post('/login', 'login')->name('login');
    Route::post('/forgot-password', 'forgotPassword');
    Route::post('/reset-password', 'resetPassword');
});

// Lectura pública básica (como tenías antes para que el alumno vea materias/temas)
Route::get('/subjects',             [SubjectController::class, 'index']);
Route::get('/subjects/{id}',        [SubjectController::class, 'show']);
Route::get('/subjects/{subjectId}/topics', [TopicController::class, 'index']);
Route::get('/topics/{id}',          [TopicController::class, 'show']);
Route::get('/topics/{topicId}/exercises', [ExerciseController::class, 'getByTopic']);

// ===================================================
// 🔒 RUTAS PROTEGIDAS (auth:sanctum)
// ===================================================
Route::middleware(['auth:sanctum'])->group(function () {

    // --- Sesión / Perfil ---
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'me');
    });

    // --- Usuario ---
    Route::get('/lives', [UserController::class, 'getLives']);

    // --- Progreso de usuario ---
    Route::get('/subjects/user/progress', [SubjectController::class, 'getUserProgress']);

    // --- Ejercicios: responder ---
    Route::post('/exercises/{exerciseId}/answer', [ExerciseController::class, 'submitAnswer']);

    // ===================================================
    // 🧠 RUTAS DE ADMINISTRADOR (auth + admin)
    // ===================================================
    Route::middleware(['admin'])->group(function () {

        // --- Gestión de Temas ---
        Route::post('/topics',           [TopicController::class, 'store']);
        Route::put('/topics/{id}',       [TopicController::class, 'update']);
        Route::delete('/topics/{id}',    [TopicController::class, 'destroy']);

        // --- Gestión de Materias ---
        Route::post('/subjects',         [SubjectController::class, 'store']);
        Route::put('/subjects/{id}',     [SubjectController::class, 'update']);
        Route::delete('/subjects/{id}',  [SubjectController::class, 'destroy']);

        // --- Gestión de Ejercicios ---
        Route::post('/exercises',        [ExerciseController::class, 'store']);
        Route::put('/exercises/{id}',    [ExerciseController::class, 'update']);
        Route::delete('/exercises/{id}', [ExerciseController::class, 'destroy']);

        // --- Gestión de Usuarios ---
        Route::get('/users',             [UserController::class, 'index']);
        Route::get('/users/{id}',        [UserController::class, 'show']);
        Route::get('/users/stats/general', [UserController::class, 'getStats']);

        // --- Gráficos ---
        Route::get('/charts',            [ChartController::class, 'index']);
        Route::post('/charts',           [ChartController::class, 'store']);
        Route::put('/charts/{id}',       [ChartController::class, 'update']);
        Route::delete('/charts/{id}',    [ChartController::class, 'destroy']);

        // --- Reportes ---
        Route::prefix('/reports')->group(function () {
            Route::get('/general',      [ReportController::class, 'getGeneralStats']);     // rendimiento general semanal
            Route::get('/subjects',     [ReportController::class, 'getSubjectStats']);     // promedios por materia
            Route::get('/lives',        [ReportController::class, 'getLivesStats']);       // estadísticas de vidas
            Route::get('/new-users',    [ReportController::class, 'getNewUsersPerMonth']); // nuevos usuarios últimos 6 meses
            Route::get('/most-viewed',  [ReportController::class, 'getMostViewedSubjects']); // materias más vistas
        });
    });
});
Route::get('/ping', fn () => response()->json(['ok' => true, 'ts' => now()]));
