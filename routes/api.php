<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\v1\SignUpController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\ClubAdmin\CatalogController;
use App\Http\Controllers\v1\ClubAdmin\EventController;
use App\Http\Controllers\v1\ClubAdmin\CompetitionController;
use App\Http\Controllers\v1\ClubAdmin\CompetitionResultController;
use App\Http\Controllers\v1\Member\MemberCompetitionController;
use App\Http\Controllers\v1\NoticeController;
use App\Http\Controllers\v1\ClubAdmin\ClubNewsController;
use App\Http\Controllers\v1\ClubAdmin\PagesController;
use App\Http\Controllers\v1\ClubAdmin\ClubSettingsController;
use App\Http\Controllers\v1\MemberController;
use App\Http\Controllers\v1\ClubAdmin\MemberRequestController;
use App\Http\Controllers\v1\ClubAdmin\ClubDashboardController;
use App\Http\Controllers\v1\ClubAdmin\FeaturedImageController;
use App\Http\Controllers\v1\Member\MemberAdminController;
use App\Http\Controllers\v1\Member\MemberNoteController;
use App\Http\Controllers\v1\Member\MemberClassLogController;
use App\Http\Controllers\v1\Member\MemberInterestBrandController;
use App\Http\Controllers\v1\ClubPublic\PublicClubController;
use App\Http\Controllers\v1\Member\MemberPracticeLogController;
use App\Http\Controllers\v1\ClubAdmin\ClubGalleryController;
use App\Http\Controllers\v1\ClubAdmin\RoleController;
use App\Http\Controllers\v1\ClubAdmin\CompetitionGlobalSettingController;
use App\Http\Controllers\v1\Judge\JudgeController;
use App\Http\Controllers\v1\Member\BookingController;
use App\Http\Controllers\v1\Member\PlannedLocationController;
use App\Http\Controllers\v1\GlobalSearchController;
use App\Http\Controllers\v1\Member\PlannedDayOutController;

Route::prefix('v1')->group(function() {

    Route::post('sign-up', [SignUpController::class, 'store']);
    Route::post('already-registered', [SignUpController::class, 'alreadyRegistered']);
    Route::post('already-registered-domain', [SignUpController::class, 'alreadyRegisteredDomain']);
    Route::get('email-verified', [SignUpController::class, 'EmailVerification'])->name('email-verified');

    Route::post('login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');

    // Public club routes
    Route::domain('{username}-staging.cameraclub.website')
        ->prefix('club/public')
        ->name('club.public.')
        ->group(function () {
            Route::get('/home', [PublicClubController::class, 'home']);
            Route::get('/event', [PublicClubController::class, 'eventIndex']);
            Route::post('/event', [PublicClubController::class, 'event']);

            Route::post('/competitions', [PublicClubController::class, 'competitionIndex']);
            Route::post('/competition', [PublicClubController::class, 'competition']);

            Route::get('/galleries', [PublicClubController::class, 'galleries']);
            Route::get('/gallery/{id}', [PublicClubController::class, 'clubGallery']);
            Route::get('/member/{id}/galleries', [PublicClubController::class, 'memberGalleries']);

            Route::get('/news', [PublicClubController::class, 'newsIndex']);
            Route::post('/news', [PublicClubController::class, 'newsSingle']);

            Route::get('/about-us', [PublicClubController::class, 'aboutUs']);
        });

    Route::get('/search', [GlobalSearchController::class, 'search'])->name('global.search');

    // Route::domain('{username}-staging.cameraclub.website')->group(function () {
    //     Route::get('/club-public-data', [PublicClubController::class, 'home']);
    // });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/roles', [RoleController::class, 'index']);

        //judges endpoints
        Route::prefix('judge')->name('judge.')->group(function () {
            Route::get('/{competitionId}/entries', [JudgeController::class, 'getEntriesWithScores']);
            Route::post('/entry/{entryId}/score', [JudgeController::class, 'saveScore']);

            Route::post('/dashboard', [JudgeController::class, 'dashboardData']);
            Route::get('/clubs/{clubId}/competitions', [JudgeController::class, 'competitionsForJudge']);
        });

        // club admin routes
        Route::middleware(['auth', 'role:club_admin'])->group(function () {
            Route::apiResource('catalogs', CatalogController::class);
        
            Route::apiResource('events', EventController::class);
            Route::get('/event-extras', [EventController::class, 'getEventExtras']);

            Route::apiResource('competitions', CompetitionController::class);
            Route::get('/competition-extras', [CompetitionController::class, 'getCompetitionExtras']);
            Route::get('/competition-global-settings', [CompetitionGlobalSettingController::class, 'index']);
            Route::post('/competition-global-settings', [CompetitionGlobalSettingController::class, 'store']);

            Route::apiResource('/competition-results', CompetitionResultController::class);
            Route::post('/competition-results/{competitionId}/publish', [CompetitionResultController::class, 'publishResults']);
            Route::get('/competition-results/club/published-results', [CompetitionResultController::class, 'recentPublishedResults']);

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

            Route::apiResource('club-gallery', ClubGalleryController::class);

            Route::get('/member-pending-requests', [MemberRequestController::class, 'pendingRequests']);
            Route::post('/assign-club', [MemberRequestController::class, 'assignClub']);
            Route::get('/member-request/{userId}', [MemberRequestController::class, 'getRequestingMember']);
            Route::post('/reject-club-request', [MemberRequestController::class, 'rejectClubRequest']);
            
        });

        // member routes
        Route::middleware(['auth', 'role:member'])->group(function () {
            Route::post('/join-club', [MemberController::class, 'requestToJoinClub']);
            Route::get('/joined-clubs', [MemberController::class, 'joinedClubs']);

            Route::apiResource('planned-day-outs', PlannedDayOutController::class);

            Route::apiResource('booking', BookingController::class);
            Route::post('booking-extras', [BookingController::class, 'bookingExtras']);

            Route::apiResource('planned-locations', PlannedLocationController::class);

            Route::post('/create-gallery', [MemberController::class, 'createGallery']);
            Route::post('/upload-gallery-images', [MemberController::class, 'uploadGalleryImages']);
            Route::post('/post-comment', [MemberController::class, 'postCommentOrLikes']);
            Route::put('/profile/update', [MemberController::class, 'profileUpdate']);

            //competition routes
            Route::post('/join-competition', [MemberCompetitionController::class, 'joinCompetition']);
            Route::get('/get-joined-competition', [MemberCompetitionController::class, 'joinedCompetition']);
            Route::post('/submit-competition-entry', [MemberCompetitionController::class, 'submitCompetitionEntry']);

            //member admin routes
            Route::get('/profile/posts/view', [MemberAdminController::class, 'memberProfilePostsView']);
            Route::apiResource('member-notice', NoticeController::class)->only(['store', 'update']);

            Route::prefix('member-admin')->group(function () {

                Route::get('/events', [MemberAdminController::class, 'memberEvents']);
                Route::get('/event/{id}', [MemberAdminController::class, 'memberSingleEvent']);

                Route::get('/competitions', [MemberAdminController::class, 'memberCompetitions']);
                Route::get('/competition/{id}', [MemberAdminController::class, 'memberSingleCompetition']);

                Route::get('/profile', [MemberAdminController::class, 'memberAdminProfile']);
                Route::get('/profile/portfolio', [MemberAdminController::class, 'memberAdminProfilePortfolio']);
                Route::get('/profile/learning', [MemberAdminController::class, 'memberAdminProfileLearning']);

                Route::apiResource('profile/notes', MemberNoteController::class);
                Route::apiResource('profile/class-logs', MemberClassLogController::class);
                Route::apiResource('profile/interests-brands', MemberInterestBrandController::class);

                Route::get('practice-logs', [MemberPracticeLogController::class, 'index']);
                Route::post('practice-logs', [MemberPracticeLogController::class, 'store']);
                Route::delete('practice-logs/{id}', [MemberPracticeLogController::class, 'destroy']);

            });
            
        });

    });

});

