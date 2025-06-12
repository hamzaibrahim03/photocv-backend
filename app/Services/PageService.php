<?php
namespace App\Services;

use App\Repositories\PagesRepositoryInterface;

class PageService
{
    private $pageRepository;

    public function __construct(PagesRepositoryInterface $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    public function allPages( $request )
    {
        return $this->pageRepository->all( $request );
    }

    public function showPage( $id ) {
        return $this->pageRepository->show( $id );
    }

    public function createPage(array $data, $file)
    {
        return $this->pageRepository->create($data, $file);
    }

    public function updatePage(int $id, array $data, $file)
    {
        return $this->pageRepository->update($id, $data, $file);
    }

    public function deletePage( $id )
    {
        return $this->pageRepository->delete($id);
    }

    public function getPagesExtras( $request )
    {
        return $this->pageRepository->getPagesExtras( $request );
    }
}
