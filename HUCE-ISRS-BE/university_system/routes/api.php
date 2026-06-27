<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – University System
|--------------------------------------------------------------------------
| POST /api/token          -> Xác thực client credentials, trả access_token
| GET  /api/students/{id}  -> Thông tin sinh viên (yêu cầu token)
| GET  /api/students/{id}/courses -> Danh sách môn học (yêu cầu token)
*/

Route::post('/token', [AuthController::class, 'token']);
Route::post('/student/login', [AuthController::class, 'studentLogin']);

Route::middleware('verify.api.token')->group(function () {
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::get('/students/{id}/courses', [StudentController::class, 'courses']);
    Route::get('/students/{id}/courses/semester/{semester_key}', [StudentController::class, 'coursesBySemester']);
    Route::get('/students/{id}/registered-courses/{year}/{semester}', [StudentController::class, 'registeredCoursesByTerm']);
    Route::get('/departments/{id}/lecturers', [StudentController::class, 'departmentLecturers']);
});
