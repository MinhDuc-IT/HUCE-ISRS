<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Học kỳ", description: "Quản lý Học kỳ (Semester)")]
class SemesterController extends BaseController
{
    /**
     * GET /api/admin/semesters — Danh sách học kỳ
     */
    #[OA\Get(
        path: "/api/admin/semesters",
        operationId: "listSemesters",
        summary: "Danh sách học kỳ",
        security: [["sanctum" => []]],
        tags: ["Học kỳ"],
    )]
    #[OA\Response(response: 200, description: "Thành công")]
    public function index(): JsonResponse
    {
        $semesters = Semester::orderBy('Year', 'desc')
            ->orderBy('TermNumber', 'desc')
            ->get();
        return $this->success($semesters);
    }

    /**
     * POST /api/admin/semesters — Tạo học kỳ mới
     */
    #[OA\Post(
        path: "/api/admin/semesters",
        operationId: "storeSemester",
        summary: "Tạo học kỳ mới",
        security: [["sanctum" => []]],
        tags: ["Học kỳ"],
    )]
    #[OA\Response(response: 201, description: "Tạo thành công")]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'Name'       => 'required|string|max:255',
            'Year'       => 'required|integer|min:2000|max:2100',
            'TermNumber' => 'required|integer|min:1|max:3',
            'IsActive'   => 'boolean',
        ]);

        $semester = Semester::create([
            'Name'       => $request->Name,
            'Year'       => $request->Year,
            'TermNumber' => $request->TermNumber,
            'IsActive'   => $request->boolean('IsActive', false),
        ]);

        return $this->success($semester, 'Tạo học kỳ thành công', 201);
    }
}
