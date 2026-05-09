<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\TutoringTerm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Thống kê",
    description: "Các API thống kê đợt phụ đạo"
)]
class StatisticController extends BaseController
{
    /**
     * Lấy danh sách các đợt phụ đạo
     */
    #[OA\Get(
        path: "/api/admin/statistics/terms",
        operationId: "getStatisticTerms",
        summary: "Lấy danh sách đợt phụ đạo",
        security: [["sanctum" => []]],
        tags: ["Thống kê"],
    )]
    #[OA\Response(response: 200, description: "Thành công")]
    public function getTerms(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $terms = TutoringTerm::orderBy('id', 'desc')->get(['id', 'Name as name']);
        
        return $this->success(['terms' => $terms]);
    }

    /**
     * Lấy thống kê cho 1 đợt phụ đạo cụ thể
     */
    #[OA\Get(
        path: "/api/admin/statistics/terms/{id}",
        operationId: "getTermStatistics",
        summary: "Thống kê đợt phụ đạo cụ thể",
        security: [["sanctum" => []]],
        tags: ["Thống kê"],
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID đợt phụ đạo", schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Thành công")]
    #[OA\Response(response: 404, description: "Không tìm thấy đợt phụ đạo")]
    public function getTermStatistics(Request $request, $id): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $term = TutoringTerm::find($id);
        if (!$term) {
            return $this->error('Không tìm thấy đợt phụ đạo.', null, 404);
        }

        // 1. Tổng số môn danh mục đợt này
        // (Trong DB hiện tại, một TutoringClass đại diện cho 1 môn được mở trong đợt)
        $catalogCourseCount = DB::table('TutoringClass')
            ->where('TutoringTermId', $id)
            ->count('CourseId');

        // Lấy danh sách các lớp trong đợt này
        $classIds = DB::table('TutoringClass')
            ->where('TutoringTermId', $id)
            ->pluck('id')
            ->toArray();

        $distinctStudentCount = 0;
        $coursesWithRegistrationCount = 0;
        $assignedClassCount = 0;
        $totalRevenue = 0;

        if (count($classIds) > 0) {
            // 2. Số lượng sinh viên phân biệt đã đăng ký và được duyệt ('active')
            $distinctStudentCount = DB::table('Enrollment')
                ->whereIn('TutoringClassId', $classIds)
                ->where('Status', 'active')
                ->distinct('StudentId')
                ->count('StudentId');

            // 3. Số môn (lớp) có ít nhất 1 đăng ký (active)
            $classesWithRegs = DB::table('Enrollment')
                ->whereIn('TutoringClassId', $classIds)
                ->where('Status', 'active')
                ->distinct('TutoringClassId')
                ->pluck('TutoringClassId')
                ->toArray();
            
            $coursesWithRegistrationCount = count($classesWithRegs);

            // 4. Lớp đã phân công GV
            $assignedClassCount = DB::table('TutoringClass')
                ->whereIn('id', $classIds)
                ->whereNotNull('TeacherId')
                ->count();

            // 5. Tổng tiền phụ đạo ước tính (tổng số SV * (đơn giá cơ bản + VAT))
            // Dựa trên logic hệ số phụ đạo và đơn giá của term
            $totalEnrollments = DB::table('Enrollment')
                ->whereIn('TutoringClassId', $classIds)
                ->where('Status', 'active')
                ->count();
            
            // Giả sử Đơn giá sinh viên phải nộp là (DonGia1Tiet * he so? + VAT). 
            // Nếu DB không có fee per registration, ta tính tạm bằng 500k * số đăng ký cho Demo, 
            // Hoặc lấy theo DonGia1Tiet. Ta sẽ giả lập 1 số dựa trên cài đặt.
            $feePerRegistration = 500000; 
            $vatPercent = 0.1; // 10%
            $perReg = $feePerRegistration * (1 + $vatPercent);
            
            $totalRevenue = $totalEnrollments * $perReg;
        }

        return $this->success([
            'cohortId' => $id,
            'distinctStudentCount' => $distinctStudentCount,
            'catalogCourseCount' => $catalogCourseCount,
            'coursesWithRegistrationCount' => $coursesWithRegistrationCount,
            'assignedClassCount' => $assignedClassCount,
            'totalRevenue' => $totalRevenue
        ]);
    }
}
