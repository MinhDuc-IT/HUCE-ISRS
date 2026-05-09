<?php

use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\TutoringClassController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TeacherPaymentController;
use App\Http\Controllers\Api\StatisticController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – Remedial Registration System
|--------------------------------------------------------------------------
| [PUBLIC]
|   POST   /api/auth/login    -> Đăng nhập (Use case: Đăng nhập)
|
| [PROTECTED – Bearer token qua Sanctum]
|   POST   /api/auth/logout   -> Đăng xuất
|   GET    /api/auth/me       -> Thông tin người dùng hiện tại
|
|   GET    /api/students/{studentId}/eligible-courses -> Môn đủ điều kiện
|   GET    /api/students/{studentId}/registrations    -> Danh sách đơn đăng ký
|   POST   /api/registrations                         -> Tạo đăng ký mới
|   DELETE /api/registrations/{id}                    -> Hủy đăng ký
*/

// ── Public: Đăng nhập ────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

// ── Protected: yêu cầu Bearer token ─────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
    
    // ── Admin: Cài đặt hệ thống (Use case: Cài đặt hệ thống) ─────────────────
    Route::prefix('admin/settings')->group(function () {
        Route::get('/',        [SettingController::class, 'index']);   // Xem danh sách cấu hình
        Route::post('/',       [SettingController::class, 'update']);  // Cập nhật cấu hình
    });

    // ── Admin: Thanh toán tiền phụ đạo ───────────────────────────────────────
    Route::prefix('admin/payments/teachers')->group(function () {
        Route::get('/',        [TeacherPaymentController::class, 'index']);  // Tổng hợp và xem (JSON)
        Route::get('/export',  [TeacherPaymentController::class, 'export']); // Xuất file Excel (.xlsx)
    });

    // ── Admin: Thống kê đợt phụ đạo ──────────────────────────────────────────
    Route::prefix('admin/statistics/terms')->group(function () {
        Route::get('/',        [StatisticController::class, 'getTerms']);
        Route::get('/{id}',    [StatisticController::class, 'getTermStatistics']);
    });

    // ── Admin: Quản lý người dùng (Use case: Thêm, Sửa, Xóa người dùng) ──────────
    Route::prefix('admin/users')->group(function () {

        Route::get('/',        [UserController::class, 'index']);   // Danh sách
        Route::post('/',       [UserController::class, 'store']);   // Thêm mới
        Route::get('/{id}',    [UserController::class, 'show']);    // Chi tiết (bước 1 UC Sửa/Xóa)
        Route::patch('/{user}',[UserController::class, 'update']);  // Sửa
        Route::delete('/{user}',[UserController::class, 'destroy']);// Xóa ← UC chính
    });

    // ── Admin: Quản lý Học kỳ ──────────────────────────────────────────────────
    Route::prefix('admin/semesters')->group(function () {
        Route::get('/',   [\App\Http\Controllers\Api\SemesterController::class, 'index']);
        Route::post('/',  [\App\Http\Controllers\Api\SemesterController::class, 'store']);
    });

    // ── Admin: Quản lý đợt phụ đạo ───────────────────────────────────────────────
    Route::prefix('admin/tutoring-terms')->group(function () {
        Route::get('/',                    [\App\Http\Controllers\Api\TutoringTermController::class, 'index']);
        Route::post('/',                   [\App\Http\Controllers\Api\TutoringTermController::class, 'store']);
        Route::get('/{id}',               [\App\Http\Controllers\Api\TutoringTermController::class, 'show']);
        Route::patch('/{id}',             [\App\Http\Controllers\Api\TutoringTermController::class, 'update']);
        Route::delete('/{id}',            [\App\Http\Controllers\Api\TutoringTermController::class, 'destroy']);
    });

    // ── Admin: Quản lý Lớp phụ đạo ───────────────────────────────────────────────
    Route::prefix('admin/tutoring-classes')->group(function () {
        Route::get('/',                    [TutoringClassController::class, 'index']);    // Danh sách
        Route::post('/',                   [TutoringClassController::class, 'store']);    // Thêm mới
        Route::get('/{id}',               [TutoringClassController::class, 'show']);     // Chi tiết (bước 1 UC Sửa/Xóa)
        Route::patch('/{id}',             [TutoringClassController::class, 'update']);
        Route::delete('/{id}',            [TutoringClassController::class, 'destroy']);
        Route::patch('/{id}/assign-teacher', [TutoringClassController::class, 'assignTeacher']);
    });

    // ── Admin: Quản lý Bộ môn ──────────────────────────────────────────────────
    Route::prefix('admin/departments')->group(function () {
        Route::get('/',                    [\App\Http\Controllers\Api\DepartmentController::class, 'index']);
        Route::post('/',                   [\App\Http\Controllers\Api\DepartmentController::class, 'store']);
        Route::get('/{id}',               [\App\Http\Controllers\Api\DepartmentController::class, 'show']);
        Route::patch('/{id}',             [\App\Http\Controllers\Api\DepartmentController::class, 'update']);
        Route::delete('/{id}',            [\App\Http\Controllers\Api\DepartmentController::class, 'destroy']);
        Route::post('/{id}/send-email',    [\App\Http\Controllers\Api\DepartmentController::class, 'sendSummaryEmail']);
    });

    // ── Sinh viên: Đăng ký học bổ sung ───────────────────────────────────────
    Route::prefix('students/{studentId}')->group(function () {
        Route::get('eligible-courses', [RegistrationController::class, 'eligibleCourses']);
        Route::get('registrations',    [RegistrationController::class, 'index']);
    });

    Route::post('/registrations',        [RegistrationController::class, 'store']);
    Route::delete('/registrations/{id}', [RegistrationController::class, 'cancel']);
});
