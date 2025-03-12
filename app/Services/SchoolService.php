<?php

namespace App\Services;

use App\Repositories\SchoolRepositoryInterface;

class SchoolService
{
    protected $schoolRepository;

    public function __construct(SchoolRepositoryInterface $schoolRepository)
    {
        $this->schoolRepository = $schoolRepository;
    }

    public function getAllSchools()
    {
        return $this->schoolRepository->getAll();
    }

    public function getAllPaginated($request)
    {
        return $schools = $this->schoolRepository->getAllPaginated($request);
    }

    public function getSchoolById($id)
    {
        return $this->schoolRepository->findById($id);
    }

    public function createSchool($data)
    {
        return $this->schoolRepository->create($data);
    }

    public function updateSchool($id, $data)
    {
        return $this->schoolRepository->update($id, $data);
    }

    public function deleteSchool($id)
    {
        $this->schoolRepository->delete($id);
    }
}
