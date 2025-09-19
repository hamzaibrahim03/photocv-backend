<?php

namespace App\Repositories\Member;

use App\Models\PlannedDayOut;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PlannedDayOutRepository implements PlannedDayOutRepositoryInterface
{
    public function getAllForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return PlannedDayOut::where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): PlannedDayOut
    {
        return PlannedDayOut::create($data);
    }

    public function find(int $id): ?PlannedDayOut
    {
        return PlannedDayOut::find($id);
    }

    public function update(PlannedDayOut $plannedDayOut, array $data): PlannedDayOut
    {
        $plannedDayOut->update($data);
        return $plannedDayOut;
    }

    public function delete(PlannedDayOut $plannedDayOut): bool
    {
        return $plannedDayOut->delete();
    }

}
