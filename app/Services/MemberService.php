<?php
namespace App\Services;

use App\Repositories\MemberRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Yajra\DataTables\DataTables;

class MemberService
{
    private $memberRepository;

    public function __construct(MemberRepositoryInterface $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    public function allMembers( $request )
    {
        return $this->memberRepository->all( $request );
    }

    public function showMember( $id ) {
        return $this->memberRepository->show( $id );
    }

    public function createMember(array $data, $files)
    {
        return $this->memberRepository->create($data, $files);
    }

    public function updateMember(int $id, array $data, $files)
    {
        return $this->memberRepository->update($id, $data, $files);
    }

    public function deleteMember( $id )
    {
        return $this->memberRepository->delete($id);
    }

    public function assignMember( $user_id, $club_id )
    {
        return $this->memberRepository->assignClubToUser($user_id, $club_id);
    }

    public function uploadGalleryImages($images) {
        return $this->memberRepository->memberGalleryImages( $images );
    }
}
