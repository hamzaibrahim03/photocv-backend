<?php

namespace App\Http\Controllers\v1\Photographer;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Services\Profile\ProfileFormatterService;

class PhotographerDashboardController extends Controller
{
    private ProfileFormatterService $profileFormatter;

    public function __construct(ProfileFormatterService $profileFormatter)
    {
        $this->profileFormatter = $profileFormatter;
    }

    /**
     * Everything the photographer dashboard UI needs for the logged-in
     * member's own profile: greeting/stat-card fields, bio, social links,
     * clubs, recent comments, competition entries, awards, and the full
     * paginated-on-the-frontend gallery - same shape SuperProfileController
     * builds for an admin viewing any member, just scoped to the
     * authenticated user instead of a route id, and reachable by any member
     * (not just super_admin).
     */
    public function dashboard()
    {
        $user = auth()->user()->load('roles');
        $profile = $this->profileFormatter->format($user, true);

        return MemberResponse::success('Dashboard retrieved successfully.', $profile);
    }
}
