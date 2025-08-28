<?php

namespace App\Repositories\Judge;

use App\Models\User;
use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionEntryScore;
use App\Http\Responses\GenericResponse;
use App\Traits\DataTables\CompetitionDataTableTrait;

class JudgeRepository implements JudgeRepositoryInterface
{
    use CompetitionDataTableTrait;

    /**
     * Get posting information for the judge.
     */
    public function getJudgePostingInformation($judgeId)
    {
       try {
            $judge = User::findOrFail($judgeId);

            $data = [
                'total_photos_submitted' => $judge->totalPhotosPosted(),
                'total_likes_received'   => $judge->totalLikesReceived(),
                'total_comments_received'=> $judge->receivedCommentsCount(),
            ];

            return GenericResponse::success('Data fetched successfully.', $data, 200);

        }catch (\Exception $e) {
            return GenericResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Get upcoming competitions assigned to the judge.
     */
    public function getUpcomingCompetitionsForJudge($judgeId)
    {
        try {
            $judge = User::findOrFail($judgeId);
            $competitions = $judge->upcomingCompetitions;
            return GenericResponse::success('Data fetched successfully.', $competitions, 200);
        } catch (\Exception $e) {
            return GenericResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Get clubs with upcoming competitions that the judge is assigned to.
     */
    public function getClubsWithUpcomingCompetitions($judgeId, $request = null)
    {
        try {
            $judge = User::findOrFail($judgeId);
            return $this->getClubsInfoWithUpcomingCompetitions($judge,$request);
        }catch (\Exception $e) {
            return GenericResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    /**
     * Get all recent judging activity by the user within their club.
     */
    public function getAllRecentJudgingByUser($userId)
    {
        try {
            $user = User::with('clubs')->find($userId);

            if (!$user || $user->clubs->isEmpty()) {
                return GenericResponse::error('No clubs found for this user.', 404);
            }

            // get all club IDs the judge belongs to
            $clubIds = $user->clubs->pluck('id')->toArray();

            $scores = CompetitionEntryScore::with([
                'entry' => function ($q) {
                    $q->select('id', 'member_comp_id', 'entry_image', 'entry_image_title', 'entry_type', 'position', 'total_score', 'is_published')
                    ->with([
                        'competitionMember:id,comp_id,member_id',
                        'competitionMember.member:id,first_name,last_name,email',
                        'competitionMember.competition:id,club_id,comp_name'
                    ]);
                },
                'judge:id,first_name,last_name,email'
            ])
            ->where('judge_id', $userId)
            ->whereHas('entry.competitionMember.competition', function ($q) use ($clubIds) {
                $q->whereIn('club_id', $clubIds);
            })
            ->latest()
            ->get();

            return GenericResponse::success('Recent judging by user fetched successfully.', $scores);

        } catch (\Exception $e) {
            return GenericResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function getCompetitionsForJudgeInClub($judgeId, $clubId)
    {
        $club = Club::findOrFail($clubId);

        // Competitions where judge IS assigned
        $assigned = Competition::where('club_id', $clubId)
            ->whereHas('judges', function ($q) use ($judgeId) {
                $q->where('user_id', $judgeId);
            })
            ->get();

        // Competitions where judge is NOT assigned
        $unassigned = Competition::where('club_id', $clubId)
            ->whereDoesntHave('judges', function ($q) use ($judgeId) {
                $q->where('user_id', $judgeId);
            })
            ->get();

        return response()->json([
            'club' => $club->club_name,
            'assigned_competitions' => $assigned,
            'unassigned_competitions' => $unassigned,
        ]);
    }

}
