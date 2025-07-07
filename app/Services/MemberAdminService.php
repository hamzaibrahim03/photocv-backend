<?php
namespace App\Services;

use App\Repositories\MemberAdminRepositoryInterface;

class MemberAdminService
{
    private $memberAdminRepository;

    public function __construct(MemberAdminRepositoryInterface $memberAdminRepository)
    {
        $this->memberAdminRepository = $memberAdminRepository;
    }

    /**
     * Get all members with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function memberEvents( $request )
    {
        return [
            'events' => $this->memberAdminRepository->allEvents( $request ),
            'calander_events' => $this->memberAdminRepository->getMemberEventCalanderDetails($request),
            'recent_events' => $this->memberAdminRepository->getMemberMoreEvents(),
        ];
    }

    /**
     * Method to get single event details for member admin with additional information
     * @param mixed $id
     */
    public function memberSingleEvent( $id )
    {
        return $this->memberAdminRepository->memberSingleEvent($id);
    }

    /**
     * Method to load all member admin competitions with additional information
     * @param mixed $request
     */
    public function memberCompetitions($request)
    {
        return $this->memberAdminRepository->allCompetitions($request);
    }

    /**
     * Method to return single competition information with additional data
     * @param mixed $id
     */
    public function memberSingleCompetition($id)
    {
        return $this->memberAdminRepository->memberSingleCompetition($id);
    }

    /**
     * Method to get gallery and notices information
     * @return void
     */
    public function memberProfilePostsView()
    {
        return [
            'notices' => $this->memberAdminRepository->getMemberNotices(),
            'recent_comments_and_interactions' => $this->memberAdminRepository->getMemberNoticesRecentComments(),
            'gallery' => $this->memberAdminRepository->getMemberGallery(),
        ];
    }

    /**
     * Method to get member dashboard profile information
     * @return array{gallery: mixed, member_info: mixed, my_awards: mixed, profile_extras: mixed, recent_comments_and_interactions: mixed, recent_competition_submissions: mixed}
     */
    public function memberAdminProfile()
    {
        return [
            'member_info' => $this->memberAdminRepository->getMemberInformation(),
            'recent_competition_submissions' => $this->memberAdminRepository->getMyRecentSubmissions(),
            'recent_comments_and_interactions' => $this->memberAdminRepository->getMemberNoticesRecentComments(),
            'my_awards' => $this->memberAdminRepository->getMemberAwards(),
            'gallery' => $this->memberAdminRepository->getMemberGallery(),
            'profile_extras' => $this->memberAdminRepository->getMemberProfileExtras(),
        ];
    }

    public function memberAdminProfilePortfolio()
    {
        return [
            'member_info' => $this->memberAdminRepository->getMemberInformation(),
            'recent_comments_and_interactions' => $this->memberAdminRepository->getMemberNoticesRecentComments(),
            'gallery' => $this->memberAdminRepository->getMemberGallery(),
        ];
    }

}
