<?php

namespace App\Repositories;

use App\Models\Event;
use App\Models\Club;
use App\Models\User;
use App\Models\Competition;
use App\Models\CompetitionMember;
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
            $competitions = $this->getAllIndexData($request, $query, 'name');
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
            ->first();
    }

    public function joinCompetition($data)
    {
        $response = null;

        try {
            $user = auth()->user();
            $competition = Competition::findOrFail($data['comp_id']);
            $clubIds = $user->clubs->pluck('id')->toArray();

            // Ensure the competition belongs to one of the user's clubs
            if (!in_array($competition->club_id, $clubIds)) {
                $response = CompetitionResponse::error('Unauthorized to join this competition.', 403);
            } else {
                // Check if the user has already joined this competition
                $existingEntry = CompetitionMember::where('comp_id', $competition->id)
                    ->where('member_id', $user->id)
                    ->first();

                if ($existingEntry) {
                    $response = CompetitionResponse::error('You have already joined this competition.', 400);
                } else {
                    // Create a new entry for the user in the competition
                    $entry = new CompetitionMember();
                    $entry->comp_id = $competition->id;
                    $entry->member_id = $user->id;
                    $entry->save();

                    $response = CompetitionResponse::success('Successfully joined the competition.', $entry, 201);
                }
            }
        } catch (\Exception $e) {
            $response = CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }

        return $response;
    }
    
    public function submitCompetitionEntry($data)
    {
        $response = null;

        try {
            $user = auth()->user();
            $competitionMember = CompetitionMember::findOrFail($data['member_comp_id']);

            // Ensure the competition member belongs to the user
            if ($competitionMember->member_id !== $user->id) {
                return CompetitionResponse::error('Unauthorized to submit entry for this competition.', 403);
            }

            // Create a new entry
            $entry = new CompetitionMembersEntry();
            $entry->member_comp_id = $competitionMember->id;
            $entry->entry_type = $data['entry_type'];
            
            // Handle file upload
            if (isset($data['entry_image']) && $data['entry_image']->isValid()) {
                $path = $data['entry_image']->store('competition_entries', 'public');
                $entry->entry_image = $path;
            } else {
                return CompetitionResponse::error('Invalid or missing entry image.', 400);
            }

            $entry->save();

            $response = CompetitionResponse::success('Competition entry submitted successfully.', $entry, 201);
        } catch (\Exception $e) {
            $response = CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }

        return $response;
    }

}
