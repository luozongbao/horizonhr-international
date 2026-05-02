<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes are prefixed with /api automatically by Laravel.
| All routes here use the Sanctum stateful middleware (configured in Kernel).
|
*/

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/
Route::get('/health', fn () => response()->json(['status' => 'ok', 'service' => 'HRINT API']));

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Public auth endpoints — rate-limited to 5 requests/minute per IP
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('register',        [\App\Http\Controllers\Auth\AuthController::class, 'register']);
        Route::post('login',           [\App\Http\Controllers\Auth\AuthController::class, 'login']);
        Route::post('forgot-password', [\App\Http\Controllers\Auth\AuthController::class, 'forgotPassword']);
        Route::post('reset-password',  [\App\Http\Controllers\Auth\AuthController::class, 'resetPassword']);
        Route::post('confirm-email',   [\App\Http\Controllers\Auth\AuthController::class, 'confirmEmail']);
    });

    // Protected auth endpoints (require valid Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout',  [\App\Http\Controllers\Auth\AuthController::class, 'logout']);
        Route::post('refresh', [\App\Http\Controllers\Auth\AuthController::class, 'refresh']);
        Route::get ('me',      [\App\Http\Controllers\Auth\AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated routes (any role)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |------------------------------------------------------------------
    | Student Portal
    |------------------------------------------------------------------
    */
    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::get   ('/dashboard',               [\App\Http\Controllers\Student\DashboardController::class,     'index']);
        Route::get   ('/profile',                 [\App\Http\Controllers\Student\ProfileController::class,       'show']);
        Route::put   ('/profile',                 [\App\Http\Controllers\Student\ProfileController::class,       'update']);
        Route::post  ('/profile/avatar',          [\App\Http\Controllers\Student\ProfileController::class,       'uploadAvatar']);
        Route::get   ('/resume',                  [\App\Http\Controllers\Student\ResumeController::class,        'show']);
        Route::put   ('/resume',                  [\App\Http\Controllers\Student\ResumeController::class,        'update']);
        Route::post  ('/resume/upload',           [\App\Http\Controllers\Student\ResumeController::class,        'upload']);
        Route::get   ('/applications',            [\App\Http\Controllers\Student\ApplicationController::class,   'index']);
        Route::post  ('/applications',            [\App\Http\Controllers\Student\ApplicationController::class,   'store']);
        Route::delete('/applications/{id}',       [\App\Http\Controllers\Student\ApplicationController::class,   'destroy']);
        Route::get   ('/interviews',              [\App\Http\Controllers\Student\InterviewController::class,     'index']);
        Route::get   ('/interviews/{id}/token',   [\App\Http\Controllers\Student\InterviewController::class,     'trtcToken']);
        Route::get   ('/seminars',                [\App\Http\Controllers\Student\SeminarController::class,       'index']);
        Route::post  ('/seminars/{id}/register',  [\App\Http\Controllers\Student\SeminarController::class,       'register']);
        Route::get   ('/seminars/{id}/stream',    [\App\Http\Controllers\Student\SeminarController::class,       'streamUrl']);
    });

    /*
    |------------------------------------------------------------------
    | Enterprise Portal
    |------------------------------------------------------------------
    */
    Route::middleware('role:enterprise')->prefix('enterprise')->group(function () {
        Route::get   ('/dashboard',               [\App\Http\Controllers\Enterprise\DashboardController::class,  'index']);
        Route::get   ('/profile',                 [\App\Http\Controllers\Enterprise\ProfileController::class,    'show']);
        Route::put   ('/profile',                 [\App\Http\Controllers\Enterprise\ProfileController::class,    'update']);
        Route::post  ('/profile/logo',            [\App\Http\Controllers\Enterprise\ProfileController::class,    'uploadLogo']);
        Route::apiResource('/jobs',               \App\Http\Controllers\Enterprise\JobController::class);
        Route::get   ('/jobs/{id}/applications',  [\App\Http\Controllers\Enterprise\JobController::class,        'applications']);
        Route::put   ('/applications/{id}/status',[\App\Http\Controllers\Enterprise\ApplicationController::class,'updateStatus']);
        Route::get   ('/talent',                  [\App\Http\Controllers\Enterprise\TalentController::class,     'index']);
        Route::get   ('/talent/{id}',             [\App\Http\Controllers\Enterprise\TalentController::class,     'show']);
        Route::post  ('/interviews',              [\App\Http\Controllers\Enterprise\InterviewController::class,  'store']);
        Route::put   ('/interviews/{id}',         [\App\Http\Controllers\Enterprise\InterviewController::class,  'update']);
        Route::get   ('/interviews/{id}/token',   [\App\Http\Controllers\Enterprise\InterviewController::class,  'trtcToken']);
    });

    /*
    |------------------------------------------------------------------
    | Admin Panel
    |------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get   ('/dashboard',               [\App\Http\Controllers\Admin\DashboardController::class,      'index']);

        // User management
        Route::apiResource('/users',              \App\Http\Controllers\Admin\UserController::class);
        Route::put   ('/users/{id}/role',         [\App\Http\Controllers\Admin\UserController::class,           'updateRole']);
        Route::put   ('/users/{id}/status',       [\App\Http\Controllers\Admin\UserController::class,           'updateStatus']);

        // Resume review
        Route::get   ('/resumes',                 [\App\Http\Controllers\Admin\ResumeController::class,         'index']);
        Route::put   ('/resumes/{id}/status',     [\App\Http\Controllers\Admin\ResumeController::class,         'updateStatus']);

        // Interviews
        Route::get   ('/interviews',              [\App\Http\Controllers\Admin\InterviewController::class,      'index']);

        // Seminars
        Route::apiResource('/seminars',           \App\Http\Controllers\Admin\SeminarController::class);
        Route::post  ('/seminars/{id}/start',     [\App\Http\Controllers\Admin\SeminarController::class,        'startStream']);
        Route::post  ('/seminars/{id}/end',       [\App\Http\Controllers\Admin\SeminarController::class,        'endStream']);

        // Announcements
        Route::apiResource('/announcements',      \App\Http\Controllers\Admin\AnnouncementController::class);

        // CMS Pages
        Route::apiResource('/pages',              \App\Http\Controllers\Admin\PageController::class);

        // CMS Posts / News
        Route::apiResource('/posts',              \App\Http\Controllers\Admin\PostController::class);
        Route::put   ('/posts/{id}/publish',      [\App\Http\Controllers\Admin\PostController::class,           'publish']);

        // Contact messages
        Route::get   ('/contacts',                [\App\Http\Controllers\Admin\ContactController::class,        'index']);
        Route::get   ('/contacts/{id}',           [\App\Http\Controllers\Admin\ContactController::class,        'show']);
        Route::put   ('/contacts/{id}/status',    [\App\Http\Controllers\Admin\ContactController::class,        'updateStatus']);
        Route::post  ('/contacts/{id}/reply',     [\App\Http\Controllers\Admin\ContactController::class,        'reply']);
        Route::delete('/contacts/{id}',           [\App\Http\Controllers\Admin\ContactController::class,        'destroy']);

        // Settings
        Route::get   ('/settings',                [\App\Http\Controllers\Admin\SettingsController::class,       'index']);
        Route::put   ('/settings',                [\App\Http\Controllers\Admin\SettingsController::class,       'update']);

        // Language / i18n
        Route::get   ('/languages',               [\App\Http\Controllers\Admin\LanguageController::class,       'index']);
        Route::put   ('/languages/{locale}',      [\App\Http\Controllers\Admin\LanguageController::class,       'update']);
    });
});

/*
|--------------------------------------------------------------------------
| Public API (no auth)
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    Route::get('/jobs',              [\App\Http\Controllers\Public\JobController::class,     'index']);
    Route::get('/jobs/{id}',         [\App\Http\Controllers\Public\JobController::class,     'show']);
    Route::get('/talent',            [\App\Http\Controllers\Public\TalentController::class,  'index']);
    Route::get('/seminars',          [\App\Http\Controllers\Public\SeminarController::class, 'index']);
    Route::get('/seminars/{id}',     [\App\Http\Controllers\Public\SeminarController::class, 'show']);
    Route::get('/news',              [\App\Http\Controllers\Public\NewsController::class,    'index']);
    Route::get('/news/{slug}',       [\App\Http\Controllers\Public\NewsController::class,    'show']);
    Route::get('/pages/{slug}',      [\App\Http\Controllers\Public\PageController::class,    'show']);
    Route::post('/contact',          [\App\Http\Controllers\Public\ContactController::class, 'store']);
    Route::get('/settings',          [\App\Http\Controllers\Public\SettingsController::class,'publicConfig']);
});
