<?php
namespace App\Services\Member;

use App\Repositories\Member\GearRepositoryInterface;
use App\Http\Responses\MemberResponse;
use App\Http\Resources\GearResource;
use App\Models\Catalog;
use App\Models\Gear;

class GearService
{
    private $gearRepository;

    public function __construct(GearRepositoryInterface $gearRepository)
    {
        $this->gearRepository = $gearRepository;
    }

    /**
     * Get all gears of auth member with optional filtering and pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index($request)
    {
        return $this->gearRepository->all($request);
    }

    /**
     * Dropdown data for the gear, gear library and cheat sheet screens.
     */
    public function extras()
    {
        $userGears = Gear::where('user_id', auth()->id());

        return MemberResponse::success('Gear extras retrieved successfully.', [
            'gear_categories'        => Catalog::where('catalog_type', 'gear_category')->get(['id', 'name', 'icon']),
            'cheat_sheet_categories' => Catalog::where('catalog_type', 'cheat_sheet_category')->get(['id', 'name']),
            'systems'                => (clone $userGears)->whereNotNull('system')->distinct()->orderBy('system')->pluck('system'),
            'types'                  => (clone $userGears)->whereNotNull('type')->distinct()->orderBy('type')->pluck('type'),
            'model_names'            => (clone $userGears)->whereNotNull('model_name')->distinct()->orderBy('model_name')->pluck('model_name'),
            'suitability_fields'     => Gear::SUITABILITY_FIELDS,
            'suitability_levels'     => [
                ['min' => 0,  'label' => GearResource::suitabilityLabel(0)],
                ['min' => 20, 'label' => GearResource::suitabilityLabel(20)],
                ['min' => 40, 'label' => GearResource::suitabilityLabel(40)],
                ['min' => 60, 'label' => GearResource::suitabilityLabel(60)],
                ['min' => 80, 'label' => GearResource::suitabilityLabel(80)],
            ],
        ]);
    }

    /**
     * Method to store gears
     * @param mixed $data
     */
    public function store($data)
    {
        return $this->gearRepository->create($data);
    }

    /**
     * Method to show single gears
     * @param mixed $id
     */
    public function show($id)
    {
        $record = $this->gearRepository->find($id);
        return MemberResponse::success('Retrieved successfully.', new GearResource($record));
    }

    /**
     * Method to update gears
     * @param mixed $data
     * @param mixed $id
     */
    public function update($data, $id)
    {
        return $this->gearRepository->update($id, $data);
    }

    /**
     * Method to delete gears
     * @param mixed $id
     */
    public function delete($id)
    {
        return $this->gearRepository->delete($id);
    }
}
