<?php

namespace App\Traits\DataTables;

use App\Models\Event;
use App\Models\Club;
use App\Models\Comment;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

trait EventDataTableTrait
{
    use CommonDataTableTrait;

    /**
     * Get all events data for the authenticated user's club.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function getAllEventData($request)
    {
        $query = Event::with(['types', 'tags', 'images', 'comments']);

        $club = Club::where('user_id', auth()->id())->first();
        if ($club) {
            $query->where('club_id', $club->id);
        } else {
            return DataTables::of(collect([]))->make(true);
        }

        // Filter by event type (multi-type)
        if ($request->has('event_type_id') && $request->event_type_id != '') {
            $query->whereHas('types', function ($q) use ($request) {
                $q->where('catalog.id', $request->event_type_id);
            });
        }

        // Search
        if ($request->has('search_term') && $request->search_term != '') {
            $query->where('name', 'LIKE', '%' . $request->search_term . '%');
        }

        // Ordering
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($event) {
                return '<a href="' . route('events.show', $event->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('featured_image', function ($event) {
                return $event->featured_image ? asset('storage/' . $event->featured_image) : null;
            })
            ->make(true);
    }

    /**
     * Get all events data for admin dashboard with various stats.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function getAllAdminEventData($request)
    {
        $query = Event::with(['images', 'comments.user']);

        $clubIds = auth()->user()->clubs->pluck('id')->toArray();

        if (!empty($clubIds)) {
            $query->whereIn('club_id', $clubIds);
        } else {
            return response()->json([
                'dataTable' => [],
                'recentComments' => []
            ]);
        }

        if ($request->has('search_term') && $request->search_term != '') {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search_term . '%');
            });
        }

        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        $recentComments = Comment::with('user', 'event')
            ->where('record_type', 'event')
            ->whereHas('event', function ($q) use ($clubIds) {
                $q->whereIn('club_id', $clubIds);
            })
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->map(function ($comment) {
                return [
                    'event_id' => $comment->record_id,
                    'event_name' => $comment->event->name ?? 'Unknown',
                    'comment_id' => $comment->id,
                    'comment' => $comment->comment,
                    'user' => $comment->user->username ?? 'Unknown',
                    'created_at' => $comment->created_at->toDateTimeString(),
                ];
            });

        // Clone the base query for counting total events
        $totalEventCount = (clone $query)->count();

        // Get the next upcoming event based on event_date
        $nextEvent = (clone $query)
            ->whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->first();

        $daysUntilNextEvent = $nextEvent
            ? Carbon::now()->diffInDays(Carbon::parse($nextEvent->event_date), false)
            : null;


        $dataTable = DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($event) {
                return '<a href="' . route('events.show', $event->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('featured_image', function ($event) {
                return $event->featured_image ? asset('storage/' . $event->featured_image) : null;
            })
            ->removeColumn('comments')
            ->rawColumns(['action'])
            ->toArray(); // convert to array for merging

        return response()->json([
            'dataTable' => $dataTable,
            'recentComments' => $recentComments,
            'totalEventCount' => $totalEventCount,
            'daysUntilNextEvent' => $daysUntilNextEvent,
        ]);
    }
}
