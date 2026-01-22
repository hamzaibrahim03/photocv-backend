<?php

namespace App\Listeners;

use App\Events\ContentInteracted;
use App\Models\Comment;
use App\Services\ClubAdmin\NotificationService;

class CreateInteractionNotification
{
    public function handle(ContentInteracted $event): void
    {
        $comment = $event->comment;
        $actor   = $event->actor;

        // Only published interactions should notify (optional)
        if ($comment->is_published === false) return;

        // Normalize like types in your project
        $isLike = in_array($comment->comment_type, ['like', 'liking'], true);
        $isComment = $comment->comment_type === 'comment';

        // We only handle like/comment
        if (!$isLike && !$isComment) return;

        $service = app(NotificationService::class);

        // record_type decides which module
        switch ($comment->record_type) {

            case 'notice': {
                $notice = $comment->memberNotice; // your relation exists
                if (!$notice) return;

                // receiver: notice owner
                $receiverId = (int) $notice->member_id;

                $service->create(
                    receiverId: $receiverId,
                    actorId: $actor->id,
                    category: 'social',
                    type: $isLike ? 'notice_liked' : 'notice_commented',
                    notifiable: $notice,
                    data: [
                        'title' => $isLike ? 'New like' : 'New comment',
                        'body'  => $isLike
                            ? "{$actor->username} liked your notice"
                            : "{$actor->username} commented on your notice",
                        'notice_id' => $notice->id,
                        'comment_id' => $comment->id,
                    ]
                );
                return;
            }

            case 'event': {
                $eventModel = $comment->event; // your relation exists (belongsTo Event::class, record_id)
                if (!$eventModel) return;

                // receiver: event creator (created_by)
                $receiverId = (int) $eventModel->created_by;

                $service->create(
                    receiverId: $receiverId,
                    actorId: $actor->id,
                    category: 'social',
                    type: $isLike ? 'event_liked' : 'event_commented',
                    notifiable: $eventModel,
                    data: [
                        'title' => $isLike ? 'New like' : 'New comment',
                        'body'  => $isLike
                            ? "{$actor->username} liked your event"
                            : "{$actor->username} commented on your event",
                        'event_id' => $eventModel->id,
                        'comment_id' => $comment->id,
                    ]
                );
                return;
            }

            case 'competition_entry': {
                $entry = $comment->competitionEntry; // belongsTo CompetitionMembersEntry via record_id
                if (!$entry) return;

                $compMember = $entry->competitionMember; // belongsTo CompetitionMember via member_comp_id
                if (!$compMember) return;

                $receiverId = (int) $compMember->member_id; // entry owner (EXACT)

                $service->create(
                    receiverId: $receiverId,
                    actorId: $actor->id,
                    category: 'social',
                    type: $isLike ? 'competition_entry_liked' : 'competition_entry_commented',
                    notifiable: $entry,
                    data: [
                        'title' => $isLike ? 'New like' : 'New comment',
                        'body'  => $isLike
                            ? "{$actor->username} liked your competition entry"
                            : "{$actor->username} commented on your competition entry",
                        'entry_id' => $entry->id,
                        'competition_id' => (int) $compMember->comp_id,
                        'comment_id' => $comment->id,
                    ]
                );
                return;
            }

            case 'photo': {

                $photo = $comment->photo; // uses the relation above
                if (!$photo) return;

                // Try the most common owner fields.
                // Replace these with the exact correct one from your Photo model.
                $receiverId = (int) ($photo->member_id ?? $photo->user_id ?? $photo->uploaded_by ?? 0);

                if (!$receiverId) return;

                $service->create(
                    receiverId: $receiverId,
                    actorId: $actor->id,
                    category: 'social',
                    type: $isLike ? 'photo_liked' : 'photo_commented',
                    notifiable: $photo,
                    data: [
                        'title' => $isLike ? 'New like' : 'New comment',
                        'body'  => $isLike
                            ? "{$actor->username} liked your photo"
                            : "{$actor->username} commented on your photo",
                        'photo_id' => $photo->id,
                        'comment_id' => $comment->id,
                    ]
                );
                return;
            }



            // Add more modules if needed
            // case 'club_news': ...

            default:
                return;
        }
    }
}
