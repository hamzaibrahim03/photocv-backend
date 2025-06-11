<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\v1\SignUpController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\SchoolController;
use App\Http\Controllers\v1\StudentController;
use App\Http\Controllers\v1\CatalogController;
use App\Http\Controllers\v1\EventController;
use App\Http\Controllers\v1\CompetitionController;
use App\Http\Controllers\v1\NoticeController;
use App\Http\Controllers\v1\ClubNewsController;
use App\Http\Controllers\v1\PagesController;
use App\Http\Controllers\v1\ClubSettingsController;
use App\Http\Controllers\v1\MemberController;
use App\Http\Controllers\v1\ClubDashboardController;

Route::prefix('v1')->group(function() {

    Route::post('sign-up', [SignUpController::class, 'store']);
    Route::get('email-verified', [SignUpController::class, 'EmailVerification'])->name('email-verified');

    Route::post('login', [AuthController::class, 'login']);


    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');


    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');


        // Admin/Teacher Routes (Role: Admin/Teacher)
        Route::middleware(['auth', 'role:admin,club_admin'])->group(function () {
            Route::apiResource('schools', SchoolController::class);
           
            

        });

        // Parent Routes (Role: Parent)
        Route::middleware(['auth', 'role:club_admin'])->group(function () {
            Route::apiResource('students', StudentController::class);
            Route::post('parent/add_student', [StudentController::class, 'add_student']);
        });

        // Student Routes (Role: Student)
        Route::middleware(['auth', 'role:member'])->group(function () {
           
        });


        Route::apiResource('catalogs', CatalogController::class);
        Route::apiResource('events', EventController::class);
        Route::apiResource('competitions', CompetitionController::class);
        Route::apiResource('notices', NoticeController::class);
        Route::apiResource('club-news', ClubNewsController::class);
        Route::apiResource('pages', PagesController::class);
        Route::apiResource('club-settings', ClubSettingsController::class);
        Route::apiResource('members', MemberController::class);
        Route::get('/club/dashboard', [ClubDashboardController::class, 'getDashboardData']);
        Route::post('/assign-club', [MemberController::class, 'assignClub']);

    });

});



