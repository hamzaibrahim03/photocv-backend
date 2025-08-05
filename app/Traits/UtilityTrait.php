<?php

namespace App\Traits;

use Yajra\DataTables\DataTables;
use App\Models\Event;
use App\Models\Club;
use App\Models\Comment;
use App\Models\MemberNotice;
use App\Models\MemberNote;
use App\Models\MemberBrand;
use App\Models\MemberAward;
use App\Models\User;
use App\Models\MemberPracticeLog;
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
        $query = Event::with(['eventType', 'eventKind', 'images', 'comments']);

        $club = Club::where('user_id', auth()->id())->first();
        if ($club) {
            $query->where('club_id', $club->id);
        } else {
            return DataTables::of(collect([]))->make(true);
        }

        if ($request->has('event_type_id') && $request->event_type_id != '') {
            $query->where('event_type_id', $request->event_type_id);
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


    public function getAllMemberInterestsBrands($request, $memberId)
    {
        $query = MemberBrand::where('member_id', $memberId);

        // Filter by type
        if ($request->has('type') && in_array($request->type, ['interest', 'brands'])) {
            $query->whereNotNull($request->type);
        }

        // Search term filter
        if ($request->has('search_term') && $request->search_term !== '') {
            $query->where(function ($q) use ($request) {
                $q->where('interest', 'like', '%' . $request->search_term . '%')
                ->orWhere('brands', 'like', '%' . $request->search_term . '%');
            });
        }

        // Order by column if provided (DataTables)
        if ($request->has('order') && count($request->order)) {
            $columnIndex = $request->order[0]['column'];
            $column = $request->columns[$columnIndex]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        // Get results
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($item) {
                return '<a href="' . route('events.show', $item->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('image', function ($item) {
                return $item->image ? asset('storage/' . $item->image) : null;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getDataTableResponse($request, $type)
    {
        $query = MemberPracticeLog::where('member_id', auth()->id())
            ->where('type', $type);

        if ($request->has('search_term') && $request->search_term !== '') {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search_term . '%')
                ->orWhere('description', 'like', '%' . $request->search_term . '%');
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('file', function ($log) {
                return $log->file ? asset('storage/' . $log->file) : null;
            })
            ->addColumn('action', function ($log) {
                if ($log->file) {
                    return '<a href="' . asset('storage/' . $log->file) . '" target="_blank" class="btn btn-sm btn-primary">View</a>';
                }
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getAllMemberNotes($request, $memberId)
    {
        $query = MemberNote::where('member_id', $memberId);

        // Search filter
        if ($request->has('search_term') && $request->search_term != '') {
            $search = $request->search_term;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                ->orWhere('description', 'LIKE', '%' . $search . '%')
                ->orWhere('tags', 'LIKE', '%' . $search . '%')
                ->orWhere('location', 'LIKE', '%' . $search . '%');
            });
        }

        // Filter by notice_type_id
        if ($request->has('notice_type_id') && $request->notice_type_id != '') {
            $query->where('notice_type_id', $request->notice_type_id);
        }

        // Filter by poll
        if ($request->has('poll') && $request->poll != '') {
            $query->where('poll', $request->poll);
        }

        // Filter by urgency_importance
        if ($request->has('urgency_importance') && $request->urgency_importance != '') {
            $query->where('urgency_importance', $request->urgency_importance);
        }

        // Filter by comment_allowed
        if ($request->has('comment_allowed') && $request->comment_allowed != '') {
            $query->where('comment_allowed', $request->comment_allowed);
        }

        // Sorting
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('created_at', 'desc'); // Default sort
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($note) {
                return '<a href="' . route('member-notes.show', $note->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('featured_image', function ($note) {
                return $note->featured_image ? asset('storage/' . $note->featured_image) : null;
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

        if ($request->has('page_type_id') && $request->page_type_id != '') {
            $query->where('page_type_id', $request->page_type_id);
        }

        if ($request->has('news_type_id') && $request->news_type_id != '') {
            $query->where('news_type_id', $request->news_type_id);
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
        $query = MemberNotice::with([ 'noticeType', 'files', 'comments', 'club', 'member',]);

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

    public function getAllCompetitionsData($request, $query, $search)
    {
        // Apply search filter
        if ($request->has('search_term') && $request->search_term != '') {
            $query->where($search, 'LIKE', '%' . $request->search_term . '%');
        }

        // Apply filters
        if ($request->has('print_vs_digital') && $request->print_vs_digital != '') {
            $query->where('print_vs_digital', $request->print_vs_digital);
        }

        if ($request->has('color_vs_mono') && $request->color_vs_mono != '') {
            $query->where('color_vs_mono', $request->color_vs_mono);
        }

        if ($request->has('competition_type_id') && $request->competition_type_id != '') {
            $query->where('competition_type_id', $request->competition_type_id);
        }

        if ($request->has('allowed_image_formats') && $request->allowed_image_formats != '') {
            $query->where('allowed_image_formats', 'LIKE', '%' . $request->allowed_image_formats . '%');
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

    public function getAllMemberIndexData($request)
    {
        $query = User::role('member')->with([
        'galleries' => function ($query) {
            $query->where('is_active', true)
                ->with(['photos' => function ($photoQuery) {
                    $photoQuery->where('is_active', true)
                        ->with(['comments' => function ($q) {
                            $q->where('is_published', true);
                        }]);
                }]);
        }
    ]);

    // Search
    if ($request->filled('search_term')) {
        $query->where('username', 'like', '%' . $request->search_term . '%');
    }

    return DataTables::of($query)
        ->addIndexColumn()

        ->addColumn('gallery_total_photos', function ($member) {
            return $member->galleries->sum(function ($gallery) {
                return $gallery->photos->count();
            });
        })

        ->addColumn('gallery_total_comments', function ($member) {
            return $member->galleries->sum(function ($gallery) {
                return $gallery->photos->sum(function ($photo) {
                    return $photo->comments->count();
                });
            });
        })

        ->addColumn('gallery_total_likes', function ($member) {
            $likes = 0;
            foreach ($member->galleries as $gallery) {
                foreach ($gallery->photos as $photo) {
                    $likes += $photo->comments->where('comment_type', 'liking')->count();
                }
            }
            return $likes;
        })

        ->addColumn('award_winning_photos', function ($member) {
            return MemberAward::whereHas('photo.gallery', function ($query) use ($member) {
                $query->where('uploaded_by', $member->id);
            })->count();
        })

        // ->addColumn('comments_preview', function ($member) {
        //     $comments = [];

        //     foreach ($member->galleries as $gallery) {
        //         foreach ($gallery->photos as $photo) {
        //             foreach ($photo->comments as $comment) {
        //                 if ($comment->comment_type === 'comment') {
        //                     $comments[] = $comment->comment;
        //                     if (count($comments) >= 3) break 3;
        //                 }
        //             }
        //         }
        //     }

        //     return implode('<br>', $comments);
        // })

        // ->rawColumns(['comments_preview'])
        ->make(true);
    }

}
