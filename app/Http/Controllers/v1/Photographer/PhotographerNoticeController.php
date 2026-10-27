<?php

namespace App\Http\Controllers\v1\Photographer;

use App\Http\Controllers\Controller;
use App\Services\NoticeService;
use App\Http\Requests\Notice\StoreNoticeRequest;
use App\Http\Requests\Notice\UpdateNoticeRequest;

class PhotographerNoticeController extends Controller
{
    private $noticeService;

    public function __construct(NoticeService $noticeService)
    {
        $this->noticeService = $noticeService;
    }

    /**
     * List the authenticated photographer's own notices.
     */
    public function index()
    {
        return $this->noticeService->memberNotices();
    }

    public function show($id)
    {
        return $this->noticeService->memberShowNotice($id);
    }

    public function extras()
    {
        return $this->noticeService->memberNoticeExtras();
    }

    public function store(StoreNoticeRequest $request)
    {
        return $this->noticeService->createNotice($request->except('images'), $request->file('images'), $request->file('documents'));
    }

    public function update(UpdateNoticeRequest $request, $id)
    {
        return $this->noticeService->updateNotice($id, $request->except('images'), $request->file('images'), $request->file('documents'));
    }
}
