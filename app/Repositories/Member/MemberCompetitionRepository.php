<?php

namespace App\Repositories\Member;

use App\Models\Competition;
use App\Models\CompetitionMember;
use App\Traits\UtilityTrait;
use App\Http\Responses\CompetitionResponse;
use App\Models\CompetitionMembersEntry;

class MemberCompetitionRepository implements MemberCompetitionRepositoryInterface
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

    public function getJoinedCompetition()
    {
        $user = auth()->user();

        $competitions = $user->joinedCompetitions()
        ->get()
        ->map(function ($competition) {
            return [
                'competition_member_id' => $competition->pivot->id,
                'name'                  => $competition->name,
            ];
        });

        return CompetitionResponse::success('Member joined competitions.', $competitions, 201);
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
