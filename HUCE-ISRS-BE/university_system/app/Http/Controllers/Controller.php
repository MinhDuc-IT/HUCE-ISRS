<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "University System API",
    version: "1.0.0",
    description: "API hệ thống quản lý trường đại học – dùng để tích hợp với hệ thống đăng ký học phần bổ sung",
    contact: new OA\Contact(email: "admin@university.edu.vn")
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "token",
    description: "Nhập access_token nhận được từ POST /api/token"
)]
#[OA\Server(
    url: "http://localhost:8001",
    description: "University System Server"
)]
abstract class Controller
{
    //
}
