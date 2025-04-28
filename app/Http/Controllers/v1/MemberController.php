<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MemberService;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;

class MemberController extends Controller
{
    private $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    public function index( Request $request )
    {
        return $this->memberService->allMembers( $request );
    }

    public function show( $id ) {
        return $this->memberService->showMember( $id );
    }

    public function store(StoreMemberRequest $request)
    {
        return $this->memberService->createMember($request->except('profile_image'), $request->file('profile_image'));
    }

    public function update(UpdateMemberRequest $request, $id)
    {
        return $this->memberService->updateMember( $id, $request->except('profile_image'), $request->file('profile_image') );
    }

    public function destroy( $id )
    {
        return $this->memberService->deleteMember( $id );
    }

    public function uploadGalleryImages(Request $request)
    {
        return $this->memberService->memberGalleryImages( $request->file('images') );
    }
}
