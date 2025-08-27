<?php

namespace App\Traits\DataTables;

use Yajra\DataTables\DataTables;
use App\Models\Competition;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\CompetitionMembersEntry;

trait CompetitionDataTableTrait
{
    use CommonDataTableTrait;

    public function getAllCompetitionResults($request, $query)
    {
        // Search filter
        if ($request->has('search_term') && $request->search_term != '') {
            $query->where('name', 'LIKE', '%' . $request->search_term . '%');
        }

        // Filter by competition type
        if ($request->has('competition_type_id') && $request->competition_type_id != '') {
            $query->where('competition_type_id', $request->competition_type_id);
        }

        // Sorting
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        // Eager load relationships
        $query->with([
            'competitionMembers.entries' => function ($q) {
                $q->select('id', 'member_comp_id', 'entry_image', 'entry_image_title', 'entry_type');
            },
            'competitionMembers.entries.scores' => function ($q) {
                $q->with('judge:id,first_name,last_name,email') // bring judge info
                ->select('id', 'entry_id', 'judge_id', 'score', 'comment');
            },
        ])
        ->withCount([
            'competitionMembers as total_images' => function ($q) {
                $q->join('competition_members_entries as cme', 'competition_members.id', '=', 'cme.member_comp_id');
            }
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('featured_image', function ($competition) {
                return $competition->featured_image
                    ? asset('storage/' . $competition->featured_image)
                    : null;
            })
            ->addColumn('images', function ($competition) {
                return $competition->competitionMembers
                    ->flatMap(function ($member) {
                        return $member->entries->map(function ($entry) {
                            return [
                                'entry_image'   => asset('storage/' . $entry->entry_image),
                                'entry_title'   => $entry->entry_image_title,
                                'entry_type'    => $entry->entry_type,
                                'scores'        => $entry->scores->map(function ($score) {
                                    return [
                                        'id'      => $score->id,
                                        'score'   => $score->score,
                                        'comment' => $score->comment,
                                        'judge'   => $score->judge
                                    ];
                                })
                            ];
                        });
                    })
                    ->values()
                    ->toArray();
            })
            ->addColumn('action', function ($competition) {
                return '<a href="'.route('competitions.show', $competition->id).'" class="btn btn-sm btn-primary">View</a>';
            })
            ->make(true);
    }

    /**
     * Get all competitions data with filtering, searching, and sorting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array  $search
     */
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

    /**
     * Get competition entries data with filtering, searching, and sorting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $searchField
     */
    public function getCompetitionEntryData($request, $query, $searchField = 'name')
    {
        // Apply search filter
        if ($request->has('search_term') && $request->search_term != '') {
            $query->where($searchField, 'LIKE', '%' . $request->search_term . '%');
        }

        // Filter by competition type
        if ($request->has('competition_type_id') && $request->competition_type_id != '') {
            $query->where('competition_type_id', $request->competition_type_id);
        }

        // Sorting
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        // Eager load relationships
        $query->with([
            'competitionMembers' => function ($q) {
                $q->select('id', 'comp_id', 'member_id');
            },
            'competitionMembers.entries',
            'competitionMembers.entries.scores' => function ($q) {
                $q->with('judge:id,first_name,last_name,email')
                // ->where('judge_id', auth()->id())
                ->select('id', 'entry_id', 'judge_id', 'score', 'comment');
            },
            'competitionMembers.member:id,first_name,last_name,email'
        ])
        ->withCount([
            'competitionMembers as total_images' => function ($q) {
                $q->join('competition_members_entries as cme', 'competition_members.id', '=', 'cme.member_comp_id');
            }
        ]);

        // DataTables output
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('featured_image', function ($competition) {
                return $competition->featured_image
                    ? asset('storage/' . $competition->featured_image)
                    : null;
            })
            ->addColumn('images', function ($competition) {
                return $competition->competitionMembers
                    ->flatMap(function ($member) {
                        return $member->entries->map(function ($entry) {
                            return asset('storage/' . $entry->entry_image);
                        });
                    })
                    ->values()
                    ->toArray();
            })
            ->addColumn('action', function ($competition) {
                return '<a href="'.route('competitions.show', $competition->id).'" class="btn btn-sm btn-primary">View</a>';
            })
            ->make(true);
    }

    /**
     * Get all competitions data for admin dashboard with various stats.
     *
     * @param  \Illuminate\Http\Request  $request
     */
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

    /**
     * Get clubs with upcoming competitions for a user.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Http\Request|null  $request
     */
    public function getClubsInfoWithUpcomingCompetitions($user, $request)
    {
        $query = $user->clubs()
            ->withCount([
                'upcomingCompetitions as total_upcoming_competitions'
            ])
            ->with([
                'upcomingCompetitions' => function($q) {
                    $q->orderBy('start_date', 'asc');
                }
            ]);

        return DataTables::of($query)
            ->addColumn('club_name', fn($club) => $club->club_name)
            ->addColumn('total_upcoming', fn($club) => $club->total_upcoming_competitions)
            ->addColumn('next_competition', function($club) {
                $next = $club->upcomingCompetitions->first();
                return $next ? $next->name . ' (' . $next->start_date->format('d M Y') . ')' : '-';
            })
            ->make(true);
    }
}
