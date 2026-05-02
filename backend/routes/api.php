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
| Social OAuth
| GET  /api/auth/social/{provider}           — redirect to provider
| GET  /api/auth/social/{provider}/callback  — handle callback, return token
| DELETE /api/auth/social/{provider}         — unlink provider (auth required)
| Supported providers: google, facebook, linkedin, wechat
|--------------------------------------------------------------------------
*/
Route::prefix('auth/social')->group(function () {
    Route::get   ('{provider}',          [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirect']);
    Route::get   ('{provider}/callback', [\App\Http\Controllers\Auth\SocialAuthController::class, 'callback']);
    Route::delete('{provider}',          [\App\Http\Controllers\Auth\SocialAuthController::class, 'unlink'])
        ->middleware('auth:sanctum');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes (any role)
| check.status: blocks suspended/deleted accounts after Sanctum authentication
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Shared Interview Routes (role-filtered in controller)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::get ('/interviews',            [\App\Http\Controllers\InterviewController::class, 'index']);
    Route::post('/interviews',            [\App\Http\Controllers\InterviewController::class, 'store']);
    Route::get ('/interviews/{id}',       [\App\Http\Controllers\InterviewController::class, 'show']);
    Route::put ('/interviews/{id}',       [\App\Http\Controllers\InterviewController::class, 'update']);
    Route::put ('/interviews/{id}/cancel',[\App\Http\Controllers\InterviewController::class, 'cancel']);
});
// Join: accepts Bearer token OR X-Room-Token (no standard auth:sanctum guard)
Route::post('/interviews/{id}/join', [\App\Http\Controllers\InterviewController::class, 'join'])
    ->middleware('interview.room.auth');

Route::middleware(['auth:sanctum', 'check.status'])->group(function () {

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
        // Resumes
        Route::get   ('/resumes',                 [\App\Http\Controllers\Student\ResumeController::class,        'index']);
        Route::post  ('/resumes',                 [\App\Http\Controllers\Student\ResumeController::class,        'store']);
        Route::put   ('/resumes/{id}',            [\App\Http\Controllers\Student\ResumeController::class,        'update']);
        Route::delete('/resumes/{id}',            [\App\Http\Controllers\Student\ResumeController::class,        'destroy']);
        // Talent Card
        Route::get   ('/talent-card',             [\App\Http\Controllers\Student\TalentCardController::class,    'show']);
        Route::put   ('/talent-card',             [\App\Http\Controllers\Student\TalentCardController::class,    'update']);
        Route::get   ('/applications',            [\App\Http\Controllers\Student\ApplicationController::class,   'index']);
        Route::post  ('/applications',            [\App\Http\Controllers\Student\ApplicationController::class,   'store']);
        Route::delete('/applications/{id}',       [\App\Http\Controllers\Student\ApplicationController::class,   'destroy']);
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
        Route::get   ('/jobs',                    [\App\Http\Controllers\Enterprise\JobController::class,        'index']);
        Route::post  ('/jobs',                    [\App\Http\Controllers\Enterprise\JobController::class,        'store'])->middleware('enterprise.verified');
        Route::put   ('/jobs/{id}',               [\App\Http\Controllers\Enterprise\JobController::class,        'update']);
        Route::delete('/jobs/{id}',               [\App\Http\Controllers\Enterprise\JobController::class,        'destroy']);
        Route::put   ('/jobs/{id}/publish',       [\App\Http\Controllers\Enterprise\JobController::class,        'publish'])->middleware('enterprise.verified');
        Route::put   ('/jobs/{id}/close',         [\App\Http\Controllers\Enterprise\JobController::class,        'close']);
        Route::get   ('/applications',             [\App\Http\Controllers\Enterprise\ApplicationController::class,'index']);
        Route::get   ('/applications/{id}',          [\App\Http\Controllers\Enterprise\ApplicationController::class,'show']);
        Route::put   ('/applications/{id}/status',   [\App\Http\Controllers\Enterprise\ApplicationController::class,'updateStatus']);
        Route::get   ('/jobs/{id}/applications',  [\App\Http\Controllers\Enterprise\JobController::class,        'applications']);
        Route::get   ('/talent',                  [\App\Http\Controllers\Enterprise\TalentController::class,     'index']);
        Route::get   ('/talent/{id}',             [\App\Http\Controllers\Enterprise\TalentController::class,     'show']);
    });

    /*
    |------------------------------------------------------------------
    | Admin Panel
    |------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get   ('/dashboard',               [\App\Http\Controllers\Admin\DashboardController::class,      'index']);

        // User management
        Route::get   ('/users',                           [\App\Http\Controllers\Admin\UserController::class, 'index']);
        Route::post  ('/users/admin',                     [\App\Http\Controllers\Admin\UserController::class, 'createAdmin']);
        Route::get   ('/users/{id}',                      [\App\Http\Controllers\Admin\UserController::class, 'show']);
        Route::put   ('/users/{id}/status',               [\App\Http\Controllers\Admin\UserController::class, 'updateStatus']);
        Route::put   ('/users/{id}/activate-enterprise',  [\App\Http\Controllers\Admin\UserController::class, 'activateEnterprise']);
        Route::delete('/users/{id}',                      [\App\Http\Controllers\Admin\UserController::class, 'destroy']);

        // Jobs
        Route::get   ('/jobs',                    [\App\Http\Controllers\Admin\JobController::class,            'index']);
        Route::delete('/jobs/{id}',               [\App\Http\Controllers\Admin\JobController::class,            'destroy']);

        // Applications
        Route::get   ('/applications',            [\App\Http\Controllers\Admin\ApplicationController::class,   'index']);

        // Resume review
        Route::get   ('/resumes',                 [\App\Http\Controllers\Admin\ResumeController::class,         'index']);
        Route::put   ('/resumes/{id}/review',     [\App\Http\Controllers\Admin\ResumeController::class,         'review']);

        // Interviews
        Route::get   ('/interviews',                     [\App\Http\Controllers\Admin\InterviewController::class,      'index']);
        Route::put   ('/interviews/{id}/record',          [\App\Http\Controllers\Admin\InterviewController::class,      'updateRecord']);

        // Seminars
        Route::get   ('/seminars',                [\App\Http\Controllers\Admin\SeminarController::class,        'index']);
        Route::post  ('/seminars',                [\App\Http\Controllers\Admin\SeminarController::class,        'store']);
        Route::get   ('/seminars/{id}',           [\App\Http\Controllers\Admin\SeminarController::class,        'show']);
        Route::put   ('/seminars/{id}',           [\App\Http\Controllers\Admin\SeminarController::class,        'update']);
        Route::delete('/seminars/{id}',           [\App\Http\Controllers\Admin\SeminarController::class,        'destroy']);
        Route::post  ('/seminars/{id}/go-live',   [\App\Http\Controllers\Admin\SeminarController::class,        'goLive']);
        Route::post  ('/seminars/{id}/end-live',  [\App\Http\Controllers\Admin\SeminarController::class,        'endLive']);

        // Announcements
        Route::apiResource('/announcements',      \App\Http\Controllers\Admin\AnnouncementController::class);

        // CMS Pages
        Route::get   ('/pages',              [\App\Http\Controllers\Admin\PageController::class, 'index']);
        Route::post  ('/pages',              [\App\Http\Controllers\Admin\PageController::class, 'store']);
        Route::get   ('/pages/{id}',         [\App\Http\Controllers\Admin\PageController::class, 'show']);
        Route::put   ('/pages/{id}',         [\App\Http\Controllers\Admin\PageController::class, 'update']);
        Route::delete('/pages/{id}',         [\App\Http\Controllers\Admin\PageController::class, 'destroy']);

        // CMS Posts / News
        Route::get   ('/posts',              [\App\Http\Controllers\Admin\PostController::class, 'index']);
        Route::post  ('/posts',              [\App\Http\Controllers\Admin\PostController::class, 'store']);
        Route::get   ('/posts/{id}',         [\App\Http\Controllers\Admin\PostController::class, 'show']);
        Route::put   ('/posts/{id}',         [\App\Http\Controllers\Admin\PostController::class, 'update']);
        Route::delete('/posts/{id}',         [\App\Http\Controllers\Admin\PostController::class, 'destroy']);
        Route::post  ('/posts/{id}/publish', [\App\Http\Controllers\Admin\PostController::class, 'publish']);
        Route::post  ('/posts/{id}/unpublish',[\App\Http\Controllers\Admin\PostController::class,'unpublish']);

        // Contact messages
        Route::get   ('/contacts',                [\App\Http\Controllers\Admin\ContactController::class,        'index']);
        Route::get   ('/contacts/{id}',           [\App\Http\Controllers\Admin\ContactController::class,        'show']);
        Route::put   ('/contacts/{id}/status',    [\App\Http\Controllers\Admin\ContactController::class,        'updateStatus']);
        Route::post  ('/contacts/{id}/reply',     [\App\Http\Controllers\Admin\ContactController::class,        'reply']);
        Route::delete('/contacts/{id}',           [\App\Http\Controllers\Admin\ContactController::class,        'destroy']);

        // Settings
        Route::get   ('/settings',                [\App\Http\Controllers\Admin\SettingsController::class,       'index']);
        Route::put   ('/settings',                [\App\Http\Controllers\Admin\SettingsController::class,       'update']);
        Route::post  ('/settings/test-smtp',      [\App\Http\Controllers\Admin\SettingsController::class,       'testSmtp']);
        Route::post  ('/settings/upload-logo',    [\App\Http\Controllers\Admin\SettingsController::class,       'uploadLogo']);
        Route::post  ('/settings/upload-favicon', [\App\Http\Controllers\Admin\SettingsController::class,       'uploadFavicon']);

        // Language Settings
        Route::get   ('/languages',                  [\App\Http\Controllers\Admin\LanguageController::class,       'index']);
        Route::post  ('/languages',                  [\App\Http\Controllers\Admin\LanguageController::class,       'store']);
        Route::put   ('/languages/{code}',           [\App\Http\Controllers\Admin\LanguageController::class,       'update']);
        Route::delete('/languages/{code}',           [\App\Http\Controllers\Admin\LanguageController::class,       'destroy']);

        // Translations (export/import MUST be declared before {key} wildcard)
        Route::get   ('/translations/export',        [\App\Http\Controllers\Admin\TranslationController::class,   'export']);
        Route::post  ('/translations/import',        [\App\Http\Controllers\Admin\TranslationController::class,   'import']);
        Route::get   ('/translations',               [\App\Http\Controllers\Admin\TranslationController::class,   'index']);
        Route::post  ('/translations',               [\App\Http\Controllers\Admin\TranslationController::class,   'store']);
        Route::get   ('/translations/{key}',         [\App\Http\Controllers\Admin\TranslationController::class,   'show']);
        Route::put   ('/translations/{key}',         [\App\Http\Controllers\Admin\TranslationController::class,   'update']);
        Route::delete('/translations/{key}',         [\App\Http\Controllers\Admin\TranslationController::class,   'destroy']);
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
    Route::get('/enterprises',       [\App\Http\Controllers\Public\EnterpriseController::class, 'index']);
    Route::get('/enterprises/{id}',  [\App\Http\Controllers\Public\EnterpriseController::class, 'show']);
    Route::get('/talent',            [\App\Http\Controllers\Public\TalentController::class,  'index']);
    Route::get('/talent/{id}',       [\App\Http\Controllers\Public\TalentController::class,  'show']);
    Route::get ('/seminars',                    [\App\Http\Controllers\Public\SeminarController::class, 'index']);
    Route::get ('/seminars/{id}',               [\App\Http\Controllers\Public\SeminarController::class, 'show']);
    Route::post('/seminars/{id}/register',      [\App\Http\Controllers\Public\SeminarController::class, 'register']);
    Route::get ('/seminars/{id}/recording',     [\App\Http\Controllers\Public\SeminarController::class, 'recording']);
    Route::get ('/seminars/{id}/danmu',         [\App\Http\Controllers\Public\SeminarController::class, 'getDanmu']);
    Route::post('/seminars/{id}/danmu',         [\App\Http\Controllers\Public\SeminarController::class, 'sendDanmu']);
    // Pages (CMS static pages)
    Route::get('/pages',             [\App\Http\Controllers\Public\PageController::class,    'index']);
    Route::get('/pages/{slug}',      [\App\Http\Controllers\Public\PageController::class,    'show']);
    // Posts (news / announcements)
    Route::get('/posts',             [\App\Http\Controllers\Public\PostController::class,    'index']);
    Route::get('/posts/{id}',        [\App\Http\Controllers\Public\PostController::class,    'show']);
    Route::post('/contact',          [\App\Http\Controllers\Public\ContactController::class, 'store']);
    Route::get('/settings',          [\App\Http\Controllers\Public\SettingsController::class,   'publicConfig']);
    Route::get('/languages',         [\App\Http\Controllers\Public\LanguageController::class,    'activeLanguages']);
    Route::get('/translations',      [\App\Http\Controllers\Public\TranslationController::class, 'index']);
});
