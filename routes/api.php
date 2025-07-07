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
use App\Http\Controllers\v1\MemberAdminController;

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

            Route::apiResource('members', MemberController::class);
            Route::get('/members-galleries', [MemberController::class, 'membersGalleries']);
            Route::get('/member/{member}/galleries', [MemberController::class, 'memberGalleryDetails']);

            Route::apiResource('club-settings', ClubSettingsController::class);
            Route::get('/club/dashboard', [ClubDashboardController::class, 'getDashboardData']);

            Route::post('/assign-feature-image', [FeaturedImageController::class, 'assign']);
        });

        // member routes
        Route::middleware(['auth', 'role:member'])->group(function () {
            Route::post('/assign-club', [MemberController::class, 'assignClub']);
            Route::get('/joined-clubs', [MemberController::class, 'joinedClubs']);

            Route::post('/create-gallery', [MemberController::class, 'createGallery']);
            Route::post('/upload-gallery-images', [MemberController::class, 'uploadGalleryImages']);
            Route::post('/post-comment', [MemberController::class, 'postCommentOrLikes']);
            Route::put('/profile/update', [MemberController::class, 'profileUpdate']);

            //competition routes
            Route::post('/join-competition', [CompetitionController::class, 'joinCompetition']);
            Route::post('/submit-competition-entry', [CompetitionController::class, 'submitCompetitionEntry']);

            //member admin routes
            Route::get('/member-admin-events', [MemberAdminController::class, 'memberEvents']);
            Route::get('/member-admin-event/{id}', [MemberAdminController::class, 'memberSingleEvent']);
            Route::get('/member-admin-competitions', [MemberAdminController::class, 'memberCompetitions']);
            Route::get('/member-admin-competition/{id}', [MemberAdminController::class, 'memberSingleCompetition']);
            Route::get('/profile/posts/view', [MemberAdminController::class, 'memberProfilePostsView']);
            Route::post('/notices', [NoticeController::class, 'create']);
            Route::put('/notices/{id}', [NoticeController::class, 'update']);
            Route::get('/member-admin-profile', [MemberAdminController::class, 'memberAdminProfile']);
            Route::get('/member-admin/profile/portfolio', [MemberAdminController::class, 'memberAdminProfilePortfolio']);
            Route::get('/member-admin/profile/learning', [MemberAdminController::class, 'memberAdminProfileLearning']);
            Route::get('/member-admin-notes', [MemberAdminController::class, 'memberNotes']);
            
        });

    });

});

