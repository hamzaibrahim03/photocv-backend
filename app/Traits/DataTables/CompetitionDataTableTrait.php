<?php

namespace App\Traits\DataTables;

use Yajra\DataTables\DataTables;
use App\Models\Competition;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\CompetitionMembersEntry;
use App\Models\Club;
use App\Models\CompetitionEntryScore;

trait CompetitionDataTableTrait
{
    use CommonDataTableTrait;

    /**
     * Get all competition results with filtering, searching, and sorting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
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
            'competitionMembers.entries.scores' => function ($q) {
                $q->with('judge:id,first_name,last_name,email')
                ->select('id', 'entry_id', 'judge_id', 'score', 'comment');
            },
            'competitionMembers.entries',
            'competitionMembers.member:id,first_name,last_name,email'
        ]);

        $competitions = $query->get();

        // Transform data to entry-centric format
        $entries = [];
        foreach ($competitions as $competition) {
            foreach ($competition->competitionMembers as $memberComp) {
                foreach ($memberComp->entries as $entry) {
                    $totalScore = $entry->scores->sum('score');
                    $entries[] = [
                        'entry_id' => $entry->id,
                        'entry_image' => asset('storage/' . $entry->entry_image),
                        'entry_image_title' => $entry->entry_image_title,
                        'member_name' => $memberComp->member->first_name . ' ' . $memberComp->member->last_name,
                        'scores' => $entry->scores->map(function ($score) {
                            return [
                                'judge_name' => $score->judge->first_name . ' ' . $score->judge->last_name,
                                'score' => $score->score,
                                'comment' => $score->comment
                            ];
                        }),
                        'total_score' => $totalScore
                    ];
                }
            }
        }

        return DataTables::of(collect($entries))
            ->addIndexColumn()
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

    /**
     * Get competitions for a judge in a specific club with DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $judgeId
     * @param  int  $clubId
     */
    public function getCompetitionsForJudgeInClubWithDatatable($request, $judgeId, $clubId)
    {
        try {
            $club = Club::findOrFail($clubId);

            // Base query for all competitions in this club
            $baseQuery = Competition::with([
                    'competitionMembers' => function ($q) {
                        $q->select('id', 'comp_id', 'member_id');
                    },
                    'competitionMembers.member:id,first_name,last_name,email',
                    'competitionMembers.entries' => function ($q) {
                        $q->select(
                            'id', 'member_comp_id', 'entry_image', 'entry_image_title',
                            'entry_type', 'position', 'total_score', 'is_published'
                        );
                    }
                ])
                ->where('club_id', $clubId);

            // --- Filters from request ---
            if ($request->filled('search_term')) {
                $baseQuery->where('name', 'LIKE', '%' . $request->search_term . '%');
            }

            if ($request->filled('competition_type_id')) {
                $baseQuery->where('competition_type_id', $request->competition_type_id);
            }

            if ($request->filled('status')) {
                $baseQuery->where('status', $request->status);
            }

            // --- Assigned competitions ---
            $assigned = (clone $baseQuery)
                ->whereHas('judges', fn($q) => $q->where('user_id', $judgeId))
                ->get()
                ->map(fn($c) => array_merge($c->toArray(), ['is_assigned' => 'Yes']));

            // --- Unassigned competitions ---
            $unassigned = (clone $baseQuery)
                ->whereDoesntHave('judges', fn($q) => $q->where('user_id', $judgeId))
                ->get()
                ->map(fn($c) => array_merge($c->toArray(), ['is_assigned' => 'No']));

            // Merge both for DataTables
            $allCompetitions = $assigned->merge($unassigned);

            // Counts
            $totalCompetitions = $allCompetitions->count();
            $assignedCount = $assigned->count();
            $unassignedCount = $unassigned->count();

            // Return DataTables response
            return DataTables::of($allCompetitions)
                ->addColumn('is_assigned', fn($row) => $row['is_assigned'])
                ->addColumn('entries', function ($row) {
                    return collect($row['competition_members'] ?? [])
                        ->flatMap(function ($member) {
                            return collect($member['entries'] ?? [])
                                ->map(function ($entry) use ($member) {
                                    return [
                                        'entry_image' => $entry['entry_image'] ? asset('storage/' . $entry['entry_image']) : null,
                                        'entry_title' => $entry['entry_image_title'],
                                        'entry_type' => $entry['entry_type'],
                                        'position' => $entry['position'],
                                        'total_score' => $entry['total_score'],
                                        'is_published' => $entry['is_published'],
                                        'member' => $member['member'] ?? null,
                                    ];
                                });
                        })
                        ->values()
                        ->toArray();
                })
                ->with([
                    'club' => $club->club_name,
                    'total_competitions' => $totalCompetitions,
                    'assigned_competitions_count' => $assignedCount,
                    'unassigned_competitions_count' => $unassignedCount,
                ])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code'  => $e->getCode() ?: 500
            ], 500);
        }
    }

    /**
     * Get judge scores for a specific competition with DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $competitionId
     * @param  int  $judgeId
     */
    public function getJudgeScoresForCompetitionDatatable($request, $competitionId, $judgeId)
    {
        // Ensure the judge is assigned to this competition
        $competition = Competition::with('judges:id')->findOrFail($competitionId);

        if (! $competition->judges->contains('id', $judgeId)) {
            // DataTables expects JSON, so always return this format
            return DataTables::of(collect([]))
                ->with([
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'error'           => 'You are not a judge or not assigned to this competition.'
                ])
                ->make(true);
        }

        // Base query
        $query = CompetitionMembersEntry::query()
            ->whereHas('competitionMember', function ($q) use ($competitionId) {
                $q->where('comp_id', $competitionId);
            })
            ->with([
                'competitionMember.member:id,username,first_name,last_name,email',
                'scores' => function ($q) use ($judgeId) {
                    $q->where('judge_id', $judgeId);
                }
            ]);

        // ---- search_term (custom) ----
        if ($request->filled('search_term')) {
            $term = trim($request->get('search_term'));
            $query->where(function ($q) use ($term) {
                $q->where('entry_image_title', 'LIKE', "%{$term}%")
                ->orWhereHas('competitionMember.member', function ($q2) use ($term) {
                    $q2->where('username', 'LIKE', "%{$term}%")
                        ->orWhere('email', 'LIKE', "%{$term}%")
                        ->orWhere('first_name', 'LIKE', "%{$term}%")
                        ->orWhere('last_name', 'LIKE', "%{$term}%")
                        ->orWhereRaw("CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,'')) LIKE ?", ["%{$term}%"]);
                });
            });
        }

        // ---- ordering ----
        $sortable = [
            'entry_image_title' => 'competition_members_entries.entry_image_title',
            'total_score'       => 'competition_members_entries.total_score',
            'created_at'        => 'competition_members_entries.created_at',
        ];

        if ($request->has('order') && is_array($request->order) && count($request->order)) {
            $orderColIndex = (int) $request->order[0]['column'];
            $orderDir      = $request->order[0]['dir'] ?? 'asc';
            $columns       = $request->get('columns', []);
            $requestedKey  = $columns[$orderColIndex]['data'] ?? null;

            if ($requestedKey && isset($sortable[$requestedKey])) {
                $query->orderBy($sortable[$requestedKey], $orderDir === 'desc' ? 'desc' : 'asc');
            } else {
                $query->latest('competition_members_entries.created_at');
            }
        } else {
            $query->latest('competition_members_entries.created_at');
        }

        // ---- DataTables response ----
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('member_name', function ($entry) {
                $m = $entry->competitionMember->member ?? null;
                $full = trim(($m->first_name ?? '') . ' ' . ($m->last_name ?? ''));
                return $full !== '' ? $full : ($m->username ?? '');
            })
            ->addColumn('member_email', function ($entry) {
                return $entry->competitionMember->member->email ?? '';
            })
            ->addColumn('score', function ($entry) {
                return optional($entry->scores->first())->score ?? null;
            })
            ->addColumn('position', function ($entry) {
                return optional($entry->scores->first())->position ?? null;
            })
            ->editColumn('entry_image', function ($entry) {
                return $entry->entry_image_url ?? null;
            })
            ->with([
                'progress' => [
                    'scored' => $query->get()->filter(fn($e) => $e->scores->isNotEmpty())->count(),
                    'total'  => $query->count(),
                ],
                'positions' => CompetitionEntryScore::with('entry.competitionMember')
                    ->whereHas('entry.competitionMember', function ($q) use ($competitionId) {
                        $q->where('comp_id', $competitionId);
                    })
                    ->where('judge_id', $judgeId)
                    ->whereNotNull('position') // <-- ignore null positions
                    ->get()
                    ->groupBy('position')
                    ->map->count()
            ])
            ->make(true);
    }
}
