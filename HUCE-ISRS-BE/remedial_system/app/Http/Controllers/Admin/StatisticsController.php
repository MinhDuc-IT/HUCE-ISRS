<?php

namespace App\Http\Controllers\Admin;

use App\Application\Services\Admin\RemedialStatisticsService;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Thống kê', description: 'Các API thống kê đợt phụ đạo')]
class StatisticsController extends BaseController
{
    public function __construct(
        private readonly RemedialStatisticsService $statisticsService,
    ) {}

    #[OA\Get(
        path: '/api/admin/statistics/terms',
        summary: 'Lấy danh sách đợt phụ đạo để lọc',
        security: [['sanctum' => []]],
        tags: ['Thống kê'],
        responses: [
            new OA\Response(response: 200, description: 'Thành công')
        ]
    )]
    public function listTerms(): JsonResponse
    {
        return $this->success(['terms' => $this->statisticsService->listTermOptions()]);
    }

    public function listTermSummaries(): JsonResponse
    {
        return $this->success($this->statisticsService->listAllTermSummaries());
    }

    #[OA\Get(
        path: '/api/admin/statistics/terms/{id}',
        summary: 'Thống kê tổng quan đợt phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Thống kê'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Thành công'),
            new OA\Response(response: 404, description: 'Không tìm thấy')
        ]
    )]
    public function termStatistics(int $id): JsonResponse
    {
        try {
            return $this->success($this->statisticsService->getTermStatistics($id));
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 404);
        }
    }

    #[OA\Get(
        path: '/api/admin/statistics/terms/{id}/teaching-payments',
        summary: 'Thống kê thanh toán giảng dạy phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Thống kê'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'keyword', in: 'query', required: false, description: 'Tên giảng viên', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Số lượng trên trang', schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Trang hiện tại', schema: new OA\Schema(type: 'integer', default: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Thành công'),
            new OA\Response(response: 404, description: 'Không tìm thấy')
        ]
    )]
    public function teachingPaymentStatistics(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $keyword = $request->input('keyword');
            $perPage = (int) $request->input('per_page', 15);
            $data = $this->statisticsService->getTeachingPaymentStatistics($id, $keyword, $perPage);
            return $this->success($data);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 404);
        }
    }

    #[OA\Get(
        path: '/api/admin/statistics/terms/{id}/teaching-payments/export',
        summary: 'Xuất Excel thanh toán giảng dạy phụ đạo',
        security: [['sanctum' => []]],
        tags: ['Thống kê'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'keyword', in: 'query', required: false, description: 'Tên giảng viên', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'File Excel'),
            new OA\Response(response: 404, description: 'Không tìm thấy')
        ]
    )]
    public function exportTeachingPayments(int $id, \Illuminate\Http\Request $request)
    {
        try {
            $keyword = $request->input('keyword');
            $query = $this->statisticsService->getTeachingPaymentStatisticsQuery($id, $keyword);
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\TeachingPaymentExport($query),
                'teaching_payments_term_' . $id . '.xlsx'
            );
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 404);
        }
    }
}
