<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CourseDto;
use App\DTOs\RegisteredCourseDto;
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

        $rows = DB::connection('sqlsrv')
            ->table('DT_SinhVien as sv')
            ->join('DT_DangKyHocPhan as dk', 'dk.IDSinhVien', '=', 'sv.Id')
            ->leftJoin('TKB_LopHocPhan as lhp', 'dk.IDLopHocPhan', '=', 'lhp.Id')
            ->leftJoin('TKB_MonHoc as mh', 'lhp.IDMonHoc', '=', 'mh.Id')
            ->leftJoin('DM_Dot as dot', 'lhp.IDDot', '=', 'dot.Id')
            ->leftJoin('TMP_DsBoMonKhoa as BMK', 'mh.IDToBoMon', '=', 'BMK.IDBoMon')
            ->where('sv.MaSinhVien', $id)
            ->select(
                'mh.MaHocPhan',
                'mh.MaMonHoc',
                'mh.TenMonHoc',
                'mh.SoTinChi',
                'lhp.MaLopHocPhan',
                'dot.SoThuTu as HocKy',
                'dot.IDNamHoc as IDNamHoc',
                'BMK.IDBoMon as departmentId',
                'BMK.TenBoMon as departmentName',
            )
            ->get();

        $courses = $rows->map(fn ($row) => CourseDto::fromRow($row));

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

    #[OA\Get(
        path: '/api/students/{id}/registered-courses/{year}/{semester}',
        operationId: 'getStudentRegisteredCoursesByTerm',
        summary: 'Môn đã đăng ký học chính quy theo năm học và học kỳ',
        description: 'Lấy từ DT_DangKyHocPhan — môn SV đã đăng ký lớp học phần trong kỳ (DM_NamHoc.NamHoc + DM_Dot.SoThuTu). Không trả về môn có 1 tín chỉ.',
        security: [['bearerAuth' => []]],
        tags: ['Sinh viên'],
    )]
    public function registeredCoursesByTerm(string $id, int $year, int $semester): JsonResponse
    {
        $sinhVien = SinhVien::where('MaSinhVien', $id)->first();

        if (! $sinhVien) {
            return $this->error('Không tìm thấy sinh viên với mã: ' . $id, null, 404);
        }

        $rows = DB::connection('sqlsrv')
            ->table('DT_SinhVien as sv')
            ->join('DT_DangKyHocPhan as dk', 'dk.IDSinhVien', '=', 'sv.Id')
            ->join('TKB_LopHocPhan as lhp', 'lhp.Id', '=', 'dk.IDLopHocPhan')
            ->join('DM_Dot as dot', 'dot.Id', '=', 'lhp.IDDot')
            ->join('DM_NamHoc as nh', 'nh.Id', '=', 'dot.IDNamHoc')
            ->join('TKB_MonHoc as tkb_mh', 'tkb_mh.Id', '=', 'lhp.IDMonHoc')
            ->leftJoin('DM_MonHoc as mh', 'mh.MaMonHoc', '=', 'tkb_mh.MaMonHoc')
            ->leftJoin('DM_TrangThaiDangKy as ttdk', 'ttdk.Id', '=', 'dk.IDTrangThaiDangKy')
            ->leftJoin('TKB_DanhSachLopHocPhanThi as dst', function ($join) {
                $join->on('dst.IDLopHocPhan', '=', 'lhp.Id')
                    ->on('dst.IDDot', '=', 'lhp.IDDot');
            })
            ->where('sv.MaSinhVien', $id)
            ->where('nh.NamHoc', $year)
            ->where('dot.SoThuTu', $semester)
            ->whereIn('dk.IDTrangThaiDangKy', [1, 2, 3, 4])
            ->whereRaw('COALESCE(mh.SoTinChi, tkb_mh.SoTinChi, 0) <> 1')
            ->selectRaw("
                sv.Id as IDSinhVien,
                sv.MaSinhVien,
                nh.NienHoc,
                nh.NamHoc,
                dot.SoThuTu as HocKy,
                dot.TenDot,
                COALESCE(mh.MaMonHoc, tkb_mh.MaMonHoc) as MaMonHoc,
                COALESCE(mh.TenMonHoc, tkb_mh.TenMonHoc) as TenMonHoc,
                COALESCE(mh.SoTinChi, tkb_mh.SoTinChi, 0) as SoTinChi,
                lhp.MaLopHocPhan,
                lhp.LopDuKien,
                dk.NgayDangKy,
                dk.Id as IDDangKyHocPhan,
                dk.IDTrangThaiDangKy,
                ttdk.TenTrangThai as TrangThaiDangKy,
                COALESCE(
                    (
                        SELECT TOP (1) lt.NgayThi
                        FROM TKB_DanhSachLopXepLichThi ds
                        INNER JOIN TKB_LopXepLichThi lxt ON lxt.Id = ds.IDLopXepLichThi
                        INNER JOIN TKB_LichThi lt ON lt.IDLopXepLichThi = lxt.Id
                        WHERE ds.IDLopHocPhan = lhp.Id
                        ORDER BY lt.NgayThi, lt.TuTiet
                    ),
                    dst.NgayThi
                ) as NgayThi
            ")
            ->orderByRaw('COALESCE(mh.MaMonHoc, tkb_mh.MaMonHoc)')
            ->get();

        $courses = $rows->map(fn ($row) => RegisteredCourseDto::fromRow($row));

        return $this->success($courses, 'Thành công');
    }
}
