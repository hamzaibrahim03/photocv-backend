<?php

use Illuminate\Support\Facades\Route;


//dd("web");
Route::get('/', function () {
    return view('welcome');
    // dd('1');
});

/*
Route::resource('courses', CourseController::class);
Route::resource('course_modules', CourseModuleController::class);
Route::resource('enrollments', EnrollmentController::class);
Route::resource('class_schedules', ClassScheduleController::class);
Route::resource('live_classes', LiveClassController::class);
*/
