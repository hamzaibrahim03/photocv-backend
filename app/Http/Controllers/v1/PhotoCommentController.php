<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\Comment;
use Illuminate\Http\Request;

class PhotoCommentController extends Controller
{
    /**
     * Store a real comment on a photo.
     *
     * Used by CommentsDrawMember.jsx (src/Ryton and src/club_admin) and
     * CommentsDrawClub.jsx (src/Ryton), which previously POSTed to this
     * same path before the route existed (404, silently swallowed, with a
     * fabricated optimistic comment shown in its place).
     */
    public function store(Request $request, $photoId)
    {
        $photo = Photo::find($photoId);

        if (!$photo) {
            return response()->json([
                'success' => false,
                'message' => 'Photo not found.',
            ], 404);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $user = $request->user();

        $comment = Comment::create([
            'record_id' => $photo->id,
            'record_type' => 'photo',
            'comment_type' => 'comment',
            'comment' => $validated['body'],
            'interacted_by' => $user?->id,
            'is_published' => true,
        ]);

        $comment->loadMissing('user');

        return response()->json([
            'success' => true,
            'message' => 'Comment posted successfully.',
            'data' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'posted_by' => $comment->user->username ?? $user?->username ?? 'Unknown',
                'posted_by_id' => $comment->user->id ?? $user?->id,
                'posted_at' => $comment->created_at->toDateTimeString(),
            ],
        ], 201);
    }
}
