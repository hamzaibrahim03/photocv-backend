<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MemberService;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Http\Requests\Member\GalleryRequest ;

class MemberController extends Controller
{
    private $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request )
    {
        return $this->memberService->allMembers( $request );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( $id ) {
        return $this->memberService->showMember( $id );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMemberRequest $request)
    {
        return $this->memberService->createMember($request->except('profile_image'), $request->file('profile_image'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMemberRequest $request, $id)
    {
        return $this->memberService->updateMember( $id, $request->except('profile_image'), $request->file('profile_image') );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id )
    {
        return $this->memberService->deleteMember( $id );
    }

    /**
     * Assign a club to a member.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assignClub(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'club_id' => 'required|exists:clubs,id',
        ]);

        $this->memberService->assignMember($validated['user_id'], $validated['club_id']);

        return response()->json(['message' => 'Club assigned successfully']);
    }

    /**
     * Create a new gallery for a member.
     *
     * @param  \App\Http\Requests\Member\GalleryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function createGallery(GalleryRequest $request)
    {
        return $this->memberService->createGallery($request->validated());
    }

    /**
     * Upload gallery images for a member.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadGalleryImages(GalleryRequest $request)
    {
        return $this->memberService->memberGalleryImages( $request->validated() );
    }

    /**
     * Method to post comments or likes on member galleries.
     * @param \App\Http\Requests\Member\StoreMemberRequest $request
     */
    public function postCommentOrLikes(StoreMemberRequest $request)
    {
        return $this->memberService->postCommentOrLikes($request->validated());
    }

    /**
     * Method to get all member galleries.
     * @return \Illuminate\Http\Response
     */
    public function membersGalleries()
    {
        return $this->memberService->membersGalleries();
    }

    /**
     * Method to get member gallery details.
     * @param mixed $memberId
     */
    public function memberGalleryDetails($memberId)
    {
        return $this->memberService->memberGalleryDetails($memberId);
    }

    /**
     * Method to update member profile.
     * @param \App\Http\Requests\Member\UpdateMemberRequest $request
     */
    public function profileUpdate(UpdateMemberRequest $request)
    {
        return $this->memberService->profileUpdate($request->all());
    }
}
