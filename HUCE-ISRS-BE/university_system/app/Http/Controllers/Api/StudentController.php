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
        description: "ID học kỳ (VD: 10) hoặc Mã học kỳ (VD: 20241)",
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
         *   WHERE sv.MaSinhVien = ?
         */
        $query = DB::connection('sqlsrv')
            ->table('DT_KetQuaHocTapMonHoc as kq')
            ->leftJoin('TKB_LopHocPhan as lhp', 'kq.IDLopHocPhan', '=', 'lhp.Id')
            ->leftJoin('TKB_MonHoc as mh',       'lhp.IDMonHoc',    '=', 'mh.Id')
            ->leftJoin('DM_Dot as dot',           'lhp.IDDot',       '=', 'dot.Id')
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
                'dot.IdNamHoc'
            );

        // Lọc theo học kỳ
        if (is_numeric($semester_key) && strlen($semester_key) < 4) {
            // Số ngắn (VD: 10) → ID trực tiếp của DM_Dot
            $query->where('lhp.IDDot', $semester_key);
        } else {
            // Chuỗi dạng YYYYS (VD: 20241) hoặc YYYY (VD: 2024)
            $year  = substr($semester_key, 0, 4);
            $order = substr($semester_key, 4);

            $query->where('dot.IdNamHoc', $year);
            if ($order !== '') {
                $query->where('dot.SoThuTu', $order);
            }
        }

        $rows = $query->get();

        $courses = $rows->map(fn($row) => CourseDto::fromRow($row));

        return $this->success($courses, 'Thành công');
    }
}
