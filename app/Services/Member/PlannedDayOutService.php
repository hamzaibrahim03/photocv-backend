<?php
namespace App\Services\Member;

use App\Http\Resources\PlannedDayOutResource;
use App\Models\PlannedDayOut;
use App\Repositories\Member\PlannedDayOutRepositoryInterface;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Responses\GenericResponse;
use Illuminate\Http\JsonResponse;

class PlannedDayOutService
{
    private $plannedDayOutRepository;

    public function __construct(PlannedDayOutRepositoryInterface $plannedDayOutRepository)
    {
        $this->plannedDayOutRepository = $plannedDayOutRepository;
    }

    public function listUserPlans(): JsonResponse
    {
        try {
            $plans = $this->plannedDayOutRepository->getAllForUser(auth()->id());
            $response = PlannedDayOutResource::collection($plans)->additional([
                'meta' => [
                    'total' => $plans->total(),
                    'per_page' => $plans->perPage(),
                    'current_page' => $plans->currentPage(),
                    'last_page' => $plans->lastPage(),
                ]
            ]);

            return GenericResponse::success('Planned day outs retrieved successfully.', $response);
        } catch (\Exception $e) {
            return GenericResponse::error('Failed to fetch planned day outs.', ['exception' => $e->getMessage()]);
        }
    }

    public function createPlan(array $data): JsonResponse
    {
        try {
            $data['user_id'] = auth()->id();
            $plan = $this->plannedDayOutRepository->create($data);
            return GenericResponse::success('Planned day out created successfully.', new PlannedDayOutResource($plan));
        } catch (\Exception $e) {
            return GenericResponse::error('Failed to create planned day out.', ['exception' => $e->getMessage()]);
        }
    }

    public function getPlan(PlannedDayOut $plannedDayOut): JsonResponse
    {
        try {
            return GenericResponse::success('Planned day out retrieved successfully.', new PlannedDayOutResource($plannedDayOut));
        } catch (\Exception $e) {
            return GenericResponse::error('Failed to fetch planned day out.', ['exception' => $e->getMessage()]);
        }
    }

    public function updatePlan(PlannedDayOut $plannedDayOut, array $data): JsonResponse
    {
        try {
            $plan = $this->plannedDayOutRepository->update($plannedDayOut, $data);
            return GenericResponse::success('Planned day out updated successfully.', new PlannedDayOutResource($plan));
        } catch (\Exception $e) {
            return GenericResponse::error('Failed to update planned day out.', ['exception' => $e->getMessage()]);
        }
    }

    public function deletePlan(PlannedDayOut $plannedDayOut): JsonResponse
    {
        try {
            $this->plannedDayOutRepository->delete($plannedDayOut);
            return GenericResponse::success('Planned day out deleted successfully.');
        } catch (\Exception $e) {
            return GenericResponse::error('Failed to delete planned day out.', ['exception' => $e->getMessage()]);
        }
    }
}
