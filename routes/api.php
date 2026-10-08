<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\v1\SignUpController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\ClubAdmin\CatalogController;
use App\Http\Controllers\v1\ClubAdmin\EventController;
use App\Http\Controllers\v1\ClubAdmin\CompetitionController;
use App\Http\Controllers\v1\ClubAdmin\CompetitionResultController;
use App\Http\Controllers\v1\PhotoCommentController;
use App\Http\Controllers\v1\Member\MemberCompetitionController;
use App\Http\Controllers\v1\NoticeController;
use App\Http\Controllers\v1\ClubAdmin\ClubNewsController;
use App\Http\Controllers\v1\ClubAdmin\PagesController;
use App\Http\Controllers\v1\ClubAdmin\ClubSettingsController;
use App\Http\Controllers\v1\ClubAdmin\ClubSettingsStepsController;
use App\Http\Controllers\v1\ClubAdmin\ClubConfigurationStepsController;
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
use App\Http\Controllers\v1\Judge\JudgePanelController;
use App\Http\Controllers\v1\Member\BookingController;
use App\Http\Controllers\v1\Member\PlannedLocationController;
use App\Http\Controllers\v1\GlobalSearchController;
use App\Http\Controllers\v1\Member\PlannedDayOutController;
use App\Http\Controllers\v1\ClubAdmin\NotificationController;
use App\Http\Controllers\v1\Member\GearController;
use App\Http\Controllers\v1\Member\GearLibraryController;
use App\Http\Controllers\v1\Member\GearWishlistController;
use App\Http\Controllers\v1\Member\CheatSheetController;
use App\Http\Controllers\v1\Member\SavedLibraryItemController;
use App\Http\Controllers\v1\Super\SuperClubController;
use App\Http\Controllers\v1\Super\SuperProfileController;
use App\Http\Controllers\v1\Super\SuperRoleController;
use App\Http\Controllers\v1\Super\SuperPlatformSettingController;

Route::prefix('v1')->group(function() {

    Route::post('sign-up', [SignUpController::class, 'store']);
    Route::post('already-registered', [SignUpController::class, 'alreadyRegistered']);
    Route::post('already-registered-domain', [SignUpController::class, 'alreadyRegisteredDomain']);
    Route::get('email-verified', [SignUpController::class, 'EmailVerification'])->name('email-verified');

    Route::post('login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');


    // Route::middleware('auth:sanctum')->group(function () {
    //     Route::get('/notifications', [NotificationController::class, 'index']);
    //     Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    //     Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    //     Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);
    // });

    // Public club routes
    Route::domain('{username}-staging.cameraclub.website')
        ->prefix('club/public')
        ->name('club.public.')
        ->group(function () {
            Route::get('/home', [PublicClubController::class, 'home']);
            Route::get('/events', [PublicClubController::class, 'eventIndex']);
            Route::get('/event/{id}', [PublicClubController::class, 'event']);

            Route::get('/competitions', [PublicClubController::class, 'competitionIndex']);
            Route::get('/competition/{id}', [PublicClubController::class, 'competition']);
            Route::get('/competition-results', [PublicClubController::class, 'competitionResults']);
            Route::get('/competition-results/{id}', [PublicClubController::class, 'singleCompetitionResults']);

            Route::get('/galleries', [PublicClubController::class, 'galleries']);
            Route::get('/gallery/{id}', [PublicClubController::class, 'clubGallery']);
            Route::get('/member/{id}/galleries', [PublicClubController::class, 'memberGalleries']);
            Route::get('/random-club-galleries', [PublicClubController::class, 'randomClubGalleries']);
            Route::get('/random-member-galleries', [PublicClubController::class, 'randomMemberGalleries']);

            Route::get('/news', [PublicClubController::class, 'newsIndex']);
            Route::get('/news/{id}', [PublicClubController::class, 'newsSingle']);

            Route::get('/about-us', [PublicClubController::class, 'aboutUs']);

            Route::get('/notices', [PublicClubController::class, 'noticeIndex']);
            Route::get('/notice/{id}', [PublicClubController::class, 'noticeSingle']);

            Route::get('catalog/all-grouped', [CatalogController::class, 'getAllGrouped']);


            Route::middleware('auth:sanctum')->group(function () {
                Route::get('/notifications', [NotificationController::class, 'index']);
                Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
                Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
                Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);
                Route::get('/notifications/filters', [NotificationController::class, 'filters']);
            });

        });

    Route::get('/search', [GlobalSearchController::class, 'search'])->name('global.search');

    // Route::domain('{username}-staging.cameraclub.website')->group(function () {
    //     Route::get('/club-public-data', [PublicClubController::class, 'home']);
    // });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/roles', [RoleController::class, 'index']);

        // Any authenticated role that can view a photo (member, club_admin,
        // Secretary, judge, speaker, etc. via CommentsDrawMember/CommentsDrawClub)
        // can post a comment on it.
        Route::post('/photos/{photoId}/comments', [PhotoCommentController::class, 'store']);

        //judges endpoints
        Route::prefix('judge')->name('judge.')->group(function () {
            Route::get('/{competitionId}/entries', [JudgeController::class, 'getEntriesWithScores']);
            Route::post('/entry/{entryId}/score', [JudgeController::class, 'saveScore']);

            Route::post('/dashboard', [JudgeController::class, 'dashboardData']);
            Route::get('/clubs/{clubId}/competitions', [JudgeController::class, 'competitionsForJudge']);

            // judge panel screens
            Route::get('/dashboard', [JudgePanelController::class, 'dashboard']);
            Route::get('/overview', [JudgePanelController::class, 'overview']);
            Route::get('/clubs', [JudgePanelController::class, 'clubs']);
            Route::get('/competitions', [JudgePanelController::class, 'competitions']);
            Route::get('/competitions/{competitionId}', [JudgePanelController::class, 'competition'])->whereNumber('competitionId');
            Route::put('/competitions/{competitionId}', [JudgePanelController::class, 'updateCompetition'])->whereNumber('competitionId');
            Route::get('/competitions/{competitionId}/submissions', [JudgePanelController::class, 'submissions'])->whereNumber('competitionId');
            Route::get('/submissions/{entryId}', [JudgePanelController::class, 'submission'])->whereNumber('entryId');
            Route::post('/entry/{entryId}/bookmark', [JudgePanelController::class, 'bookmark'])->whereNumber('entryId');
        });

        // Shared with Speaker: Speaker screens (SpeakerClubEvents/SpeakerEventDetail/
        // SpeakerEventEdit) manage the club's events + the catalogs dropdown used
        // when editing one, so they need the same event/catalog access as club_admin.
        Route::middleware(['auth', 'role:club_admin,speaker'])->group(function () {
            Route::apiResource('catalogs', CatalogController::class);
            Route::apiResource('events', EventController::class);
            Route::get('/event-extras', [EventController::class, 'getEventExtras']);
        });

        // Shared with Secretary: Secretary screens (SecretaryCompetitions*,
        // SecretarySeasonResults, SecretaryImageLightbox) manage competitions and
        // publish/view their results, so they need the same access as club_admin.
        Route::middleware(['auth', 'role:club_admin,Secretary'])->group(function () {
            Route::apiResource('competitions', CompetitionController::class);
            Route::get('/competition-extras', [CompetitionController::class, 'getCompetitionExtras']);
            Route::get('/competition-global-settings', [CompetitionGlobalSettingController::class, 'index']);
            Route::post('/competition-global-settings', [CompetitionGlobalSettingController::class, 'store']);

            Route::apiResource('/competition-results', CompetitionResultController::class);
            Route::post('/competition-results/{competitionId}/publish', [CompetitionResultController::class, 'publishResults']);
            Route::get('/competition-results/club/published-results', [CompetitionResultController::class, 'recentPublishedResults']);
        });

        // club admin routes
        Route::middleware(['auth', 'role:club_admin'])->group(function () {
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

            Route::get('/club-settings-general', [ClubSettingsStepsController::class, 'generalShow']);
            Route::post('/club-settings-general', [ClubSettingsStepsController::class, 'generalStore']);
            Route::get('/club-settings-appearance', [ClubSettingsStepsController::class, 'appearanceShow']);
            Route::post('/club-settings-appearance', [ClubSettingsStepsController::class, 'appearanceStore']);
            Route::get('/club-settings-membership', [ClubSettingsStepsController::class, 'membershipShow']);
            Route::post('/club-settings-membership', [ClubSettingsStepsController::class, 'membershipStore']);
            Route::get('/club-settings-content', [ClubSettingsStepsController::class, 'contentShow']);
            Route::post('/club-settings-content', [ClubSettingsStepsController::class, 'contentStore']);
            Route::get('/club-settings-privacy', [ClubSettingsStepsController::class, 'privacyShow']);
            Route::post('/club-settings-privacy', [ClubSettingsStepsController::class, 'privacyStore']);

            Route::get('/club-configuration-general', [ClubConfigurationStepsController::class, 'generalShow']);
            Route::post('/club-configuration-general', [ClubConfigurationStepsController::class, 'generalStore']);
            Route::get('/club-configuration-news', [ClubConfigurationStepsController::class, 'newsShow']);
            Route::post('/club-configuration-news', [ClubConfigurationStepsController::class, 'newsStore']);
            Route::get('/club-configuration-events', [ClubConfigurationStepsController::class, 'eventsShow']);
            Route::post('/club-configuration-events', [ClubConfigurationStepsController::class, 'eventsStore']);
            Route::get('/club-configuration-galleries', [ClubConfigurationStepsController::class, 'galleriesShow']);
            Route::post('/club-configuration-galleries', [ClubConfigurationStepsController::class, 'galleriesStore']);
            Route::get('/club-configuration-competitions', [ClubConfigurationStepsController::class, 'competitionsShow']);
            Route::post('/club-configuration-competitions', [ClubConfigurationStepsController::class, 'competitionsStore']);

            Route::post('/assign-feature-image', [FeaturedImageController::class, 'assign']);

            Route::apiResource('club-gallery', ClubGalleryController::class);

            Route::get('/member-pending-requests', [MemberRequestController::class, 'pendingRequests']);
            Route::post('/assign-club', [MemberRequestController::class, 'assignClub']);
            Route::get('/member-request/{userId}', [MemberRequestController::class, 'getRequestingMember']);
            Route::post('/reject-club-request', [MemberRequestController::class, 'rejectClubRequest']);

            Route::apiResource('saved-library-items', SavedLibraryItemController::class)->only(['index', 'store', 'destroy']);

        });

        // super admin routes
        Route::middleware(['auth', 'role:super_admin'])->prefix('super')->name('super.')->group(function () {
            Route::get('/clubs', [SuperClubController::class, 'index']);
            Route::post('/clubs', [SuperClubController::class, 'store']);
            Route::get('/clubs/{id}', [SuperClubController::class, 'show']);
            Route::put('/clubs/{id}', [SuperClubController::class, 'update']);
            Route::post('/clubs/{id}', [SuperClubController::class, 'update']);
            Route::delete('/clubs/{id}', [SuperClubController::class, 'destroy']);
            Route::post('/clubs/{id}/toggle-status', [SuperClubController::class, 'toggleStatus']);
            Route::get('/clubs/{id}/members', [SuperClubController::class, 'members']);

            Route::get('/dashboard-stats', [SuperProfileController::class, 'dashboardStats']);
            Route::get('/dashboard-calendar', [SuperProfileController::class, 'dashboardCalendar']);
            Route::get('/profiles', [SuperProfileController::class, 'index']);
            Route::get('/profiles/{id}', [SuperProfileController::class, 'show']);
            Route::put('/profiles/{id}', [SuperProfileController::class, 'update']);
            Route::delete('/profiles/{id}', [SuperProfileController::class, 'destroy']);

            Route::get('/roles', [SuperRoleController::class, 'index']);
            Route::post('/roles', [SuperRoleController::class, 'store']);
            Route::put('/roles/{id}', [SuperRoleController::class, 'update']);
            Route::delete('/roles/{id}', [SuperRoleController::class, 'destroy']);
            Route::post('/roles/{id}/toggle-status', [SuperRoleController::class, 'toggleStatus']);

            Route::get('/platform-settings', [SuperPlatformSettingController::class, 'show']);
            Route::put('/platform-settings', [SuperPlatformSettingController::class, 'update']);
        });

        // member routes
        Route::middleware(['auth', 'role:member'])->group(function () {
            Route::post('/join-club', [MemberController::class, 'requestToJoinClub']);
            Route::get('/joined-clubs', [MemberController::class, 'joinedClubs']);

            Route::apiResource('planned-day-outs', PlannedDayOutController::class);

            Route::apiResource('booking', BookingController::class);
            Route::post('booking-extras', [BookingController::class, 'bookingExtras']);

            Route::apiResource('planned-locations', PlannedLocationController::class);

            // gear routes
            Route::get('gear-extras', [GearController::class, 'extras']);
            Route::apiResource('gears', GearController::class);
            Route::apiResource('gear-libraries', GearLibraryController::class);
            Route::apiResource('gear-wishlists', GearWishlistController::class);

            // learning routes
            Route::apiResource('cheat-sheets', CheatSheetController::class);

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

