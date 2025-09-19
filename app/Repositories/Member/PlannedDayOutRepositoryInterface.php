<?php

namespace App\Repositories\Member;

use App\Models\PlannedDayOut;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PlannedDayOutRepositoryInterface
{
    public function getAllForUser(int $userId, int $perPage = 10): LengthAwarePaginator;
    public function find(int $id): ?PlannedDayOut;
    public function create(array $data): PlannedDayOut;
    public function update(PlannedDayOut $plannedDayOut, array $data): PlannedDayOut;
    public function delete(PlannedDayOut $plannedDayOut): bool;
}
