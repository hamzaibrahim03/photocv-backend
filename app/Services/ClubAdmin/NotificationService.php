<?php

namespace App\Services\ClubAdmin;

use App\Models\Notification;

class NotificationService
{
    public function create(
        int $receiverId,
        ?int $actorId,
        string $category,
        string $type,
        $notifiable = null,
        array $data = []
    ): ?Notification {
        if ($actorId && $receiverId === $actorId) return null; // no self-notify

        return Notification::create([
            'user_id' => $receiverId,
            'actor_id' => $actorId,
            'category' => $category,
            'type' => $type,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable?->id,
            'data' => $data,
        ]);
    }
}
