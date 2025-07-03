<?php

namespace App\Traits;

use Yajra\DataTables\DataTables;
use App\Models\Event;
use App\Models\Club;
use App\Models\Comment;
use App\Models\MemberNotice;
use Carbon\Carbon;

trait UtilityTrait
{
    private $default_pagesize = 10;

    public function getAlltraitdata($request, $query)
    {
        // Apply global search filter
        if ($request->has('search_term') && $request->search_term != '') {
           $query =  $this->applySearchFilter($query, $request->search_term);
        }
        // Apply sorting (DataTables automatically sends the column and direction)
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        $request->start = $request->start ?? 0;
        $request->length = $request->length ? $request->length : $this->default_pagesize;

        // Pagination (use skip for offset and take for limit)
        $data = $query->skip((int) $request->start)
                      ->take((int) $request->length)
                      ->get();

        return DataTables::of($data)->addIndexColumn()->make(true);
    }

    public function getAllEventData($request)
    {
        $query = Event::with(['images', 'comments']);

        $club = Club::where('user_id', auth()->id())->first();
        if ($club) {
            $query->where('club_id', $club->id);
        } else {
            return DataTables::of(collect([]))->make(true);
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

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($event) {
                return '<a href="' . route('events.show', $event->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('featured_image', function ($event) {
                return $event->featured_image ? asset('storage/' . $event->featured_image) : null;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function getAllIndexData($request, $query, $search)
    {
        // Apply search filter
        if ($request->has('search_term') && $request->search_term != '') {
            $query->where($search, 'LIKE', '%' . $request->search_term . '%');
        }

        // Sorting
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($event) {
                return '<a href="'.route('events.show', $event->id).'" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('featured_image', function ($event) {
                return $event->featured_image ? asset('storage/' . $event->featured_image) : null;
            })
            ->make(true);
    }

    public function getAllNoticeData($request)
    {
        $query = MemberNotice::with(['files', 'comments']);

        $club = Club::where('user_id', auth()->id())->first();
        if ($club) {
            $query->where('club_id', $club->id);
        } else {
            // Optionally return an empty result if user has no club
            return DataTables::of(collect([]))->make(true);
        }

        // Apply search filter
        if ($request->has('search_term') && $request->search_term != '') {
            $query->where('title', 'LIKE', '%' . $request->search_term . '%');
        }

        // Sorting
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($event) {
                return '<a href="'.route('events.show', $event->id).'" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('featured_image', function ($event) {
                return $event->featured_image ? asset('storage/' . $event->featured_image) : null;
            })
            ->make(true);
    }

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

        // Clone for recent comments before DataTables modifies it
        // $recentEvents = (clone $query)
        //     ->latest('event_date')
        //     ->take(4)
        //     ->with(['comments.user']) // Include user info in comments
        //     ->get();

        // // Format recent comments
        // $recentComments = [];
        // foreach ($recentEvents as $event) {
        //     foreach ($event->comments->take(2) as $comment) { // optional: limit 2 per event
        //         $recentComments[] = [
        //             'event_id' => $event->id,
        //             'event_name' => $event->name,
        //             'comment_id' => $comment->id,
        //             'comment' => $comment->comment,
        //             'user' => $comment->user->username ?? 'Unknown',
        //             'created_at' => $comment->created_at->toDateTimeString(),
        //         ];
        //     }
        // }

        $recentComments = Comment::with('user', 'event')
            ->where('record_type', 'event')
            ->whereHas('event', function ($q) use ($clubIds) {
                $q->whereIn('club_id', $clubIds);
            })
            ->orderBy('created_at', 'desc')
            ->take(6) // adjust the number as needed
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

        $recentEventsList = (clone $query)
            ->latest('event_date') // or 'created_at' if that's more appropriate
            ->take(6)
            ->get(['id', 'name', 'event_date', 'featured_image', 'created_at']); // select only needed fields

        $recentEvents = $recentEventsList->map(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'event_date' => $event->event_date->toDateString(),
                'featured_image' => $event->featured_image
                    ? asset('storage/' . $event->featured_image)
                    : null,
            ];
        });

        // Handle month/year filtering
        $month = $request->input('month');
        $year = $request->input('year');

        $startOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $endOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->endOfMonth()
            : Carbon::now()->endOfMonth();

        // Get events in the selected month for all user's clubs
        $calendarEvents = Event::whereIn('club_id', $clubIds)
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->orderBy('event_date', 'asc')
            ->get(['event_date', 'name'])
            ->map(fn($event) => [
                'date' => $event->event_date->toDateString(),
                'name' => $event->name,
            ]);

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
            'recentEvents' => $recentEvents,
            'calendarEvents' => $calendarEvents,
        ]);
    }


}
