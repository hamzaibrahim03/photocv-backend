<?php

namespace App\Listeners;

use App\Events\CompetitionEntryAdded;
use App\Services\ClubAdmin\NotificationService;

class CreateCompetitionEntryNotification
{
    public function handle(CompetitionEntryAdded $event): void
    {
        $entry = $event->entry;
        $actor = $event->actor;

        $compMember = $entry->competitionMember;
        if (!$compMember) return;

        $competition = $compMember->competition; // EXACT relation in your CompetitionMember model
        if (!$competition) return;

        $service = app(NotificationService::class);

        // 1) Notify competition creator
        if (!empty($competition->created_by)) {
            $service->create(
                receiverId: (int) $competition->created_by,
                actorId: $actor->id,
                category: 'club',
                type: 'competition_entry_added',
                notifiable: $entry,
                data: [
                    'title' => 'New competition entry',
                    'body'  => "{$actor->username} submitted an entry for {$competition->name}",
                    'competition_id' => $competition->id,
                    'entry_id' => $entry->id,
                    'member_id' => (int) $compMember->member_id,
                ]
            );
        }

        // 2) Optional: notify judges too
        // Comment out if you don't want judges notified on every entry.
        foreach ($competition->judges as $judge) {
            $service->create(
                receiverId: (int) $judge->id,
                actorId: $actor->id,
                category: 'club',
                type: 'competition_entry_added',
                notifiable: $entry,
                data: [
                    'title' => 'New competition entry',
                    'body'  => "{$actor->username} submitted an entry for {$competition->name}",
                    'competition_id' => $competition->id,
                    'entry_id' => $entry->id,
                    'member_id' => (int) $compMember->member_id,
                ]
            );
        }
    }
}
