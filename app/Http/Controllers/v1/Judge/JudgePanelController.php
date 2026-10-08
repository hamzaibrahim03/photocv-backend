<?php

namespace App\Http\Controllers\v1\Judge;

use App\Http\Controllers\Controller;
use App\Http\Requests\Judge\JudgeCompetitionUpdateRequest;
use App\Services\Judge\JudgePanelService;
use Illuminate\Http\Request;

class JudgePanelController extends Controller
{
    protected $judgePanelService;

    public function __construct(JudgePanelService $judgePanelService)
    {
        $this->judgePanelService = $judgePanelService;
    }

    /**
     * Judging dashboard: judge card, stats, calendar (?month=&year=), upcoming competitions.
     */
    public function overview(Request $request)
    {
        return $this->judgePanelService->overview($request);
    }

    /**
     * Full judging dashboard: overview plus all clubs with their competitions
     * (?month=&year=&club_id=&search=&page=&per_page=).
     */
    public function dashboard(Request $request)
    {
        return $this->judgePanelService->dashboard($request);
    }

    /**
     * Clubs the judge judges for (?page=&per_page=&search=).
     */
    public function clubs(Request $request)
    {
        return $this->judgePanelService->clubs($request);
    }

    /**
     * Upcoming and completed competitions (?club_id=&search=&page=&per_page=).
     */
    public function competitions(Request $request)
    {
        return $this->judgePanelService->competitions($request);
    }

    /**
     * Competition details.
     */
    public function competition($competitionId)
    {
        return $this->judgePanelService->competition($competitionId);
    }

    /**
     * Update competition basic information.
     */
    public function updateCompetition(JudgeCompetitionUpdateRequest $request, $competitionId)
    {
        return $this->judgePanelService->updateCompetition($competitionId, $request->validated());
    }

    /**
     * Competition submissions (?status=all|scored|unscored&awarded=1&bookmarked=1&search=&page=).
     */
    public function submissions(Request $request, $competitionId)
    {
        return $this->judgePanelService->submissions($competitionId, $request);
    }

    /**
     * Single submission for the viewer / scoring panel.
     */
    public function submission($entryId)
    {
        return $this->judgePanelService->submission($entryId);
    }

    /**
     * Bookmark an entry (send is_bookmarked, or omit it to toggle).
     */
    public function bookmark(Request $request, $entryId)
    {
        $request->validate(['is_bookmarked' => 'nullable|boolean']);

        $isBookmarked = $request->has('is_bookmarked') ? $request->boolean('is_bookmarked') : null;

        return $this->judgePanelService->toggleBookmark($entryId, $isBookmarked);
    }
}
