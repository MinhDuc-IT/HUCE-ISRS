<?php

use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Admin\RemedialRegistrationController as AdminRemedialRegistrationController;
use App\Http\Controllers\Admin\RemedialTermController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SystemConfigurationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Department\ProfileController as DepartmentProfileController;
use App\Http\Controllers\Department\RemedialRegistrationController as DepartmentRemedialRegistrationController;
use App\Http\Controllers\Department\SubjectAssignmentController as DepartmentSubjectAssignmentController;
use App\Http\Controllers\Department\SummaryEmailController;
use App\Http\Controllers\Student\EligibleSubjectController;
use App\Http\Controllers\Student\RemedialRegistrationController as StudentRemedialRegistrationController;
use App\Http\Controllers\Student\RemedialTermController as StudentRemedialTermController;
use App\Http\Controllers\Student\TermRegisteredSubjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. Public – Auth (không cần token)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

/*
|--------------------------------------------------------------------------
| 2–4. Protected – Auth session + theo vai trò
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    /*
    | 2. Admin (role:admin)
    */
    Route::middleware('role:admin')->group(function () {

        Route::prefix('admin/system-configurations')->group(function () {
            Route::get('/', [SystemConfigurationController::class, 'index']);
            Route::post('/', [SystemConfigurationController::class, 'update']);
            Route::post('/create', [SystemConfigurationController::class, 'store']);
            Route::patch('/{key}', [SystemConfigurationController::class, 'updateItem']);
            Route::delete('/{key}', [SystemConfigurationController::class, 'destroy']);
        });

        Route::prefix('admin/statistics/terms')->group(function () {
            Route::get('/', [StatisticsController::class, 'listTerms']);
            Route::get('/summaries', [StatisticsController::class, 'listTermSummaries']);
            Route::get('/{id}', [StatisticsController::class, 'termStatistics']);
            Route::get('/{id}/teaching-payments', [StatisticsController::class, 'teachingPaymentStatistics']);
            Route::get('/{id}/teaching-payments/export', [StatisticsController::class, 'exportTeachingPayments']);
        });

        Route::prefix('admin/users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::patch('/{user}', [UserController::class, 'update']);
            Route::delete('/{user}', [UserController::class, 'destroy']);
        });

        Route::prefix('admin/remedial-terms')->group(function () {
            Route::get('/', [RemedialTermController::class, 'index']);
            Route::post('/', [RemedialTermController::class, 'store']);
            Route::get('/{id}/statistics', [StatisticsController::class, 'termStatistics']);
            Route::get('/{id}', [RemedialTermController::class, 'show']);
            Route::patch('/{id}', [RemedialTermController::class, 'update']);
            Route::patch('/{id}/status', [RemedialTermController::class, 'updateStatus']);
            Route::delete('/{id}', [RemedialTermController::class, 'destroy']);
        });

        Route::prefix('admin/departments')->group(function () {
            Route::get('/', [AdminDepartmentController::class, 'index']);
            Route::post('/', [AdminDepartmentController::class, 'store']);
            Route::get('/{id}', [AdminDepartmentController::class, 'show']);
            Route::patch('/{id}', [AdminDepartmentController::class, 'update']);
            Route::delete('/{id}', [AdminDepartmentController::class, 'destroy']);
            Route::post('/{id}/send-email', [AdminDepartmentController::class, 'sendSummaryEmail']);
        });

        Route::prefix('admin/subjects')->group(function () {
            Route::get('/', [SubjectController::class, 'index']);
            Route::post('/', [SubjectController::class, 'store']);
            Route::get('/{id}', [SubjectController::class, 'show']);
            Route::patch('/{id}', [SubjectController::class, 'update']);
            Route::delete('/{id}', [SubjectController::class, 'destroy']);
        });

        Route::get('admin/remedial-registrations/students', [AdminRemedialRegistrationController::class, 'students']);
        Route::get('admin/remedial-registrations', [AdminRemedialRegistrationController::class, 'index']);
    });

    /*
    | 3. Department / Bộ môn (role:bo_mon)
    */
    Route::middleware('role:bo_mon')->prefix('department')->group(function () {
        Route::get('me', [DepartmentProfileController::class, 'show']);
        Route::patch('me', [DepartmentProfileController::class, 'update']);
        Route::get('remedial-terms/current', [StudentRemedialTermController::class, 'current']);
        Route::get('remedial-registrations', [DepartmentRemedialRegistrationController::class, 'index']);
        Route::patch('remedial-registrations/{id}', [DepartmentRemedialRegistrationController::class, 'update']);
        Route::get('subject-assignments', [DepartmentSubjectAssignmentController::class, 'index']);
        Route::get('subjects/{subjectId}/teachers', [DepartmentSubjectAssignmentController::class, 'getTeachers']);
        Route::patch('subjects/{subjectId}/lecturer', [DepartmentSubjectAssignmentController::class, 'update']);
        Route::post('send-summary-email', [SummaryEmailController::class, 'send']);
    });

    /*
    | 4. Student / Sinh viên (role:sinh_vien)
    */
    Route::middleware('role:sinh_vien')->prefix('student')->group(function () {
        Route::get('me/eligible-subjects', [EligibleSubjectController::class, 'index']);
        Route::get('me/term-registered-subjects', [TermRegisteredSubjectController::class, 'index']);
        Route::get('me/remedial-registrations', [StudentRemedialRegistrationController::class, 'index']);
        Route::post('me/remedial-registrations', [StudentRemedialRegistrationController::class, 'store']);
        Route::delete('me/remedial-registrations/{id}', [StudentRemedialRegistrationController::class, 'destroy']);
        Route::get('remedial-terms/current', [StudentRemedialTermController::class, 'current']);
    });
});
