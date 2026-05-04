<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Remedial Registration System API",
 *     version="1.0.0",
 *     description="API Hệ thống Đăng ký Học phần Bổ sung cho sinh viên quốc tế. Tích hợp với University System qua Client Credentials.",
 *     @OA\Contact(email="admin@remedial.edu.vn")
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Nhập Bearer token xác thực người dùng"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Remedial System Server"
 * )
 *
 * @OA\Schema(
 *     schema="RegistrationResponse",
 *     description="Thông tin một đăng ký học bổ sung",
 *     @OA\Property(property="id", type="integer", example=1, description="ID đăng ký"),
 *     @OA\Property(property="studentId", type="string", example="SV001", description="Mã sinh viên"),
 *     @OA\Property(property="courseCode", type="string", example="CS101-01", description="Mã học phần"),
 *     @OA\Property(property="subjectName", type="string", example="Lập trình hướng đối tượng", description="Tên môn học"),
 *     @OA\Property(property="credits", type="integer", example=3, description="Số tín chỉ"),
 *     @OA\Property(property="status", type="string", example="pending", description="Trạng thái đăng ký"),
 *     @OA\Property(property="registeredAt", type="string", format="datetime", example="2024-01-15T08:00:00Z"),
 *     @OA\Property(property="note", type="string", nullable=true)
 * )
 */
class OpenApi
{
}
