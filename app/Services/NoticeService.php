<?php
namespace App\Services;

use App\Repositories\NoticeRepositoryInterface;

class NoticeService
{
    private $noticeRepository;

    public function __construct(NoticeRepositoryInterface $noticeRepository)
    {
        $this->noticeRepository = $noticeRepository;
    }

    public function allNotices( $request )
    {
        return $this->noticeRepository->all( $request, auth()->id() );
    }

    public function showNotice( $id ) {
        return $this->noticeRepository->show( $id, auth()->id() );
    }

    public function createNotice(array $data, $images, $document)
    {
        return $this->noticeRepository->create($data, $images, $document);
    }

    public function updateNotice(int $id, array $data, $images, $document)
    {
        return $this->noticeRepository->update($id, $data, $images, $document);
    }

    public function deleteNotice( $id )
    {
        return $this->noticeRepository->delete($id);
    }

    public function getNoticeExtras( $request )
    {
        return $this->noticeRepository->getNoticeExtras($request);
    }

    public function memberNotices()
    {
        return $this->noticeRepository->memberAll(auth()->id());
    }

    public function memberShowNotice($id)
    {
        return $this->noticeRepository->memberShow($id, auth()->id());
    }

    public function memberNoticeExtras()
    {
        return $this->noticeRepository->memberExtras(auth()->id());
    }
}
