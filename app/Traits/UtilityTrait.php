<?php

namespace App\Traits;

use Yajra\DataTables\DataTables;
use App\Models\Event;
use App\Models\Club;
use App\Models\Comment;
use App\Models\MemberNotice;
use Carbon\Carbon;
use App\Models\Competition;
use App\Models\CompetitionMembersEntry;
use Illuminate\Support\Facades\Storage;

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
        $query = MemberNotice::with(['files', 'comments', 'club', 'member']);

        $club = Club::where('user_id', auth()->id())->first();
        $userId = auth()->id();

        if ($club) {
            $query->where('club_id', $club->id);
        } else {
            // Fall back to notices created by this user (member)
            $query->where('member_id', $userId);
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
            ->addColumn('related_by', function ($notice) {
                if ($notice->club) {
                    return 'Club: ' . optional($notice->club)->name;
                } elseif ($notice->member) {
                    return 'Member: ' . optional($notice->member)->name;
                }
                return 'Unknown';
            })
            ->addColumn('action', function ($notice) {
                return '<a href="' . route('events.show', $notice->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('featured_image', function ($notice) {
                return $notice->featured_image
                    ? asset('storage/' . $notice->featured_image)
                    : null;
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

    public function getAllAdminCompetitionData($request)
    {
        $clubIds = auth()->user()->clubs->pluck('id')->toArray();

        if (empty($clubIds)) {
            return response()->json([
                'dataTable' => [],
                'totalCompetitionCount' => 0,
                'daysUntilNextCompetition' => null,
                'calendarCompetitions' => [],
                'recentCompetitions' => [],
                'recentSubmissions' => [],
            ]);
        }

        // Base query
        $competitionQuery = Competition::whereIn('club_id', $clubIds);

        // Total count
        $totalCompetitionCount = (clone $competitionQuery)->count();

        // Handle ordering/search if needed
        if ($request->has('search_term') && $request->search_term !== '') {
            $competitionQuery->where('name', 'LIKE', '%' . $request->search_term . '%');
        }

        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $competitionQuery->orderBy($column, $direction);
        }

        // DataTables response
        $dataTable = DataTables::of($competitionQuery)
            ->addIndexColumn()
            ->addColumn('action', function ($competition) {
                return '<a href="' . route('competitions.show', $competition->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('featured_image', function ($competition) {
                return $competition->featured_image
                    ? asset('storage/' . $competition->featured_image)
                    : null;
            })
            ->rawColumns(['action'])
            ->toArray();

        // Upcoming competition
        $nextCompetition = (clone $competitionQuery)
            ->whereDate('start_date', '>=', now())
            ->orderBy('start_date')
            ->first();

        $daysUntilNextCompetition = $nextCompetition
            ? now()->diffInDays($nextCompetition->start_date, false)
            : null;

        // Calendar competitions
        $month = $request->input('month');
        $year = $request->input('year');

        $startOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->startOfMonth()
            : now()->startOfMonth();

        $endOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->endOfMonth()
            : now()->endOfMonth();

        $calendarCompetitions = (clone $competitionQuery)
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->orderBy('start_date')
            ->get(['start_date', 'name'])
            ->map(fn($comp) => [
                'date' => $comp->start_date->toDateString(),
                'name' => $comp->name,
            ]);

        // Recent competitions
        $recentCompetitions = (clone $competitionQuery)
            ->latest('start_date')
            ->take(6)
            ->get(['id', 'name', 'start_date', 'featured_image'])
            ->map(function ($comp) {
                return [
                    'id' => $comp->id,
                    'title' => $comp->name,
                    'start_date' => $comp->start_date->toDateString(),
                    'featured_image' => $comp->featured_image
                        ? asset('storage/' . $comp->featured_image)
                        : null,
                ];
            });

        // Recent submissions
        $recentSubmissions = CompetitionMembersEntry::with([
                'competitionMember.competition:id,name,club_id',
                'competitionMember.member:id,username',
            ])
            ->whereHas('competitionMember.competition', fn($q) => $q->whereIn('club_id', $clubIds))
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($entry) {
                $competition = optional($entry->competitionMember)->competition;
                $member = optional($entry->competitionMember)->member;

                return [
                    'member_username' => $member->username ?? 'Unknown',
                    'competition_name' => $competition->name ?? 'Unknown',
                    'entry_image' => $entry->entry_image
                        ? url(Storage::url($entry->entry_image))
                        : null,
                    'submitted_at' => optional($entry->created_at)->toDateTimeString(),
                ];
            });

        return response()->json([
            'dataTable' => $dataTable,
            'totalCompetitionCount' => $totalCompetitionCount,
            'daysUntilNextCompetition' => $daysUntilNextCompetition,
            'calendarCompetitions' => $calendarCompetitions,
            'recentCompetitions' => $recentCompetitions,
            'recentSubmissions' => $recentSubmissions,
        ]);
    }

}
