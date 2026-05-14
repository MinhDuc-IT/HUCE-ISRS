<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CourseDto;
use App\DTOs\StudentDto;
use App\Http\Controllers\BaseController;
use App\Models\SinhVien;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Sinh viên",
    description: "Tra cứu thông tin sinh viên và môn học"
)]
class StudentController extends BaseController
{
    #[OA\Get(
        path: "/api/students/{id}",
        operationId: "getStudent",
        summary: "Lấy thông tin sinh viên",
        description: "Trả về thông tin chi tiết của một sinh viên bao gồm họ tên, ngày sinh, email trường và điểm tổng kết tích lũy.",
        security: [["bearerAuth" => []]],
        tags: ["Sinh viên"],
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "Mã sinh viên (MaSinhVien, VD: SV001)",
        schema: new OA\Schema(type: "string", example: "SV001")
    )]
    #[OA\Response(
        response: 200,
        description: "Thông tin sinh viên",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Thành công"),
                new OA\Property(property: "data", ref: "#/components/schemas/StudentDto"),
                new OA\Property(property: "errors", type: "null")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Chưa xác thực – thiếu hoặc sai token")]
    #[OA\Response(response: 404, description: "Không tìm thấy sinh viên")]
    public function show(string $id): JsonResponse
    {
        $sinhVien = SinhVien::with(['emailNguoiDung', 'tongKetDot'])
            ->where('MaSinhVien', $id)
            ->first();

        if (! $sinhVien) {
            return $this->error('Không tìm thấy sinh viên với mã: ' . $id, null, 404);
        }

        return $this->success(StudentDto::fromModel($sinhVien));
    }

    #[OA\Get(
        path: "/api/students/{id}/courses",
        operationId: "getStudentCourses",
        summary: "Lấy danh sách môn học của sinh viên",
        description: "Trả về danh sách tất cả các môn học mà sinh viên đã học kèm kết quả điểm số, mã học phần, tên môn và học kỳ.",
        security: [["bearerAuth" => []]],
        tags: ["Sinh viên"],
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "Mã sinh viên (MaSinhVien)",
        schema: new OA\Schema(type: "string", example: "SV001")
    )]
    #[OA\Response(
        response: 200,
        description: "Danh sách môn học",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Thành công"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/CourseDto")),
                new OA\Property(property: "errors", type: "null")
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Chưa xác thực")]
    #[OA\Response(response: 404, description: "Không tìm thấy sinh viên")]
    public function courses(string $id): JsonResponse
    {
        $sinhVien = SinhVien::where('MaSinhVien', $id)->first();

        if (! $sinhVien) {
            return $this->error('Không tìm thấy sinh viên với mã: ' . $id, null, 404);
        }

        $ketQuaList = $sinhVien->ketQuaHocTap()
            ->with(['lopHocPhan.monHoc', 'lopHocPhan.dot'])
            ->get();

        $courses = $ketQuaList->map(fn($kq) => CourseDto::fromKetQua($kq));

        return $this->success($courses, 'Thành công');
    }

    #[OA\Get(
        path: "/api/students/{id}/courses/semester/{semester_key}",
        operationId: "getStudentCoursesBySemester",
        summary: "Lấy danh sách môn học của sinh viên theo học kỳ (ID hoặc Mã)",
        description: "Trả về danh sách các môn học theo ID học kỳ (số nguyên) hoặc Mã học kỳ (định dạng YYYYS, ví dụ: 20241 cho HK1 năm 2024).",
        security: [["bearerAuth" => []]],
        tags: ["Sinh viên"],
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "Mã sinh viên",
        schema: new OA\Schema(type: "string", example: "SV001")
    )]
    #[OA\Parameter(
        name: "semester_key",
        in: "path",
        required: true,
        description: "Số 1–3 chữ số: Id DM_Dot (lhp.IDDot). Từ 4 ký tự: YYYY = DM_NamHoc.NamHoc, tùy chọn thêm 1 chữ số = dot.SoThuTu (VD: 20241 = năm 2024, HK có SoThuTu=1). Không dùng 25 để chỉ năm 2025.",
        schema: new OA\Schema(type: "string", example: "20241")
    )]
    #[OA\Response(
        response: 200,
        description: "Danh sách môn học theo kỳ",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Thành công"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/CourseDto")),
                new OA\Property(property: "errors", type: "null")
            ]
        )
    )]
    public function coursesBySemester(string $id, string $semester_key): JsonResponse
    {
        // Kiểm tra sinh viên tồn tại
        $sinhVien = SinhVien::where('MaSinhVien', $id)->first();

        if (!$sinhVien) {
            return $this->error('Không tìm thấy sinh viên với mã: ' . $id, null, 404);
        }

        /*
         * Mirror câu SQL:
         *   DT_SinhVien sv
         *   LEFT JOIN DT_KetQuaHocTapMonHoc kq ON sv.Id = kq.IDSinhVien
         *   LEFT JOIN TKB_LopHocPhan        lhp ON kq.IDLopHocPhan = lhp.Id
         *   LEFT JOIN TKB_MonHoc            mh  ON lhp.IDMonHoc    = mh.Id
         *   LEFT JOIN DM_Dot               dot  ON lhp.IDDot       = dot.Id
         *   LEFT JOIN DM_NamHoc            nh   ON dot.IDNamHoc    = nh.Id   (IDNamHoc là FK; năm hiển thị = nh.NamHoc)
         *   WHERE sv.MaSinhVien = ?
         */
        $query = DB::connection('sqlsrv')
            ->table('DT_KetQuaHocTapMonHoc as kq')
            ->leftJoin('TKB_LopHocPhan as lhp', 'kq.IDLopHocPhan', '=', 'lhp.Id')
            ->leftJoin('TKB_MonHoc as mh',       'lhp.IDMonHoc',    '=', 'mh.Id')
            ->leftJoin('DM_Dot as dot',           'lhp.IDDot',       '=', 'dot.Id')
            ->leftJoin('DM_NamHoc as nh',         'dot.IDNamHoc',    '=', 'nh.Id')
            ->where('kq.IDSinhVien', $sinhVien->Id)
            ->select(
                'mh.MaHocPhan',
                'mh.MaMonHoc',
                'mh.TenMonHoc',
                'mh.SoTinChi',
                'lhp.MaLopHocPhan',
                'kq.DiemChuyenCan1',
                'kq.DiemThi',
                'kq.DiemTongKet',
                'kq.DiemTinChi',
                'kq.DiemChu',
                'dot.SoThuTu as HocKy',
                'nh.NamHoc as IDNamHoc',
            );

        // Lọc theo học kỳ
        if (is_numeric($semester_key) && strlen($semester_key) < 4) {
            // Số ngắn (VD: 10) → ID trực tiếp của DM_Dot (không phải năm 20xx)
            $query->where('lhp.IDDot', $semester_key);
        } else {
            // Chuỗi dạng YYYYS (VD: 20241) hoặc YYYY (VD: 2024) — YYYY = DM_NamHoc.NamHoc, S = dot.SoThuTu
            $year  = substr($semester_key, 0, 4);
            $order = substr($semester_key, 4);

            $yearInt = (int) $year;
            $query->whereIn('dot.IDNamHoc', function ($sub) use ($yearInt) {
                $sub->select('Id')
                    ->from('DM_NamHoc')
                    ->where('NamHoc', $yearInt);
            });
            if ($order !== '') {
                $query->where('dot.SoThuTu', (int) $order);
            }
        }

        $rows = $query->get();

        $courses = $rows->map(fn($row) => CourseDto::fromRow($row));

        return $this->success($courses, 'Thành công');
    }
}
