<?php
namespace App\Services\Member;

use App\Repositories\Member\CheatSheetRepositoryInterface;
use App\Http\Responses\MemberResponse;
use App\Http\Resources\CheatSheetResource;

class CheatSheetService
{
    private $cheatSheetRepository;

    public function __construct(CheatSheetRepositoryInterface $cheatSheetRepository)
    {
        $this->cheatSheetRepository = $cheatSheetRepository;
    }

    /**
     * Get all cheat sheets of auth member with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index($request)
    {
        return $this->cheatSheetRepository->all($request);
    }

    /**
     * Method to store cheat sheets
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->cheatSheetRepository->create($data);
    }

    /**
     * Method to show single cheat sheets
     * @param mixed $id
     */
    public function show($id)
    {
        $record = $this->cheatSheetRepository->find($id);
        return MemberResponse::success('Retrieved successfully.', new CheatSheetResource($record));
    }

    /**
     * Method to update cheat sheets
     * @param mixed $data
     * @param mixed $id
     */
    public function update($data, $id)
    {
        return $this->cheatSheetRepository->update($id, $data);
    }

    /**
     * Method to delete cheat sheets
     * @param mixed $id
     */
    public function delete($id)
    {
        return $this->cheatSheetRepository->delete($id);
    }
}
