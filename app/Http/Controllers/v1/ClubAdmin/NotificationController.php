<?php

namespace App\Http\Controllers\v1\ClubAdmin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $q = Notification::where('user_id', $request->user()->id)
            ->with(['actor:id,username'])
            ->latest();

        if ($request->filled('category')) $q->where('category', $request->string('category'));
        if ($request->boolean('unread')) $q->whereNull('read_at');
        if ($request->filled('type')) {
            $q->where('type', $request->string('type'));
        }

        return response()->json($q->paginate(20));
    }

    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function markRead(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    public function readAll(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }


    public function filters()
    {
        return response()->json([
            'categories' => [
                [
                    'key'   => 'all',
                    'label' => 'All',
                ],
                [
                    'key'   => 'social',
                    'label' => 'Social',
                ],
                [
                    'key'   => 'profile',
                    'label' => 'Profile & Activity',
                ],
                [
                    'key'   => 'club',
                    'label' => 'Club / Events',
                ],
                [
                    'key'   => 'system',
                    'label' => 'System / Notices',
                ],
            ],

            'types' => [

                // ================= SOCIAL =================
                [
                    'key' => 'photo_liked',
                    'label' => 'Likes on your photos',
                    'category' => 'social',
                ],
                [
                    'key' => 'photo_commented',
                    'label' => 'Comments on your photos',
                    'category' => 'social',
                ],
                [
                    'key' => 'notice_liked',
                    'label' => 'Likes on your notices',
                    'category' => 'social',
                ],
                [
                    'key' => 'notice_commented',
                    'label' => 'Comments on your notices',
                    'category' => 'social',
                ],
                [
                    'key' => 'event_liked',
                    'label' => 'Likes on your events',
                    'category' => 'social',
                ],
                [
                    'key' => 'event_commented',
                    'label' => 'Comments on your events',
                    'category' => 'social',
                ],
                [
                    'key' => 'competition_entry_liked',
                    'label' => 'Likes on your competition entries',
                    'category' => 'social',
                ],
                [
                    'key' => 'competition_entry_commented',
                    'label' => 'Comments on your competition entries',
                    'category' => 'social',
                ],

                // ================= PROFILE & ACTIVITY =================
                [
                    'key' => 'download_requested',
                    'label' => 'Photo download requests',
                    'category' => 'profile',
                ],
                [
                    'key' => 'new_follower',
                    'label' => 'New followers',
                    'category' => 'profile',
                ],
                [
                    'key' => 'connection_request',
                    'label' => 'Connection requests',
                    'category' => 'profile',
                ],

                // ================= CLUB / EVENTS =================
                [
                    'key' => 'competition_entry_added',
                    'label' => 'Competition entry added',
                    'category' => 'club',
                ],
                [
                    'key' => 'competition_results_published',
                    'label' => 'Competition results published',
                    'category' => 'club',
                ],
                [
                    'key' => 'event_reminder',
                    'label' => 'Upcoming event reminder',
                    'category' => 'club',
                ],
                [
                    'key' => 'competition_reminder',
                    'label' => 'Upcoming competition reminder',
                    'category' => 'club',
                ],
                [
                    'key' => 'booking_added_to_calendar',
                    'label' => 'Booking added to calendar',
                    'category' => 'club',
                ],
                [
                    'key' => 'booking_reminder',
                    'label' => 'Upcoming booking reminder',
                    'category' => 'club',
                ],

                // ================= SYSTEM =================
                [
                    'key' => 'admin_notice',
                    'label' => 'Admin notices & announcements',
                    'category' => 'system',
                ],
                [
                    'key' => 'policy_updated',
                    'label' => 'Policy or terms updates',
                    'category' => 'system',
                ],
            ],
        ]);
    }

}
