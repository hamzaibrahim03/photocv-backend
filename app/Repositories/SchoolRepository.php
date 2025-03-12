<?php

namespace App\Repositories;

use App\Models\School;
use App\Traits\UtilityTrait;
use Illuminate\Database\Eloquent\Collection;


class SchoolRepository implements SchoolRepositoryInterface
{
    use UtilityTrait;
    protected $school;

    public function __construct(School $school)
    {
        $this->school = $school;

    }

    public function getAll()
    {
        return $this->school->all();
    }

    public function getAllPaginated($request)
    {
        // Get the query to fetch School
        $query = School::query();

        // Use the trait’s pagination logic
        return $this->getAlltraitdata($request, $query);
    }

    protected function applySearchFilter($query, $searchTerm)
    {
        $query->where('name', 'like', "%$searchTerm%")
              ->orWhere('mobile_no', 'like', "%$searchTerm%")
              ->orWhere('email', 'like', "%$searchTerm%");

        return $query;
    }

    public function findById($id)
    {
        return $this->school->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->school->create($data);
    }

    public function update($id, array $data)
    {
        $school = $this->findById($id);
        $school->update($data);
        return $school;
    }

    public function delete($id)
    {
        $school = $this->findById($id);
        $school->delete();
    }
}
