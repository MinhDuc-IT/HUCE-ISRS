<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Application\Services\RemedialRegistrationService;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\StudentNotFoundException;

/**
 * @OA\Tag(
 *     name="Đăng ký học bổ sung",
 *     description="Quản lý đăng ký học phần bổ sung của sinh viên"
 * )
 */
class RegistrationController extends BaseController
{
    public function __construct(
        private readonly RemedialRegistrationService $registrationService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/students/{studentId}/eligible-courses",
     *     tags={"Đăng ký học bổ sung"},
     *     summary="Lấy danh sách môn đủ điều kiện học bổ sung",
     *     description="Trả về các môn học mà sinh viên đã thi rớt và có thể đăng ký học bổ sung. Dữ liệu được lấy từ University System qua API.",
     *     operationId="getEligibleCourses",
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         required=true,
     *         description="Mã sinh viên",
     *         @OA\Schema(type="string", example="SV001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách môn đủ điều kiện",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Thành công"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="courseCode", type="string", example="CS101-01"),
     *                     @OA\Property(property="subjectName", type="string", example="Lập trình hướng đối tượng"),
     *                     @OA\Property(property="credits", type="integer", example=3),
     *                     @OA\Property(property="finalScore", type="number", nullable=true, example=4.5),
     *                     @OA\Property(property="letterGrade", type="string", example="F")
     *                 )
     *             ),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy sinh viên"),
     *     @OA\Response(response=503, description="Không kết nối được University System")
     * )
     */
    public function eligibleCourses(string $studentId): JsonResponse
    {
        try {
            $courses = $this->registrationService->getEligibleCourses($studentId);

            $data = array_map(fn($c) => [
                'courseCode'  => $c->courseCode,
                'subjectName' => $c->subjectName,
                'credits'     => $c->credits,
                'finalScore'  => $c->finalScore,
                'letterGrade' => $c->letterGrade,
            ], $courses);

            return $this->success($data, 'Danh sách môn đủ điều kiện học bổ sung');
        } catch (StudentNotFoundException $e) {
            return $this->error($e->getMessage(), null, 404);
        } catch (ExternalSystemException $e) {
            return $this->error($e->getMessage(), null, 503);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/registrations",
     *     tags={"Đăng ký học bổ sung"},
     *     summary="Đăng ký học phần bổ sung",
     *     description="Tạo đơn đăng ký học lại một môn đã thi rớt. Hệ thống sẽ xác minh điều kiện từ University System trước khi tạo đăng ký.",
     *     operationId="createRegistration",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id","course_code"},
     *             @OA\Property(property="student_id", type="string", example="SV001", description="Mã sinh viên"),
     *             @OA\Property(property="course_code", type="string", example="CS101-01", description="Mã học phần muốn đăng ký bổ sung")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Đăng ký thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đăng ký học bổ sung thành công"),
     *             @OA\Property(property="data", ref="#/components/schemas/RegistrationResponse"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Vi phạm business rules (đã đăng ký, môn đạt, ...)"),
     *     @OA\Response(response=404, description="Không tìm thấy sinh viên hoặc môn học"),
     *     @OA\Response(response=422, description="Dữ liệu đầu vào không hợp lệ"),
     *     @OA\Response(response=503, description="University System không phản hồi")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id'   => 'required|string|max:50',
            'course_code'  => 'required_without:course_codes|string|max:50',
            'course_codes' => 'required_without:course_code|array',
            'course_codes.*' => 'string|max:50',
        ]);

        try {
            $studentId = $validated['student_id'];
            
            if (isset($validated['course_codes'])) {
                $requests = $this->registrationService->bulkRegister($studentId, $validated['course_codes']);
                $data = array_map(fn($r) => $this->formatRequest($r), $requests);
                $message = 'Đăng ký các học phần bổ sung thành công';
            } else {
                $requestObj = $this->registrationService->register($studentId, $validated['course_code']);
                $data = $this->formatRequest($requestObj);
                $message = 'Đăng ký học bổ sung thành công';
            }

            return $this->success(
                $data,
                $message,
                201
            );
        } catch (StudentNotFoundException $e) {
            return $this->error($e->getMessage(), null, 404);
        } catch (ExternalSystemException $e) {
            return $this->error($e->getMessage(), null, 503);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/students/{studentId}/registrations",
     *     tags={"Đăng ký học bổ sung"},
     *     summary="Lấy danh sách đăng ký của sinh viên",
     *     description="Trả về tất cả đơn đăng ký học bổ sung của sinh viên, sắp xếp theo thời gian mới nhất.",
     *     operationId="getRegistrations",
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         required=true,
     *         description="Mã sinh viên",
     *         @OA\Schema(type="string", example="SV001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách đăng ký",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Thành công"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RegistrationResponse")),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy sinh viên")
     * )
     */
    public function index(string $studentId): JsonResponse
    {
        try {
            $requests = $this->registrationService->getRegistrations($studentId);
            $data     = array_map(fn($r) => $this->formatRequest($r), $requests);

            return $this->success($data);
        } catch (StudentNotFoundException $e) {
            return $this->error($e->getMessage(), null, 404);
        } catch (ExternalSystemException $e) {
            return $this->error($e->getMessage(), null, 503);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/registrations/{id}",
     *     tags={"Đăng ký học bổ sung"},
     *     summary="Hủy đăng ký học bổ sung",
     *     description="Hủy một đơn đăng ký đang ở trạng thái pending. Chỉ sinh viên sở hữu đơn mới có thể hủy.",
     *     operationId="cancelRegistration",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID đăng ký",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id"},
     *             @OA\Property(property="student_id", type="string", example="SV001", description="Mã sinh viên (xác minh quyền sở hữu)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hủy đăng ký thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Hủy đăng ký thành công"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Không thể hủy (đã được xử lý)"),
     *     @OA\Response(response=404, description="Không tìm thấy đăng ký")
     * )
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $this->registrationService->cancelRegistration($id, $validated['student_id']);

            // Trả về danh sách mới nhất sau khi hủy để Frontend hiển thị lại (theo yêu cầu UC)
            $requests = $this->registrationService->getRegistrations($validated['student_id']);
            $data     = array_map(fn($r) => $this->formatRequest($r), $requests);

            return $this->success($data, 'Hủy đăng ký thành công');
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }

    /**
     * Format TutoringRequest domain entity thành mảng JSON chuẩn.
     *
     * @param \App\Domain\Entities\TutoringRequest $request
     */
    private function formatRequest($request): array
    {
        // Phải join lấy courseCode để trả về cho Frontend
        $courseCode = \App\Models\Course::find($request->courseId)?->CourseCode ?? 'UNKNOWN';

        return [
            'id'               => $request->id,
            'studentId'        => \App\Models\Student::find($request->studentId)?->StudentCode ?? 'UNKNOWN',
            'courseCode'       => $courseCode,
            'tutoringTermId'   => $request->tutoringTermId,
            'requestedPeriods' => $request->requestedPeriods,
            'status'           => $request->status,
            'createdAt'        => $request->createdAt?->toIso8601String(),
            'note'             => $request->note,
        ];
    }
}
