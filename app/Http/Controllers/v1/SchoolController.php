<?php

namespace App\Http\Controllers\v1;


use App\Http\Requests\SchoolRequest;
use App\Services\SchoolService;
use App\Events\SchoolCreated;
use App\Http\Responses\SchoolResponse;
use App\Http\Requests\UpdateSchoolRequest;
use App\Models\School;
use Illuminate\Http\Request;


class SchoolController extends Controller
{
    protected $schoolService;

    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    public function index(Request $request)
    {
        return $this->schoolService->getAllPaginated($request);


    }

    public function indexold()
    {
        try
        {
            $schools = $this->schoolService->getAllSchools();

            return SchoolResponse::success('All Schools fetched successfully.', $schools, 201);
        }catch(\Exception $e){
            // Return an error response if something goes wrong
            return SchoolResponse::error('Failed to fetch Schools.', $e->getMessage(), 500);
        }

    }

    public function show($id)
    {
        try
        {
            $school = $this->schoolService->getSchoolById($id);

            return SchoolResponse::success('School fetched successfully.', $school, 201);
        }catch(\Exception $e){
            // Return an error response if something goes wrong
            return SchoolResponse::error('Failed to get School.', $e->getMessage(), 500);
        }

    }

    public function store(SchoolRequest $request)
    {
        try {
            // Pass the validated data to the service to create the school
            $school = $this->schoolService->createSchool($request->validated());
            // event(new SchoolCreated($school));

            // Return a success JSON response with the newly created course data
            return SchoolResponse::success('school created successfully.', $school, 201);
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return SchoolResponse::error('Failed to create school.', $e->getMessage(), 500);
        }
    }

    public function update(UpdateSchoolRequest $request, $id)
    {

        try {
            // dd($request);
            $school = School::findOrFail($id);
            $up_school = $this->schoolService->updateSchool($id, $request->validated());
            return SchoolResponse::success('School updated successfully.', $school, 201);
        }catch (\Exception $e) {
            // Return an error response if something goes wrong
            return SchoolResponse::error('Failed to update School.', $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {

            $this->schoolService->deleteSchool($id);
            return SchoolResponse::success('School deleted successfully.' ,'', 201);
        }
        catch (\Exception $e) {
            // Return an error response if something goes wrong
            return SchoolResponse::error('Failed to delete School.', $e->getMessage(), 500);
        }
    }
}

