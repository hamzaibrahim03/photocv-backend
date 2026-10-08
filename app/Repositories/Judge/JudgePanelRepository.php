<?php

namespace App\Repositories\Judge;

use App\Http\Responses\GenericResponse;
use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionEntryScore;
use App\Models\CompetitionMembersEntry;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Data for the judge panel screens. A "judge" is any user assigned to a
 * competition through competition_judges; every query is limited to the
 * competitions the logged-in judge is assigned to.
 */
class JudgePanelRepository implements JudgePanelRepositoryInterface
{
    /**
     * Judging dashboard: judge card, stats, calendar and upcoming competitions.
     */
    public function overview($judgeId, $request)
    {
        try {
            return GenericResponse::success('Judge overview fetched successfully.', $this->overviewData($judgeId, $request));
        } catch (Exception $e) {
            return GenericResponse::error($e->getMessage(), $this->status($e));
        }
    }

    /**
     * Full judging dashboard in one call: the overview plus every club that
     * has competitions, each with its competitions nested. `is_assigned`
     * marks the competitions this judge scores.
     */
    public function dashboard($judgeId, $request)
    {
        try {
            $data = $this->overviewData($judgeId, $request);

            $clubs = Club::with([
                    'setting',
                    'competitions' => fn ($q) => $q->with('judges:users.id')->orderBy('start_date'),
                ])
                ->whereHas('competitions')
                ->when($request->filled('club_id'), fn ($q) => $q->where('id', $request->club_id))
                ->when($request->filled('search'), fn ($q) => $q->where('club_name', 'LIKE', '%' . $request->search . '%'))
                ->orderBy('club_name')
                ->paginate((int) $request->input('per_page', 4));

            $competitionIds = $clubs->getCollection()->flatMap(fn ($club) => $club->competitions->pluck('id'))->all();
            $stats = $this->entryStats($competitionIds, $judgeId);

            $clubs->setCollection($clubs->getCollection()->map(function ($club) use ($judgeId, $stats) {
                $competitions = $club->competitions->map(function ($competition) use ($judgeId, $stats) {
                    $isAssigned = $competition->judges->contains('id', $judgeId);

                    return collect($this->competitionCard($competition))->except('club')->all() + [
                        'is_assigned' => $isAssigned,
                        'progress'    => $isAssigned ? ($stats[$competition->id] ?? ['total' => 0, 'scored' => 0]) : null,
                    ];
                })->values();

                return $club->summary() + [
                    'code'                     => $this->clubCode($club->club_name),
                    'about'                    => $club->about,
                    'image_url'                => $club->setting?->header_img_url,
                    'competitions_count'       => $competitions->count(),
                    'judge_competitions_count' => $competitions->where('is_assigned', true)->count(),
                    'competitions'             => $competitions,
                ];
            }));

            $data['clubs'] = $clubs;

            return GenericResponse::success('Judge dashboard fetched successfully.', $data);
        } catch (Exception $e) {
            return GenericResponse::error($e->getMessage(), $this->status($e));
        }
    }

    /**
     * Judge card, stats, calendar and upcoming competitions.
     */
    private function overviewData($judgeId, $request): array
    {
        $judge = User::findOrFail($judgeId);
        $base = $this->judgedCompetitions($judgeId, $request->input('club_id'));

        $next = (clone $base)->where('start_date', '>', now())->orderBy('start_date')->first();

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $calendar = (clone $base)
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->orderBy('start_date')
            ->get(['id', 'club_id', 'name', 'start_date'])
            ->map(fn ($competition) => [
                'id'        => $competition->id,
                'date'      => $competition->start_date->toDateString(),
                'name'      => $competition->name,
                'club_code' => $this->clubCode($competition->club?->club_name),
                'club'      => $competition->club?->summary(),
            ]);

        $upcoming = (clone $base)
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit((int) $request->input('upcoming_limit', 3))
            ->get(['id', 'club_id', 'name', 'start_date', 'featured_image'])
            ->map(fn ($competition) => $this->competitionCard($competition));

        return [
            'judge' => [
                'id'                => $judge->id,
                'first_name'        => $judge->first_name,
                'last_name'         => $judge->last_name,
                'full_name'         => trim($judge->first_name . ' ' . $judge->last_name),
                'profile_image_url' => $judge->profile_image_url,
                'role_label'        => 'Judge',
            ],
            'stats' => [
                'competitions_count'     => (clone $base)->count(),
                'next_competition_days'  => $next ? now()->startOfDay()->diffInDays($next->start_date->copy()->startOfDay(), false) : null,
                'next_competition'       => $next ? $this->competitionCard($next) : null,
            ],
            'calendar' => [
                'month'        => $month,
                'year'         => $year,
                'competitions' => $calendar,
            ],
            'upcoming_competitions' => $upcoming,
        ];
    }

    /**
     * Clubs the judge judges for, with how many of that club's competitions
     * they judge. Paginated for the dashboard cards; also feeds the "All Club" dropdown.
     */
    public function clubs($judgeId, $request)
    {
        try {
            $judged = fn ($q) => $q->whereHas('judges', fn ($j) => $j->where('users.id', $judgeId));

            $clubs = Club::with('setting')
                ->whereHas('competitions', $judged)
                ->withCount(['competitions as judge_competitions_count' => $judged])
                ->when($request->filled('search'), fn ($q) => $q->where('club_name', 'LIKE', '%' . $request->search . '%'))
                ->orderBy('club_name')
                ->paginate((int) $request->input('per_page', 4));

            $clubs->setCollection($clubs->getCollection()->map(fn ($club) => $club->summary() + [
                'code'               => $this->clubCode($club->club_name),
                'about'              => $club->about,
                'image_url'          => $club->setting?->header_img_url,
                'competitions_count' => $club->judge_competitions_count,
            ]));

            return GenericResponse::success('Judge clubs fetched successfully.', $clubs);
        } catch (Exception $e) {
            return GenericResponse::error($e->getMessage(), $this->status($e));
        }
    }

    /**
     * "All Competitions": upcoming ones with the judge's scoring progress,
     * completed ones (paginated) with their awarded entries.
     */
    public function competitions($judgeId, $request)
    {
        try {
            $base = $this->judgedCompetitions($judgeId, $request->input('club_id'))
                ->when($request->filled('search'), fn ($q) => $q->where('name', 'LIKE', '%' . $request->search . '%'));

            $upcoming = (clone $base)
                ->where('status', '!=', 'completed')
                ->orderBy('start_date')
                ->get();

            $completed = (clone $base)
                ->where('status', 'completed')
                ->orderByDesc('start_date')
                ->paginate((int) $request->input('per_page', 8));

            $ids = $upcoming->pluck('id')->merge($completed->getCollection()->pluck('id'))->all();
            $stats = $this->entryStats($ids, $judgeId);
            $winners = $this->awardedEntries($completed->getCollection()->pluck('id')->all());

            $completed->setCollection($completed->getCollection()->map(function ($competition) use ($stats, $winners) {
                $awarded = $winners->get($competition->id, collect());

                return $this->competitionCard($competition) + [
                    'progress'       => $stats[$competition->id] ?? ['total' => 0, 'scored' => 0],
                    'featured_entry' => $awarded->first(),
                    'awarded_entries'=> $awarded->values(),
                ];
            }));

            return GenericResponse::success('Judge competitions fetched successfully.', [
                'total_competitions' => (clone $base)->count(),
                'upcoming'           => $upcoming->map(fn ($competition) => $this->competitionCard($competition) + [
                    'progress' => $stats[$competition->id] ?? ['total' => 0, 'scored' => 0],
                ])->values(),
                'completed'          => $completed,
            ]);
        } catch (Exception $e) {
            return GenericResponse::error($e->getMessage(), $this->status($e));
        }
    }

    /**
     * Full competition details for the view/edit screens.
     */
    public function competition($judgeId, $competitionId)
    {
        try {
            $competition = $this->judgedCompetitions($judgeId)
                ->with([
                    'competitionType:id,name',
                    'judgingType:id,name',
                    'competitionCategory:id,name',
                    'competitionTheme:id,name',
                    'votingMethod:id,name',
                    'resultMethod:id,name',
                    'judges:users.id,first_name,last_name',
                ])
                ->findOrFail($competitionId);

            $stats = $this->entryStats([$competition->id], $judgeId);

            $data = $competition->toArray();
            $data['judges'] = $competition->judges->map(fn ($judge) => [
                'id'   => $judge->id,
                'name' => trim($judge->first_name . ' ' . $judge->last_name),
            ])->values();
            $data['progress'] = $stats[$competition->id] ?? ['total' => 0, 'scored' => 0];
            $data['can_edit'] = true;

            return GenericResponse::success('Competition fetched successfully.', $data);
        } catch (Exception $e) {
            return GenericResponse::error($e->getMessage(), $this->status($e));
        }
    }

    /**
     * Judge's "Basic Information" edit (name, type, description, status).
     */
    public function updateCompetition($judgeId, $competitionId, array $data)
    {
        try {
            $competition = $this->judgedCompetitions($judgeId)->findOrFail($competitionId);

            // DB enum stores "postpond"; accept the correct spelling from clients
            if (($data['status'] ?? null) === 'postponed') {
                $data['status'] = 'postpond';
            }

            $competition->update($data + ['updated_by' => $judgeId]);

            return $this->competition($judgeId, $competition->id);
        } catch (Exception $e) {
            return GenericResponse::error($e->getMessage(), $this->status($e));
        }
    }

    /**
     * Submissions grid: filter All / Scored / Unscored, awarded, bookmarked, search.
     */
    public function submissions($judgeId, $competitionId, $request)
    {
        try {
            $competition = $this->judgedCompetitions($judgeId)->findOrFail($competitionId);

            $mine = fn ($q) => $q->where('judge_id', $judgeId);
            $scored = fn ($q) => $q->where('judge_id', $judgeId)->whereNotNull('score');

            $entries = $this->competitionEntries($competition->id)
                ->with(['competitionMember.member:id,first_name,last_name', 'scores' => $mine])
                ->when($request->input('status') === 'scored', fn ($q) => $q->whereHas('scores', $scored))
                ->when($request->input('status') === 'unscored', fn ($q) => $q->whereDoesntHave('scores', $scored))
                ->when($request->boolean('awarded'), fn ($q) => $q->whereHas('scores', fn ($s) => $mine($s)->whereNotNull('position')))
                ->when($request->boolean('bookmarked'), fn ($q) => $q->whereHas('scores', fn ($s) => $mine($s)->where('is_bookmarked', true)))
                ->when($request->filled('search'), function ($q) use ($request) {
                    $term = '%' . $request->search . '%';
                    $q->where(function ($w) use ($term) {
                        $w->where('entry_image_title', 'LIKE', $term)
                            ->orWhereHas('competitionMember.member', fn ($m) => $m
                                ->where('first_name', 'LIKE', $term)
                                ->orWhere('last_name', 'LIKE', $term));
                    });
                })
                ->orderBy('competition_members_entries.id')
                ->paginate((int) $request->input('per_page', 12));

            $entries->setCollection($entries->getCollection()->map(fn ($entry) => $this->entryCard($entry)));

            $stats = $this->entryStats([$competition->id], $judgeId);

            return GenericResponse::success('Competition submissions fetched successfully.', [
                'competition' => $this->competitionCard($competition),
                'progress'    => $stats[$competition->id] ?? ['total' => 0, 'scored' => 0],
                'submissions' => $entries,
            ]);
        } catch (Exception $e) {
            return GenericResponse::error($e->getMessage(), $this->status($e));
        }
    }

    /**
     * Single submission for the image viewer and scoring panel: image,
     * the judge's score, progress, award counts, and prev/next navigation.
     */
    public function submission($judgeId, $entryId)
    {
        try {
            [$entry, $competition] = $this->judgedEntry($judgeId, $entryId);

            $entry->load([
                'competitionMember.member:id,first_name,last_name',
                'scores' => fn ($q) => $q->where('judge_id', $judgeId),
            ]);

            $ids = $this->competitionEntries($competition->id)
                ->orderBy('competition_members_entries.id')
                ->pluck('competition_members_entries.id')
                ->values();
            $index = $ids->search($entry->id);

            $thumbnails = $this->competitionEntries($competition->id)
                ->whereIn('competition_members_entries.id', $ids->slice(max(0, $index - 2), 5)->all())
                ->orderBy('competition_members_entries.id')
                ->get()
                ->map(fn ($item) => [
                    'id'         => $item->id,
                    'title'      => $item->entry_image_title,
                    'thumb_url'  => $item->entry_image_thumb ?? $item->entry_image_url,
                    'is_current' => $item->id === $entry->id,
                ]);

            $awardCounts = CompetitionEntryScore::where('judge_id', $judgeId)
                ->whereIn('entry_id', $ids)
                ->whereNotNull('position')
                ->selectRaw('position, count(*) as total')
                ->groupBy('position')
                ->pluck('total', 'position');

            $stats = $this->entryStats([$competition->id], $judgeId);

            return GenericResponse::success('Submission fetched successfully.', $this->entryCard($entry) + [
                'image_large_url'    => $entry->entry_image_large ?? $entry->entry_image_url,
                'image_original_url' => $entry->entry_image_original_url ?? $entry->entry_image_url,
                'competition'        => $this->competitionCard($competition),
                'progress'           => $stats[$competition->id] ?? ['total' => 0, 'scored' => 0],
                'award_counts'       => [
                    '1' => (int) ($awardCounts['1'] ?? 0),
                    '2' => (int) ($awardCounts['2'] ?? 0),
                    '3' => (int) ($awardCounts['3'] ?? 0),
                ],
                'navigation' => [
                    'position'    => $index + 1,
                    'total'       => $ids->count(),
                    'previous_id' => $index > 0 ? $ids[$index - 1] : null,
                    'next_id'     => $index < $ids->count() - 1 ? $ids[$index + 1] : null,
                ],
                'thumbnails' => $thumbnails,
            ]);
        } catch (Exception $e) {
            return GenericResponse::error($e->getMessage(), $this->status($e));
        }
    }

    /**
     * Bookmark / un-bookmark an entry without touching its score.
     * With $isBookmarked null the current value is toggled.
     */
    public function toggleBookmark($judgeId, $entryId, ?bool $isBookmarked = null)
    {
        try {
            [$entry] = $this->judgedEntry($judgeId, $entryId);

            $score = CompetitionEntryScore::firstOrNew(['entry_id' => $entry->id, 'judge_id' => $judgeId]);
            $score->is_bookmarked = $isBookmarked ?? !$score->is_bookmarked;
            $score->save();

            return GenericResponse::success(
                $score->is_bookmarked ? 'Submission bookmarked.' : 'Bookmark removed.',
                ['entry_id' => $entry->id, 'is_bookmarked' => (bool) $score->is_bookmarked]
            );
        } catch (Exception $e) {
            return GenericResponse::error($e->getMessage(), $this->status($e));
        }
    }

    /**
     * Competitions the judge is assigned to, optionally for one club.
     */
    private function judgedCompetitions($judgeId, $clubId = null)
    {
        return Competition::query()
            ->whereHas('judges', fn ($q) => $q->where('users.id', $judgeId))
            ->when($clubId, fn ($q) => $q->where('club_id', $clubId));
    }

    /**
     * Entries submitted to a competition.
     */
    private function competitionEntries($competitionId)
    {
        return CompetitionMembersEntry::query()
            ->whereHas('competitionMember', fn ($q) => $q->where('comp_id', $competitionId));
    }

    /**
     * Load an entry and its competition, failing with 404 unless the judge is assigned to it.
     * @return array{0: CompetitionMembersEntry, 1: Competition}
     */
    private function judgedEntry($judgeId, $entryId): array
    {
        $entry = CompetitionMembersEntry::with('competitionMember')->findOrFail($entryId);
        $competition = $this->judgedCompetitions($judgeId)->findOrFail($entry->competitionMember?->comp_id);

        return [$entry, $competition];
    }

    /**
     * Per competition: total entries and how many this judge has scored.
     * @return array<int, array{total:int, scored:int}>
     */
    private function entryStats(array $competitionIds, $judgeId): array
    {
        if (empty($competitionIds)) {
            return [];
        }

        return DB::table('competition_members_entries as e')
            ->join('competition_members as cm', 'cm.id', '=', 'e.member_comp_id')
            ->leftJoin('competition_entry_scores as s', function ($join) use ($judgeId) {
                $join->on('s.entry_id', '=', 'e.id')
                    ->where('s.judge_id', $judgeId)
                    ->whereNotNull('s.score')
                    ->whereNull('s.deleted_at');
            })
            ->whereIn('cm.comp_id', $competitionIds)
            ->groupBy('cm.comp_id')
            ->selectRaw('cm.comp_id, count(distinct e.id) as total, count(distinct s.entry_id) as scored')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->comp_id => ['total' => (int) $row->total, 'scored' => (int) $row->scored]])
            ->all();
    }

    /**
     * Entries with a final position, grouped by competition, best first.
     */
    private function awardedEntries(array $competitionIds)
    {
        if (empty($competitionIds)) {
            return collect();
        }

        return CompetitionMembersEntry::with('competitionMember.member:id,first_name,last_name')
            ->whereHas('competitionMember', fn ($q) => $q->whereIn('comp_id', $competitionIds))
            ->whereNotNull('position')
            ->orderBy('position')
            ->get()
            ->map(fn ($entry) => [
                'competition_id' => $entry->competitionMember->comp_id,
                'id'             => $entry->id,
                'title'          => $entry->entry_image_title,
                'image_url'      => $entry->entry_image_medium ?? $entry->entry_image_url,
                'thumb_url'      => $entry->entry_image_thumb ?? $entry->entry_image_url,
                'position'       => $entry->position,
                'member_name'    => $this->memberName($entry),
            ])
            ->groupBy('competition_id')
            ->map(fn ($items) => $items->take(3)->map(fn ($item) => collect($item)->except('competition_id')));
    }

    private function competitionCard(Competition $competition): array
    {
        return [
            'id'                 => $competition->id,
            'name'               => $competition->name,
            'status'             => $competition->status === 'postpond' ? 'postponed' : $competition->status,
            'start_date'         => $competition->start_date?->toDateTimeString(),
            'featured_image_url' => $competition->featured_image_url,
            'club'               => $competition->club?->summary(),
        ];
    }

    private function entryCard(CompetitionMembersEntry $entry): array
    {
        $score = $entry->scores->first();

        return [
            'id'             => $entry->id,
            'title'          => $entry->entry_image_title,
            'entry_type'     => $entry->entry_type,
            'image_url'      => $entry->entry_image_url,
            'image_medium_url' => $entry->entry_image_medium ?? $entry->entry_image_url,
            'thumb_url'      => $entry->entry_image_thumb ?? $entry->entry_image_url,
            'member'         => [
                'id'   => $entry->competitionMember?->member?->id,
                'name' => $this->memberName($entry),
            ],
            'final_position' => $entry->position,
            'my_score'       => [
                'is_scored'     => $score?->score !== null,
                'score'         => $score?->score,
                'position'      => $score?->position,
                'comment'       => $score?->comment,
                'is_bookmarked' => (bool) $score?->is_bookmarked,
            ],
        ];
    }

    private function memberName(CompetitionMembersEntry $entry): ?string
    {
        $member = $entry->competitionMember?->member;

        return $member ? trim($member->first_name . ' ' . $member->last_name) : null;
    }

    /**
     * "Ryton Camera Club" -> "RCC", used in the dashboard calendar.
     */
    private function clubCode(?string $name): ?string
    {
        if (!$name) {
            return null;
        }

        return collect(preg_split('/\s+/', trim($name)))
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }

    private function status(Exception $e): int
    {
        if ($e instanceof ModelNotFoundException) {
            return 404;
        }

        $code = (int) $e->getCode();

        return $code >= 400 && $code < 600 ? $code : 500;
    }
}
