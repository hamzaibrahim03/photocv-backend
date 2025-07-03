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

    public function memberSingleEvent( $id )
    {
        return $this->memberAdminRepository->memberSingleEvent($id);
    }

}
