<?php

namespace App\Repositories\Judge;

interface JudgePanelRepositoryInterface
{
    public function overview($judgeId, $request);
    public function dashboard($judgeId, $request);
    public function clubs($judgeId, $request);
    public function competitions($judgeId, $request);
    public function competition($judgeId, $competitionId);
    public function updateCompetition($judgeId, $competitionId, array $data);
    public function submissions($judgeId, $competitionId, $request);
    public function submission($judgeId, $entryId);
    public function toggleBookmark($judgeId, $entryId, ?bool $isBookmarked = null);
}
