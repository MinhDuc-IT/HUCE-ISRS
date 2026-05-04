<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\SystemConfig;
use App\Models\TutoringClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherPaymentExport;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Thanh toán",
    description: "Quản lý thanh toán và xuất file – chỉ Admin"
)]
class TeacherPaymentController extends BaseController
{
    /**
     * Tính toán và tổng hợp dữ liệu thanh toán.
     * Tách riêng logic để dùng chung cho cả JSON response và Excel export.
     */
    private function getPaymentData(Request $request)
    {
        // Lấy danh sách đợt phụ đạo đã kết thúc (hoặc đang mở)
        // Kèm theo số lượng sinh viên đăng ký được duyệt
        $query = DB::table('TutoringClass as t')
            ->leftJoin('Enrollment as e', function ($join) {
                $join->on('t.id', '=', 'e.TutoringClassId')
                     ->where('e.Status', '=', 'active');
            })
            ->join('TutoringTerm as tt', 't.TutoringTermId', '=', 'tt.id')
            ->join('Course as c', 't.CourseId', '=', 'c.id')
            ->join('Teacher as tc', 't.TeacherId', '=', 'tc.id')
            ->select(
                't.id',
                'tc.TeacherCode as teacher_code',
                'tc.FullName as teacher_name',
                'c.CourseCode as course_code',
                'c.CourseName as course_name',
                'c.TotalPeriods as total_periods',
                'tt.Name as term_name',
                'tt.DonGia1Tiet as don_gia',
                'tt.HeSoPD as he_so_pd',
                'tt.HeSoDonGia as he_so_don_gia',
                DB::raw('COUNT(e.id) as student_count')
            )
            ->whereNotNull('t.TeacherId')
            ->groupBy(
                't.id',
                'tc.TeacherCode',
                'tc.FullName',
                'c.CourseCode',
                'c.CourseName',
                'c.TotalPeriods',
                'tt.Name',
                'tt.DonGia1Tiet',
                'tt.HeSoPD',
                'tt.HeSoDonGia'
            )
            ->orderBy('tc.TeacherCode');

        if ($request->filled('tutoring_term_id')) {
            $query->where('t.TutoringTermId', $request->tutoring_term_id);
        }

        $classes = $query->get();

        // Map data và tính tiền
        return $classes->map(function ($item) {
            // Công thức tính tiền: Tiền = Tổng số tiết * Đơn giá 1 tiết * Hệ số đợt phụ đạo * Hệ số đơn giá
            $totalPeriods = $item->total_periods ?? 0;
            $donGia = $item->don_gia ?? 150000;
            $heSoPD = $item->he_so_pd ?? 1;
            $heSoDonGia = $item->he_so_don_gia ?? 1.0;

            $amount = $totalPeriods * $donGia * $heSoPD * $heSoDonGia;

            return [
                'id'            => $item->id,
                'teacher_code'  => $item->teacher_code,
                'teacher_name'  => $item->teacher_name,
                'course_code'   => $item->course_code,
                'course_name'   => $item->course_name,
                'term'          => $item->term_name,
                'total_periods' => $totalPeriods,
                'don_gia'       => $donGia,
                'he_so_pd'      => $heSoPD,
                'he_so_don_gia' => $heSoDonGia,
                'student_count' => $item->student_count,
                'amount'        => $amount,
                'amount_formatted' => number_format($amount, 0, ',', '.') . ' VNĐ',
            ];
        });
    }

    /**
     * GET /api/admin/payments/teachers
     *
     * Use case : Thanh toán tiền phụ đạo
     * Actor    : Admin
     *
     * Normal Flow:
     *   1. Admin chọn chức năng thanh toán                         ← bước 1
     *   2. Hệ thống tổng hợp, tính tiền và hiển thị danh sách      ← bước 2
     */
    #[OA\Get(
        path: "/api/admin/payments/teachers",
        operationId: "listTeacherPayments",
        summary: "Tổng hợp thanh toán tiền phụ đạo cho giảng viên",
        security: [["sanctum" => []]],
        tags: ["Thanh toán"],
    )]
    #[OA\Parameter(name: "tutoring_term_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Danh sách thanh toán")]
    #[OA\Response(response: 403, description: "Không có quyền")]
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->error('Không có quyền truy cập.', null, 403);
        }

        $data = $this->getPaymentData($request);

        return $this->success([
            'payments'        => $data,
        ]);
    }

    /**
     * GET /api/admin/payments/teachers/export
     *
     * Use case : Thanh toán tiền phụ đạo (tiếp theo)
     * Actor    : Admin
     *
     * Normal Flow:
     *   4. Người dùng chọn xuất danh sách ra excel                 ← bước 4
     *   5. Hệ thống xuất thông tin thành file excel                ← bước 5
     */
    #[OA\Get(
        path: "/api/admin/payments/teachers/export",
        operationId: "exportTeacherPayments",
        summary: "Xuất danh sách thanh toán ra Excel (.xlsx)",
        security: [["sanctum" => []]],
        tags: ["Thanh toán"],
    )]
    #[OA\Parameter(name: "tutoring_term_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "File Excel (.xlsx)")]
    #[OA\Response(response: 403, description: "Không có quyền")]
    public function export(Request $request)
    {
        if (! $request->user()?->isAdmin()) {
            return response()->json(['message' => 'Không có quyền truy cập.'], 403);
        }

        $data = $this->getPaymentData($request);

        return Excel::download(
            new TeacherPaymentExport($data), 
            'Danh_Sach_Thanh_Toan_Giang_Vien.xlsx'
        );
    }
}
