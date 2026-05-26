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

    public function listTerms(): JsonResponse
    {
        return $this->success(['terms' => $this->statisticsService->listTermOptions()]);
    }

    public function termStatistics(int $id): JsonResponse
    {
        try {
            return $this->success($this->statisticsService->getTermStatistics($id));
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 404);
        }
    }
}
