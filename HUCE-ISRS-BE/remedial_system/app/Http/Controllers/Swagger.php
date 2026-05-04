<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Remedial Registration System API',
    version: '1.0.0',
    description: 'Tài liệu API hệ thống đăng ký học phụ đạo',
    contact: new OA\Contact(email: 'admin@remedial.edu.vn')
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'API Server chính'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'apiKey',
    name: 'Authorization',
    in: 'header',
    description: 'Enter token in format: Bearer <token>'
)]
#[OA\SecurityScheme(
    securityScheme: 'accept_json',
    type: 'apiKey',
    name: 'Accept',
    in: 'header',
    description: 'Bắt buộc trả về JSON. Điền: application/json'
)]
class Swagger
{
    // File này chứa các annotation toàn cục cho Swagger UI
}
