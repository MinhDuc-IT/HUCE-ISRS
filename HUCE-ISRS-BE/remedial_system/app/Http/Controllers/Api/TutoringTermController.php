<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\TutoringTerm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Đợt phụ đạo", description: "Quản lý Đợt phụ đạo (Cohorts)")]
class TutoringTermController extends BaseController
{
    /**
     * GET /api/admin/tutoring-terms
     */
    #[OA\Get(
        path: "/api/admin/tutoring-terms",
        operationId: "listTutoringTerms",
        summary: "Danh sách đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Response(response: 200, description: "Thành công")]
    public function index(): JsonResponse
    {
        $terms = TutoringTerm::orderBy('id', 'desc')->get();
        return $this->success($terms);
    }

    /**
     * POST /api/admin/tutoring-terms
     */
    #[OA\Post(
        path: "/api/admin/tutoring-terms",
        operationId: "storeTutoringTerm",
        summary: "Thêm mới đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Response(response: 201, description: "Thêm thành công")]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'SemesterId'   => 'nullable|integer|exists:Semester,id',
            'Name'         => 'required|string|max:255',
            'StartDate'    => 'required|date',
            'EndDate'      => 'required|date|after_or_equal:StartDate',
            'DonGia1Tiet'  => 'required|numeric|min:0',
            'HeSoPD'       => 'required|numeric|min:0',
            'HeSoDonGia'   => 'required|numeric|min:0',
        ]);

        // Nếu FE không gửi SemesterId thì tự động lấy/tạo Semester mặc định
        if ($request->filled('SemesterId')) {
            $semesterId = $request->SemesterId;
        } else {
            $semester = \App\Models\Semester::first();
            if (!$semester) {
                $semester = \App\Models\Semester::create([
                    'Name'       => 'Học kỳ mặc định',
                    'Year'       => date('Y'),
                    'TermNumber' => 1,
                    'StartDate'  => now()->startOfYear(),
                    'EndDate'    => now()->endOfYear(),
                    'IsActive'   => true,
                ]);
            }
            $semesterId = $semester->id;
        }

        $term = TutoringTerm::create([
            'SemesterId'   => $semesterId,
            'Name'         => $request->Name,
            'StartDate'    => $request->StartDate,
            'EndDate'      => $request->EndDate,
            'DonGia1Tiet'  => $request->DonGia1Tiet,
            'HeSoPD'       => $request->HeSoPD,
            'HeSoDonGia'   => $request->HeSoDonGia,
            'IsDefault'    => false,
        ]);

        return $this->success($term, 'Tạo đợt phụ đạo thành công', 201);
    }

    /**
     * GET /api/admin/tutoring-terms/{id}
     */
    #[OA\Get(
        path: "/api/admin/tutoring-terms/{id}",
        operationId: "showTutoringTerm",
        summary: "Chi tiết đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Thành công")]
    #[OA\Response(response: 404, description: "Không tìm thấy")]
    public function show(int $id): JsonResponse
    {
        $term = TutoringTerm::find($id);
        if (!$term) return $this->error('Đợt phụ đạo không tồn tại', null, 404);
        return $this->success($term);
    }

    /**
     * PATCH /api/admin/tutoring-terms/{id}
     */
    #[OA\Patch(
        path: "/api/admin/tutoring-terms/{id}",
        operationId: "updateTutoringTerm",
        summary: "Cập nhật đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Cập nhật thành công")]
    public function update(Request $request, int $id): JsonResponse
    {
        $term = TutoringTerm::find($id);
        if (!$term) return $this->error('Đợt phụ đạo không tồn tại', null, 404);

        $request->validate([
            'Name' => 'sometimes|string|max:255',
            'StartDate' => 'sometimes|date',
            'EndDate' => 'sometimes|date|after_or_equal:StartDate',
            'RegistrationStartDate' => 'sometimes|date',
            'RegistrationEndDate' => 'sometimes|date|after_or_equal:RegistrationStartDate',
            'DonGia1Tiet' => 'sometimes|numeric|min:0',
            'HeSoPD' => 'sometimes|numeric|min:0',
            'HeSoDonGia' => 'sometimes|numeric|min:0',
        ]);

        $term->update($request->only([
            'Name', 'StartDate', 'EndDate', 
            'RegistrationStartDate', 'RegistrationEndDate',
            'DonGia1Tiet', 'HeSoPD', 'HeSoDonGia'
        ]));

        return $this->success($term, 'Cập nhật đợt phụ đạo thành công');
    }

    /**
     * DELETE /api/admin/tutoring-terms/{id}
     */
    #[OA\Delete(
        path: "/api/admin/tutoring-terms/{id}",
        operationId: "destroyTutoringTerm",
        summary: "Xóa đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Đợt phụ đạo"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Xóa thành công")]
    public function destroy(int $id): JsonResponse
    {
        $term = TutoringTerm::find($id);
        if (!$term) return $this->error('Đợt phụ đạo không tồn tại', null, 404);

        // Check for existing TutoringClass logic could be added here
        if ($term->tutoringClasses()->exists()) {
            return $this->error('Không thể xóa đợt phụ đạo vì đã có lớp mở trong đợt này.', null, 400);
        }

        $term->delete();
        return $this->success(null, 'Xóa đợt phụ đạo thành công');
    }
}
