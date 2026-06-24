<?php

namespace App\Http\Controllers\Student;

use App\Domain\Ports\Persistence\RemedialTermRepositoryPort;
use App\Http\Controllers\BaseController;
use App\Http\Resources\RemedialTermResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Sinh viên – Đợt phụ đạo', description: 'Thông tin đợt phụ đạo (read-only)')]
class RemedialTermController extends BaseController
{
    public function __construct(
        private readonly RemedialTermRepositoryPort $termRepository,
    ) {}

    public function current(): JsonResponse
    {
        $term = $this->termRepository->findCurrent();

        if ($term === null) {
            return $this->error('Hiện không có đợt phụ đạo nào.', null, 404);
        }

        return $this->success((new RemedialTermResource($term))->resolve());
    }
}
