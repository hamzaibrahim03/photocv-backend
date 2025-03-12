<?php

namespace App\Http\Controllers\v1;


use App\Http\Requests\CreateStudentRequest;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\StudentResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    // Create a new student
    public function store(CreateStudentRequest $request)
    {
        try{
            // Get validated data
            $data = $request->validated();

            // Create student
            $student = $this->studentService->createStudent($data);
            

            if(auth()->user()->role->name =='Parent'){
                $data['parent_id'] = auth()->user()->id;
                $data['student_id'] = $student->id;
                // Enroll student in the assigned course            
                $this->studentService->enrollStudentInCourse($data);
            }
            // Return a success JSON response with the newly created course data
            return StudentResponse::success('student created successfully.', $student, 201);
        }catch(\Exception $e){
            // Return an error response if something goes wrong
            return StudentResponse::error('Failed to create student.', $e->getMessage(), 500);
        }


    }

    // Other CRUD operations can be implemented here (show, update, delete)


    public function add_student(CreateStudentRequest $request){

        try{
            // Get validated data
            $data = $request->validated();
            
            // Create student
            $student = $this->studentService->createStudent($data); 
            $data['parent_id'] = auth()->user()->id;
            $data['student_id'] = $student->id;
           
            // Enroll student in the assigned course
            $this->studentService->enrollStudentInCourse( $data);

            // Return a success JSON response with the newly created course data
            return StudentResponse::success('student created successfully.', $student, 201);
        }catch(\Exception $e){
            // Return an error response if something goes wrong
            return StudentResponse::error('Failed to create student.', $e->getMessage(), 500);
        }
    }

}

