<?php
namespace App\Services\ClubAdmin;

use App\Repositories\ClubAdmin\ClubNewsRepositoryInterface;

class ClubNewsService
{
    private $clubNewsRepository;

    public function __construct(ClubNewsRepositoryInterface $clubNewsRepository)
    {
        $this->clubNewsRepository = $clubNewsRepository;
    }

    public function allClubNews( $request )
    {
        return $this->clubNewsRepository->all( $request );
    }

    public function showClubNews( $id ) {
        return $this->clubNewsRepository->getNewsById( $id, auth()->id() );
    }

    public function createClubNews(array $data, $file)
    {
        return $this->clubNewsRepository->create($data, $file);
    }

    public function updateClubNews(int $id, array $data, $file)
    {
        return $this->clubNewsRepository->update($id, $data, $file);
    }

    public function deleteClubNews( $id )
    {
        return $this->clubNewsRepository->delete($id);
    }

    public function getClubNewsExtras( $request )
    {
        return $this->clubNewsRepository->getClubNewsExtras($request);
    }
}
