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
        return $this->memberAdminRepository->allEvents( $request );
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

}
