<?php

namespace App\Repositories\ClubAdmin;

use App\Models\Competition;
use App\Traits\DataTables\CompetitionDataTableTrait;
use App\Http\Responses\CompetitionResponse;
use Illuminate\Support\Facades\DB;
use App\Models\CompetitionMembersEntry;
use App\Models\ClubSeason;
use Carbon\Carbon;

class CompetitionResultRepository implements CompetitionResultRepositoryInterface
{
    use CompetitionDataTableTrait;

    public function index($request, $user)
    {
        try {

            // Base query with eager-loaded relationships
            $query = Competition::with([
                'judgingType',
                'competitionType',
                'resultMethod',
                'votingMethod',
                'competitionCategory',
                'competitionTheme'
            ]);

            // If user is a club admin, filter by their club
            if ($user->hasRole('club_admin')) {
                $clubId = $user->club->id ?? null;
                if ($clubId) {
                    $query->where('club_id', $clubId);
                } else {
                    return CompetitionResponse::error('Club not found for this admin.', 404);
                }
            }

            // Get paginated/searchable DataTable results
            $competitions = $this->getAllCompetitionResults($request, $query, 'name');

            return CompetitionResponse::success('Competitions retrieved successfully.', $competitions);

        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function getCompetitionEntriesWithScores($id, $request, $user)
    {
        try {
            
            $query = Competition::with([
                'judgingType',
                'competitionType',
                'resultMethod',
                'votingMethod',
                'competitionCategory',
                'competitionTheme',
                'judges'
            ])->where('id', $id);

            if ($user->hasRole('club_admin')) {
                $clubId = $user->club->id ?? null;
                if ($clubId) {
                    $query->where('club_id', $clubId);
                } else {
                    return CompetitionResponse::error('Club not found for this admin.', 404);
                }
            }

            $competitionEntries = $this->getCompetitionEntryData($request, $query, 'name');
            return CompetitionResponse::success('Competition entries successfully.', $competitionEntries);

        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function assignPositionsAndPublish($data, $competitionId)
    {
        DB::beginTransaction();
        try {
            $clubId = auth()->user()->club->id ?? null;
            if (!$clubId) {
                return CompetitionResponse::error('Club not found for this admin.', 404);
            }

            foreach ($data['entries'] as $entryData) {
                $entry = CompetitionMembersEntry::where('id', $entryData['entry_id'])
                    ->whereHas('competitionMember', function ($q) use ($competitionId) {
                        $q->where('comp_id', $competitionId);
                    })
                    ->first();

                if ($entry) {
                    // If total_score comes from payload use it, otherwise recalc from judge scores
                    $totalScore = $entryData['total_score'] ?? $entry->scores()->sum('score');

                    $entry->update([
                        'position'     => $entryData['position'] ?? null,
                        'total_score'  => $totalScore,
                        'is_published' => $data['is_published'] ?? false,
                    ]);
                }
            }

            DB::commit();
            return CompetitionResponse::success('Results successfully updated and published.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAllPublishedResults($clubId = null)
    {
        try {

            if(!$clubId) {
                $clubId = auth()->user()->club->id ?? null;
            }
            if (!$clubId) {
                return CompetitionResponse::error('Club not found for this admin.', 404);
            }

            // ✅ Fetch seasons once
            $seasons = ClubSeason::where('club_id', $clubId)
                ->get(['name', 'start_date', 'end_date']);

            $competitions = Competition::with([
                'competitionMembers' => function ($q) {
                    $q->select('id', 'comp_id', 'member_id');
                },
                'competitionMembers.member:id,first_name,last_name,email',
                'competitionMembers.entries' => function ($q) {
                    $q->where('is_published', true)
                    ->select('id', 'member_comp_id', 'entry_image', 'entry_image_title', 'entry_type', 'position', 'total_score', 'is_published')->with([
                        'comments' => function ($cq) {
                            $cq->select(
                                    'id',
                                    'record_id',
                                    'record_type',
                                    'comment_type',
                                    'comment',
                                    'interacted_by',
                                    'is_published',
                                    'created_at'
                                )
                                ->where('record_type', 'competition_entry')
                                ->with('user:id,first_name,last_name,email')
                                ->latest();
                        },
                    ]);
                }
            ])
            ->where('club_id', $clubId)
            ->whereHas('competitionMembers.entries', function ($q) {
                $q->where('is_published', true);
            })
            ->get();

            // ✅ Attach season_name (and optionally season object)
            $competitions->each(function ($comp) use ($seasons) {
                $created = $comp->created_at ? Carbon::parse($comp->created_at)->toDateString() : null;

                $season = $created
                    ? $seasons->first(function ($s) use ($created) {
                        return $created >= $s->start_date && $created <= $s->end_date;
                    })
                    : null;

                // add custom fields to response
                $comp->season_name = $season->name ?? null;

            });

            return CompetitionResponse::success('Club published results fetched successfully.', $competitions);

        } catch (\Exception $e) {
            return CompetitionResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    public function getAllPublishedResultsForHome($clubId = null)
    {
        try {

            if (!$clubId) {
                $clubId = auth()->user()->club->id ?? null;
            }

            if (!$clubId) {
                return CompetitionResponse::error(
                    'Club not found for this admin.',
                    404
                );
            }

            $competitions = Competition::query()
                ->select([
                    'id',
                    'name',
                    'club_id',
                ])
                ->with([
                    'competitionMembers' => function ($memberQuery) {
                        $memberQuery
                            ->select([
                                'id',
                                'comp_id',
                                'member_id',
                            ])
                            ->with([
                                'entries' => function ($entryQuery) {
                                    $entryQuery
                                        ->where('is_published', true)
                                        ->where('position', 1) // ✅ ONLY WINNERS
                                        ->select([
                                            'id',
                                            'member_comp_id',
                                            'entry_image_title',
                                            'entry_image',
                                            'position',
                                            'is_published',
                                        ])
                                        // ->with([
                                        //     'comments' => function ($commentQuery) {
                                        //         $commentQuery
                                        //             ->select([
                                        //                 'id',
                                        //                 'record_id',
                                        //                 'record_type',
                                        //                 'comment_type',
                                        //                 'comment',
                                        //                 'interacted_by',
                                        //                 'created_at',
                                        //             ])
                                        //             ->where('record_type', 'competition_entry')
                                        //             // ->where('comment_type', 'comment')
                                        //             ->with([
                                        //                 'user:id,first_name,last_name,profile_image'
                                        //             ])
                                        //             ->latest();
                                        //             // ->limit(10);
                                        //     },
                                        // ])
                                        ->limit(1);
                                },
                            ]);
                    },
                ])
                ->where('club_id', $clubId)
                ->whereHas('competitionMembers.entries', function ($q) {
                    $q->where('is_published', true)
                    ->where('position', 1);
                })
                ->orderBy('id', 'desc')
                ->limit(3) // ✅ HOME PAGE PREVIEW LIMIT
                ->get();

            /**
             * 🔥 REMOVE MEMBERS WITH EMPTY ENTRIES
             * (must use setRelation to avoid duplicate keys)
             */
            $competitions->each(function ($competition) {
                $competition->setRelation(
                    'competitionMembers',
                    $competition->competitionMembers
                        ->filter(fn ($member) => $member->entries->isNotEmpty())
                        ->values()
                );
            });

            return CompetitionResponse::success(
                'Club published results fetched successfully.',
                $competitions
            );

        } catch (\Exception $e) {
            return CompetitionResponse::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }



}
