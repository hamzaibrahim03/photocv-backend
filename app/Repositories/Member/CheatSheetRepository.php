<?php

namespace App\Repositories\Member;

use App\Http\Resources\CheatSheetResource;
use App\Http\Responses\MemberResponse;
use App\Models\CheatSheet;
use App\Traits\DataTables\GearDataTableTrait;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CheatSheetRepository implements CheatSheetRepositoryInterface
{
    use GearDataTableTrait;

    /**
     * Get all cheat sheets of auth member with optional category/search filters.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function all($request)
    {
        try {
            $sheets = $this->getMemberCheatSheets($request, auth()->id());
            return MemberResponse::success('Cheat sheets retrieved successfully.', $sheets);
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Method to find single cheat sheet of auth member
     * @param mixed $id
     * @return CheatSheet
     */
    public function find($id)
    {
        return CheatSheet::with('category')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
    }

    /**
     * Method to store cheat sheet
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(array $data)
    {
        try {
            $data['user_id'] = auth()->id();

            if (isset($data['reference_image']) && $data['reference_image'] instanceof UploadedFile) {
                $data['reference_image'] = $data['reference_image']->store('cheat_sheets', 'public');
            }

            $sheet = CheatSheet::create($data);

            return MemberResponse::success('Cheat sheet created successfully.', new CheatSheetResource($sheet->load('category')), 201);
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Method to update cheat sheet
     * @param mixed $id
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, array $data)
    {
        try {
            $sheet = $this->find($id);

            if (isset($data['reference_image']) && $data['reference_image'] instanceof UploadedFile) {
                if ($sheet->reference_image) {
                    Storage::disk('public')->delete($sheet->reference_image);
                }
                $data['reference_image'] = $data['reference_image']->store('cheat_sheets', 'public');
            }

            $sheet->update($data);

            return MemberResponse::success('Cheat sheet updated successfully.', new CheatSheetResource($sheet->fresh('category')));
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Method to delete cheat sheet
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $sheet = $this->find($id);

            if ($sheet->reference_image) {
                Storage::disk('public')->delete($sheet->reference_image);
            }

            $sheet->delete();

            return MemberResponse::success('Cheat sheet deleted successfully.', null);
        } catch (Exception $e) {
            return MemberResponse::error($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }
}
