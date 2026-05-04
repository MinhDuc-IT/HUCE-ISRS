<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Xác thực",
    description: "Quản lý token xác thực theo luồng Client Credentials"
)]
class AuthController extends BaseController
{
    #[OA\Post(
        path: "/api/token",
        operationId: "getToken",
        summary: "Lấy access token",
        description: "Xác thực hệ thống client bằng client_id và client_secret. Trả về access_token để sử dụng cho các API tiếp theo (machine-to-machine authentication).",
        tags: ["Xác thực"],
    )]
    #[OA\RequestBody(
        required: true,
        description: "Thông tin xác thực client",
        content: new OA\JsonContent(
            required: ["client_id", "client_secret"],
            properties: [
                new OA\Property(property: "client_id", type: "string", example: "remedial_system", description: "Mã định danh của hệ thống client"),
                new OA\Property(property: "client_secret", type: "string", example: "secret_key_here", description: "Mật khẩu bí mật của client")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Lấy token thành công",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Xác thực thành công"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "access_token", type: "string", example: "abc123..."),
                    new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                    new OA\Property(property: "expires_in", type: "integer", example: 3600)
                ]),
                new OA\Property(property: "errors", type: "null")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Xác thực thất bại – client_id hoặc client_secret không hợp lệ",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "message", type: "string", example: "Xác thực thất bại"),
                new OA\Property(property: "data", type: "null"),
                new OA\Property(property: "errors", type: "null")
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Dữ liệu đầu vào không hợp lệ",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "message", type: "string", example: "Dữ liệu không hợp lệ"),
                new OA\Property(property: "data", type: "null"),
                new OA\Property(property: "errors", type: "object")
            ]
        )
    )]
    public function token(Request $request)
    {
        $validated = $request->validate([
            'client_id'     => 'required|string',
            'client_secret' => 'required|string',
        ]);

        $client = ApiClient::where('client_id', $validated['client_id'])
            ->where('is_active', true)
            ->first();

        if (! $client || ! Hash::check($validated['client_secret'], $client->client_secret)) {
            return $this->error('Xác thực thất bại: client_id hoặc client_secret không hợp lệ', null, 401);
        }

        $ttl   = (int) config('university.token_ttl', 3600);
        $token = Str::random(64);
        $key   = 'api_token:' . $token;

        cache()->put($key, [
            'client_id' => $client->client_id,
            'name'      => $client->name,
        ], $ttl);

        return $this->success([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => $ttl,
        ], 'Xác thực thành công');
    }

    #[OA\Post(
        path: "/api/student/login",
        operationId: "studentLogin",
        summary: "Đăng nhập sinh viên (Backdoor)",
        description: "Đăng nhập dành cho sinh viên sử dụng mã sinh viên và mật khẩu backdoor hệ thống.",
        tags: ["Xác thực"],
    )]
    #[OA\RequestBody(
        required: true,
        description: "Thông tin đăng nhập sinh viên",
        content: new OA\JsonContent(
            required: ["student_id", "password"],
            properties: [
                new OA\Property(property: "student_id", type: "string", example: "SV001", description: "Mã sinh viên"),
                new OA\Property(property: "password", type: "string", example: "nuce_backdoor_2026", description: "Mật khẩu backdoor")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Đăng nhập thành công",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Đăng nhập thành công"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "access_token", type: "string", example: "abc123..."),
                    new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                    new OA\Property(property: "expires_in", type: "integer", example: 3600),
                    new OA\Property(property: "student", ref: "#/components/schemas/StudentDto")
                ]),
                new OA\Property(property: "errors", type: "null")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Sai mã sinh viên hoặc mật khẩu backdoor")]
    public function studentLogin(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|string',
            'password'   => 'required|string',
        ]);

        $backdoor = env('UNIVERSITY_BACKDOOR_PASS', 'nuce_backdoor_2026');

        if ($validated['password'] !== $backdoor) {
            return $this->error('Mật khẩu backdoor không chính xác', null, 401);
        }

        $sinhVien = \App\Models\SinhVien::with(['emailNguoiDung', 'tongKetDot'])
            ->where('MaSinhVien', $validated['student_id'])
            ->first();

        if (!$sinhVien) {
            return $this->error('Không tìm thấy sinh viên với mã: ' . $validated['student_id'], null, 404);
        }

        $ttl   = (int) config('university.token_ttl', 3600);
        $token = Str::random(64);
        $key   = 'api_token:' . $token;

        cache()->put($key, [
            'student_id' => $sinhVien->MaSinhVien,
            'name'       => trim($sinhVien->HoDem . ' ' . $sinhVien->Ten),
            'role'       => 'student'
        ], $ttl);

        return $this->success([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => $ttl,
            'student'      => \App\DTOs\StudentDto::fromModel($sinhVien)
        ], 'Đăng nhập thành công');
    }
}
