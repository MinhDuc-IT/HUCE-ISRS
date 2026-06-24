<?php

namespace App\Http\Controllers\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Mô tả OpenAPI tập trung (Phase 9). Logic nằm ở các controller tương ứng.
 */
final class RemedialApiPaths
{
    #[OA\Get(
        path: '/api/admin/remedial-terms',
        operationId: 'adminListRemedialTerms',
        summary: 'Danh sách đợt phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Đợt phụ đạo'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function adminListRemedialTerms(): void {}

    #[OA\Post(
        path: '/api/admin/remedial-terms',
        operationId: 'adminCreateRemedialTerm',
        summary: 'Tạo đợt phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Đợt phụ đạo'],
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public static function adminCreateRemedialTerm(): void {}

    #[OA\Get(
        path: '/api/admin/remedial-terms/{id}',
        operationId: 'adminShowRemedialTerm',
        summary: 'Chi tiết đợt phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Đợt phụ đạo'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function adminShowRemedialTerm(): void {}

    #[OA\Patch(
        path: '/api/admin/remedial-terms/{id}',
        operationId: 'adminUpdateRemedialTerm',
        summary: 'Cập nhật đợt phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Đợt phụ đạo'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function adminUpdateRemedialTerm(): void {}

    #[OA\Delete(
        path: '/api/admin/remedial-terms/{id}',
        operationId: 'adminDeleteRemedialTerm',
        summary: 'Xóa đợt phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Đợt phụ đạo'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function adminDeleteRemedialTerm(): void {}

    #[OA\Get(
        path: '/api/admin/system-configurations',
        operationId: 'adminListSystemConfigurations',
        summary: 'Danh sách cấu hình hệ thống',
        security: [['sanctum' => []]],
        tags: ['Cài đặt hệ thống'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function adminListSystemConfigurations(): void {}

    #[OA\Post(
        path: '/api/admin/system-configurations',
        operationId: 'adminUpdateSystemConfigurations',
        summary: 'Cập nhật cấu hình hệ thống',
        security: [['sanctum' => []]],
        tags: ['Cài đặt hệ thống'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function adminUpdateSystemConfigurations(): void {}

    #[OA\Post(
        path: '/api/admin/system-configurations/create',
        operationId: 'adminCreateSystemConfiguration',
        summary: 'Tạo cấu hình hệ thống',
        security: [['sanctum' => []]],
        tags: ['Cài đặt hệ thống'],
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public static function adminCreateSystemConfiguration(): void {}

    #[OA\Patch(
        path: '/api/admin/system-configurations/{key}',
        operationId: 'adminUpdateSystemConfigurationItem',
        summary: 'Cập nhật cấu hình hệ thống theo key',
        security: [['sanctum' => []]],
        tags: ['Cài đặt hệ thống'],
        parameters: [new OA\Parameter(name: 'key', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function adminUpdateSystemConfigurationItem(): void {}

    #[OA\Delete(
        path: '/api/admin/system-configurations/{key}',
        operationId: 'adminDeleteSystemConfigurationItem',
        summary: 'Xóa cấu hình hệ thống theo key',
        security: [['sanctum' => []]],
        tags: ['Cài đặt hệ thống'],
        parameters: [new OA\Parameter(name: 'key', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function adminDeleteSystemConfigurationItem(): void {}

    #[OA\Get(
        path: '/api/admin/remedial-registrations',
        operationId: 'adminListRemedialRegistrations',
        summary: 'Tra cứu đăng ký phụ đạo (Admin)',
        security: [['sanctum' => []]],
        tags: ['Đăng ký phụ đạo'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function adminListRemedialRegistrations(): void {}

    #[OA\Get(
        path: '/api/department/me',
        operationId: 'departmentShowProfile',
        summary: 'Hồ sơ bộ môn đang đăng nhập',
        security: [['sanctum' => []]],
        tags: ['Bộ môn'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function departmentShowProfile(): void {}

    #[OA\Patch(
        path: '/api/department/me',
        operationId: 'departmentUpdateProfile',
        summary: 'Cập nhật email/SĐT bộ môn',
        security: [['sanctum' => []]],
        tags: ['Bộ môn'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function departmentUpdateProfile(): void {}

    #[OA\Get(
        path: '/api/department/remedial-registrations',
        operationId: 'departmentListRegistrations',
        summary: 'Đăng ký phụ đạo thuộc bộ môn',
        security: [['sanctum' => []]],
        tags: ['Bộ môn'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function departmentListRegistrations(): void {}

    #[OA\Post(
        path: '/api/department/send-summary-email',
        operationId: 'departmentSendSummaryEmail',
        summary: 'Gửi email tổng hợp cho bộ môn',
        security: [['sanctum' => []]],
        tags: ['Bộ môn'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function departmentSendSummaryEmail(): void {}

    #[OA\Get(
        path: '/api/student/me/eligible-subjects',
        operationId: 'studentListEligibleSubjects',
        summary: 'Môn đủ điều kiện phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Sinh viên – Môn đủ điều kiện'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function studentListEligibleSubjects(): void {}

    #[OA\Get(
        path: '/api/student/me/remedial-registrations',
        operationId: 'studentListRegistrations',
        summary: 'Đăng ký phụ đạo của sinh viên',
        security: [['sanctum' => []]],
        tags: ['Sinh viên – Đăng ký phụ đạo'],
        parameters: [
            new OA\Parameter(
                name: 'remedial_term_id',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
                description: 'Lọc theo đợt phụ đạo (thường là đợt hiện tại)',
            ),
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function studentListRegistrations(): void {}

    #[OA\Post(
        path: '/api/student/me/remedial-registrations',
        operationId: 'studentCreateRegistration',
        summary: 'Đăng ký phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Sinh viên – Đăng ký phụ đạo'],
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public static function studentCreateRegistration(): void {}

    #[OA\Delete(
        path: '/api/student/me/remedial-registrations/{id}',
        operationId: 'studentCancelRegistration',
        summary: 'Hủy đăng ký phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Sinh viên – Đăng ký phụ đạo'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function studentCancelRegistration(): void {}

    #[OA\Get(
        path: '/api/student/remedial-terms/current',
        operationId: 'studentCurrentRemedialTerm',
        summary: 'Đợt phụ đạo đang mở',
        security: [['sanctum' => []]],
        tags: ['Sinh viên – Đợt phụ đạo'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public static function studentCurrentRemedialTerm(): void {}
}
