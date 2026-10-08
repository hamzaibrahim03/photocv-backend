<?php

namespace App\Services\Judge;

use App\Repositories\Judge\JudgePanelRepositoryInterface;

class JudgePanelService
{
    protected $judgePanelRepository;

    public function __construct(JudgePanelRepositoryInterface $judgePanelRepository)
    {
        $this->judgePanelRepository = $judgePanelRepository;
    }

    public function overview($request)
    {
        return $this->judgePanelRepository->overview(auth()->id(), $request);
    }

    public function dashboard($request)
    {
        return $this->judgePanelRepository->dashboard(auth()->id(), $request);
    }

    public function clubs($request)
    {
        return $this->judgePanelRepository->clubs(auth()->id(), $request);
    }

    public function competitions($request)
    {
        return $this->judgePanelRepository->competitions(auth()->id(), $request);
    }

    public function competition($competitionId)
    {
        return $this->judgePanelRepository->competition(auth()->id(), $competitionId);
    }

    public function updateCompetition($competitionId, array $data)
    {
        return $this->judgePanelRepository->updateCompetition(auth()->id(), $competitionId, $data);
    }

    public function submissions($competitionId, $request)
    {
        return $this->judgePanelRepository->submissions(auth()->id(), $competitionId, $request);
    }

    public function submission($entryId)
    {
        return $this->judgePanelRepository->submission(auth()->id(), $entryId);
    }

    public function toggleBookmark($entryId, ?bool $isBookmarked)
    {
        return $this->judgePanelRepository->toggleBookmark(auth()->id(), $entryId, $isBookmarked);
    }
}
