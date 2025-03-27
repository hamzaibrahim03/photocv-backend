<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NoticeService;
use App\Http\Requests\Notice\StoreNoticeRequest;
use App\Http\Requests\Notice\UpdateNoticeRequest;

class NoticeController extends Controller
{
    private $noticeService;

    public function __construct(NoticeService $noticeService)
    {
        $this->noticeService = $noticeService;
    }

    public function index( Request $request )
    {
        return $this->noticeService->allNotices( $request );
    }

    public function show( $id ) {
        return $this->noticeService->showNotice( $id );
    }

    public function store(StoreNoticeRequest $request)
    {
        return $this->noticeService->createNotice( $request->except('images'), $request->file('images'), $request->file('documents'));
    }

    public function update(UpdateNoticeRequest $request, $id)
    {
        return $this->noticeService->updateNotice( $id, $request->except('images'), $request->file('images'), $request->file('documents') );
    }

    public function destroy( $id )
    {
        return $this->noticeService->deleteNotice( $id );
    }
}
