<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\v1\SignUpController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\CatalogController;
use App\Http\Controllers\v1\EventController;
use App\Http\Controllers\v1\CompetitionController;
use App\Http\Controllers\v1\NoticeController;
use App\Http\Controllers\v1\ClubNewsController;
use App\Http\Controllers\v1\PagesController;
use App\Http\Controllers\v1\ClubSettingsController;
use App\Http\Controllers\v1\MemberController;
use App\Http\Controllers\v1\ClubDashboardController;
use App\Http\Controllers\v1\FeaturedImageController;

Route::prefix('v1')->group(function() {

    Route::post('sign-up', [SignUpController::class, 'store']);
    Route::post('already-registered', [SignUpController::class, 'alreadyRegistered']);
    Route::post('already-registered-domain', [SignUpController::class, 'alreadyRegisteredDomain']);
    Route::get('email-verified', [SignUpController::class, 'EmailVerification'])->name('email-verified');

    Route::post('login', [AuthController::class, 'login']);


    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');


    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // club admin routes
        Route::middleware(['auth', 'role:club_admin'])->group(function () {
            Route::apiResource('catalogs', CatalogController::class);
        
            Route::apiResource('events', EventController::class);
            Route::get('/event-extras', [EventController::class, 'getEventExtras']);

            Route::apiResource('competitions', CompetitionController::class);
            Route::get('/competition-extras', [CompetitionController::class, 'getCompetitionExtras']);

            Route::apiResource('notices', NoticeController::class);
            Route::get('/notices-extras', [NoticeController::class, 'getNoticeExtras']);

            Route::apiResource('club-news', ClubNewsController::class);
            Route::get('/club-news-extras', [ClubNewsController::class, 'getClubNewsExtras']);

            Route::apiResource('pages', PagesController::class);
            Route::get('/pages-extras', [PagesController::class, 'getPagesExtras']);

            Route::apiResource('club-settings', ClubSettingsController::class);
            Route::apiResource('members', MemberController::class);
            Route::get('/club/dashboard', [ClubDashboardController::class, 'getDashboardData']);

            Route::post('/assign-feature-image', [FeaturedImageController::class, 'assign']);
        });

        // member routes
        Route::middleware(['auth', 'role:member'])->group(function () {
            Route::post('/assign-club', [MemberController::class, 'assignClub']);
            Route::post('/create-gallery', [MemberController::class, 'createGallery']);
            Route::post('/upload-gallery-images', [MemberController::class, 'uploadGalleryImages']);
            Route::post('/post-comment', [MemberController::class, 'postCommentOrLikes']);
        });

    });

});

