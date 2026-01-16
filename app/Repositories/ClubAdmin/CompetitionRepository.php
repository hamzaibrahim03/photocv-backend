<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Event;
use App\Models\Club;
use App\Models\User;
use App\Models\Competition;
use Carbon\Carbon;
use App\Traits\DataTables\CompetitionDataTableTrait;
use App\Http\Responses\CompetitionResponse;
use App\Models\CompetitionMembersEntry;
use App\Models\CompetitionGlobalSetting;
use Illuminate\Support\Facades\Storage;
use App\Repositories\ClubAdmin\CompetitionGlobalSettingRepository;
use App\Models\ClubSeason;

class CompetitionRepository implements CompetitionRepositoryInterface
{
    use CompetitionDataTableTrait;

    protected $competitionGlobalSettingRepository;

    public function __construct(CompetitionGlobalSettingRepository $competitionGlobalSettingRepository)
    {
        $this->competitionGlobalSettingRepository = $competitionGlobalSettingRepository;
    }

    public function all($request, $clubId = null)
    {
        try {
            $query = Competition::where('club_id', $clubId)->with([
                'judgingType',
                'competitionType',
                'resultMethod',
                'votingMethod',
                'competitionCategory',
                'competitionTheme',
                'judges',
            ]);

            $competitionArr = [
                'competitions' => $this->getAllCompetitionsData($request, $query, 'name'),
                'globalSettings' => $this->competitionGlobalSettingRepository->getFirst($clubId),
            ];
            
            return CompetitionResponse::success('Competitions retrieved successfully.', $competitionArr);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function show($id, $userId)
    {
        try {
            $competition = Competition::with([
                'judgingType',
                'competitionType',
                'resultMethod',
                'votingMethod',
                'competitionCategory',
                'competitionTheme',
                'judges',
                'competitionMembers.entries.scores.judge',
                'competitionMembers.member',
                'competitionMembers.entries.comments.user',
                'competitionMembers.entries.likes',
            ])->findOrFail($id);

            $club = Club::where('user_id', $userId)->first();

            if (!$club || $competition->club_id !== $club->id) {
                return CompetitionResponse::error(
                    'Unauthorized to view this competition.',
                    403
                );
            }

            // build entries (unchanged)
            $entries = [];
            foreach ($competition->competitionMembers as $memberComp) {
                if (!$memberComp->member) {
                    continue;
                }

                foreach ($memberComp->entries as $entry) {
                    $totalScore = $entry->scores->sum('score');

                    $entries[] = [
                        'entry_id' => $entry->id,

                        // 🔥 USE ACCESSOR (see note below)
                        'entry_image' => asset('storage/' . $entry->entry_image),

                        'entry_image_title' => $entry->entry_image_title,
                        'member_name'       => $memberComp->member->first_name . ' ' . $memberComp->member->last_name,
                        'profile_image_url' => $memberComp->member->profile_image
                            ? asset('storage/' . $memberComp->member->profile_image)
                            : null,

                        'comments' => $entry->comments->map(fn ($c) => [
                            'id' => $c->id,
                            'comment' => $c->comment,
                            'created_at' => $c->created_at,
                            'user' => $c->user ? [
                                'id' => $c->user->id,
                                'first_name' => $c->user->first_name,
                                'last_name' => $c->user->last_name,
                                'email' => $c->user->email,
                            ] : null,
                        ]),

                        // ✅ LIKES (count + optionally who liked)
                        'likes_count' => $entry->likes->count(),
                        'comment_count' => $entry->comments->count(),

                        'exif' => [
                            'camera_model'  => $entry->camera_model,
                            'lens'          => $entry->lens,
                            'focal_length'  => $entry->focal_length,
                            'aperture'      => $entry->aperture,
                            'shutter_speed' => $entry->shutter_speed,
                            'iso'           => $entry->iso,
                            'captured_at'   => $entry->captured_at,
                        ],

                        'metadata' => [
                            'image_width'  => $entry->image_width,
                            'image_height' => $entry->image_height,
                            'mime_type'    => $entry->mime_type,
                            'file_size'    => $entry->file_size,
                            'color_type'   => $entry->color_type,
                            'bit_depth'    => $entry->bit_depth,
                        ],

                        'scores' => $entry->scores->map(fn ($score) => [
                            'judge_name' => $score->judge->first_name . ' ' . $score->judge->last_name,
                            'score'      => $score->score,
                            'comment'    => $score->comment,
                        ]),

                        'is_published' => $entry->is_published,
                        'position'     => $entry->position,
                        'total_score'  => $totalScore,
                    ];
                }
            }

            $competition->entries = $entries;

            $seasons = ClubSeason::where('club_id', $competition->club_id)
                ->get(['name', 'start_date', 'end_date']);

            $created = $competition->created_at
                ? Carbon::parse($competition->created_at)->toDateString()
                : null;

            $season = $created
                ? $seasons->first(function ($s) use ($created) {
                    return $created >= $s->start_date && $created <= $s->end_date;
                })
                : null;

            $competition->season_name = $season->name ?? null;

            return CompetitionResponse::success(
                'Competition retrieved successfully.',
                $competition
            );

        } catch (\Exception $e) {
            return CompetitionResponse::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }


    public function create(array $data)
    {
        try {
            // Attach club_id based on logged in user
            $club = Club::where('user_id', auth()->id())->first();
            if ($club) {
                $data['club_id'] = $club->id;
            }

            $data['created_by'] = auth()->id();

            // Create competition
            $competition = Competition::create($data);

            // Attach judges if provided
            if (!empty($data['judges'])) {
                $judges = is_array($data['judges'])
                    ? $data['judges']
                    : explode(',', $data['judges']);

                $competition->judges()->sync($judges);
            }

            return CompetitionResponse::success(
                'Competition created successfully.',
                $competition->load('judges'),
                201
            );
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function update($id, array $data)
    {
        try {
            $competition = Competition::findOrFail($id);

            //Ensure the authenticated user's club owns this competition
            $club = Club::where('user_id', auth()->id())->first();
            if ($club && $competition->club_id !== $club->id) {
                return CompetitionResponse::error('Unauthorized to update this competition.', 403);
            }
            
            $data['updated_by'] = auth()->id();

            // Update competition fields except judges
            $competition->update(collect($data)->except('judges')->toArray());

            // If judges are provided, sync them
            if (isset($data['judges']) && is_array($data['judges'])) {
                $competition->judges()->sync($data['judges']);
            }

            // Reload judges relation for response
            $competition->load('judges');

            return CompetitionResponse::success('Competition updated successfully.', $competition);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function delete($id)
    {
        try {
            $competition = Competition::findOrFail($id);

            // Restrict deletion to the competition's owning club
            $club = Club::where('user_id', auth()->id())->first();
            if ($club && $competition->club_id !== $club->id) {
                return CompetitionResponse::error('Unauthorized to delete this competition.', 403);
            }

            $competition->delete();

            return CompetitionResponse::success('Competition deleted successfully.');
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function getCompetitionExtras($request)
    {
        $club = Club::where('user_id', auth()->id())->first();

        if (!$club) {
            return CompetitionResponse::error('No club found for the current user.', 404);
        }

        $clubId = $club->id;

        // Month and Year logic
        $month = $request->input('month');
        $year = $request->input('year');

        $startOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $endOfMonth = $month && $year
            ? Carbon::createFromDate($year, $month, 1)->endOfMonth()
            : Carbon::now()->endOfMonth();


        // Random competitions
        $randomCompetitions = Competition::select('id', 'name', 'start_date', 'featured_image')
            ->where('club_id', $clubId)
            ->inRandomOrder()
            ->take(5)
            ->get();

        // Upcoming competition (closest future competition)
        $upcomingCompetition = $this->getUpcomingCompetitions($clubId);

        $upcoming = $upcomingCompetition
            ? ['remaining_days' => Carbon::now()->startOfDay()->diffInDays(Carbon::parse($upcomingCompetition->start_date)->startOfDay(), false)]
            : null;

        // Count of members in the club
        $totalMemberCount = User::whereHas('clubs', function ($query) use ($clubId) {
            $query->where('club_id', $clubId);
        })
        ->count();

        $recentSubmissions = CompetitionMembersEntry::with([
            'competitionMember.competition:id,name',
            'competitionMember.member:id,username'
        ])
        ->latest()
        ->take(4)
        ->get()
        ->map(function ($entry) {
            $competition = $entry->competitionMember->competition ?? null;
            $member = $entry->competitionMember->member ?? null;

            return [
                'member_username' => $member->username ?? 'Unknown',
                'competition_name' => $competition->name ?? 'Unknown',
                'entry_image' => url(Storage::url($entry->entry_image)),
                'submitted_at' => optional($entry->created_at)->toDateTimeString(),
            ];
        });

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $competitionCountThisMonth = Competition::where('club_id', $clubId)
            ->whereDate('start_date', '>=', $today)
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->count();

        // Final response
        return [
            'data' => [
                'random_competitions' => $randomCompetitions,
                'upcoming_competition' => $upcoming,
                'total_member_count' => $totalMemberCount,
                'recent_submissions' => $recentSubmissions,
                'current_month_competition_count' => $competitionCountThisMonth,
                'calendar' => $this->getCalenderCompetitionData($clubId, $startOfMonth, $endOfMonth),
            ],
        ];
    }

    public function getCalenderCompetitionData($clubId, $startOfMonth, $endOfMonth)
    {
        // Monthly calendar data
        $events = Event::where('club_id', $clubId)
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->orderBy('event_date', 'asc')
            ->get(['event_date', 'name'])
            ->map(fn($e) => [
                'date' => $e->event_date->toDateString(),
                'name' => $e->name,
            ]);

        $competitions = Competition::where('club_id', $clubId)
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->orderBy('start_date', 'asc')
            ->get(['start_date', 'name'])
            ->map(fn($c) => [
                'date' => $c->start_date->toDateString(),
                'name' => $c->name,
            ]);

        return [
            'events' => $events,
            'competitions' => $competitions,
        ];
    }

    public function getUpcomingCompetitions($clubId)
    {
        return Competition::where('club_id', $clubId)
            ->whereDate('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->first();
    }

    public function getCompetitionResults()
    {
        return Competition::with([
            'competitionMembers.entries' => function ($q) {
                $q->select('id', 'member_comp_id', 'entry_image');
            }
        ])
        ->withCount([
            'competitionMembers as total_images' => function ($query) {
                $query->join('competition_members_entries as cme', 'competition_members.id', '=', 'cme.member_comp_id');
            }
        ])
        ->get()
        ->map(function ($competition) {
            // Flatten all entries to get image URLs
            $images = $competition->competitionMembers
                ->flatMap(function ($member) {
                    return $member->entries->map(function ($entry) {
                        return asset('storage/' . $entry->entry_image);
                    });
                })
                ->values()
                ->toArray();

            return [
                'id' => $competition->id,
                'name' => $competition->name,
                'description' => $competition->description,
                'total_images' => $competition->total_images,
                'images' => $images,
            ];
        });
    }

    public function getLatestCompetitionsByLimit(int $clubId, ?int $limit = 3)
    {
        $query = Competition::query()
            ->where('club_id', $clubId)
            ->whereDate('start_date', '>=', now())
            ->with([
                'judges:id,first_name,last_name,email',
            ])
            ->orderBy('start_date', 'asc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }



    public function getCompetitionsByLimit(int $clubId, ?int $limit = 3)
    {
        $query = Competition::where('club_id', $clubId)->with('judges')
            ->orderBy('start_date', 'asc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }


    /**
     * Method to return events and competitions for calander
     * @param mixed $clubId
     * @return array[][][]
     */
    public function getCalenderCompetitionAndEvent($clubId)
    {
        $startOfMonth = now()->startOfMonth();

        // Fetch events
        $events = Event::where('club_id', $clubId)
            ->whereDate('event_date', '>=', $startOfMonth)
            ->orderBy('event_date', 'asc')
            ->get(['event_date', 'name'])
            ->map(fn($e) => [
                'date' => $e->event_date->toDateString(),
                'name' => $e->name,
                'month' => $e->event_date->format('Y-m'),
            ]);

        // Fetch competitions
        $competitions = Competition::where('club_id', $clubId)
            ->whereDate('start_date', '>=', $startOfMonth)
            ->orderBy('start_date', 'asc')
            ->get(['start_date', 'name'])
            ->map(fn($c) => [
                'date' => $c->start_date->toDateString(),
                'name' => $c->name,
                'month' => $c->start_date->format('Y-m'),
            ]);

        // Group by month
        $grouped = [];

        foreach ($events as $event) {
            $month = $event['month'];
            $grouped[$month]['events'][] = [
                'date' => $event['date'],
                'name' => $event['name'],
            ];
        }

        foreach ($competitions as $comp) {
            $month = $comp['month'];
            $grouped[$month]['competitions'][] = [
                'date' => $comp['date'],
                'name' => $comp['name'],
            ];
        }

        return $grouped;
    }


    /**
     * Method to get latest competitions for home page
     * @param int $clubId
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLatestCompetitionsForHome(int $clubId, ?int $limit = 3)
    {
        $query = Competition::query()
            ->select([
                'id',
                'featured_image',
                'name',
                'start_date',
                'status',
            ])
            ->where('club_id', $clubId)
            ->whereDate('start_date', '>=', now())
            ->with([
                'judges'
            ])
            ->orderBy('start_date', 'asc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

}
