<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Event;
use App\Models\Club;
use App\Models\User;
use App\Models\Competition;
use Carbon\Carbon;
use App\Traits\UtilityTrait;
use App\Http\Responses\CompetitionResponse;
use App\Models\CompetitionMembersEntry;
use Illuminate\Support\Facades\Storage;

class CompetitionRepository implements CompetitionRepositoryInterface
{
    use UtilityTrait;

    public function all($request)
    {
        try {
            $clubId = auth()->user()->club->id;
            $query = Competition::where('club_id', $clubId)->with(['judgingType', 'competitionType', 'resultMethod', 'votingMethod', 'competitionCategory', 'competitionTheme']);
            $competitions = $this->getAllCompetitionsData($request, $query, 'name');
            return CompetitionResponse::success('Competitions retrieved successfully.', $competitions);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    public function show( $id )
    {
        try {
            $competition = Competition::with(['judgingType', 'competitionType', 'resultMethod', 'votingMethod', 'competitionCategory', 'competitionTheme'])->findOrFail($id);

            if (!$competition) {
                return CompetitionResponse::error('Competition not found.', 404);
            }

            // Get logged-in user's club
            $club = Club::where('user_id', auth()->id())->first();

            // Check if the event belongs to the user's club
            if (!$club || $competition->club_id !== $club->id) {
                return CompetitionResponse::error('Unauthorized to view this competition.', 403);
            }

            // Transform the featured_image to full URL
            $competition->featured_image = $competition->featured_image 
                ? asset('storage/' . $competition->featured_image) 
                : null;

            return CompetitionResponse::success('Competition retrieved successfully.', $competition);
        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function create(array $data)
    {
        try {
            $club = Club::where('user_id', auth()->id())->first();
            if ($club) {
                $data['club_id'] = $club->id;
            }

            $competition = Competition::create($data);
            return CompetitionResponse::success('Competition created successfully.', $competition, 201);
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

            $competition->update($data);

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
            ->get();
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

}
