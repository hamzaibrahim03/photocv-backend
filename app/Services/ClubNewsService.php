<?php
namespace App\Services;

use App\Repositories\ClubNewsRepositoryInterface;
use App\Models\ClubNews;
use Illuminate\Database\Eloquent\Collection;
use Yajra\DataTables\DataTables;

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
        return $this->clubNewsRepository->show( $id );
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
}
