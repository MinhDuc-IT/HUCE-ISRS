<?php

namespace App\Infrastructure\External\University;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Domain\Entities\SubjectResult;
use App\Domain\Entities\StudentInfo;
use App\Domain\Entities\TermRegisteredCourse;
use App\Domain\Exceptions\ExternalSystemException;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Domain\Ports\External\StudentInfoPort;
use App\Infrastructure\External\University\UniversityAuthClient;
use App\Infrastructure\Support\CircuitBreaker;

/**
 * StudentInfoApiAdapter – Adapter triển khai StudentInfoPort.
 *
 * Chịu trách nhiệm:
 * - Gọi University System API để lấy dữ liệu
 * - Map response JSON sang Domain Entities (Anti-Corruption Layer)
 * - Xử lý lỗi HTTP và ném Domain Exceptions
 *
 * KHÔNG chứa business logic – chỉ là lớp giao tiếp.
 */
class StudentInfoApiAdapter implements StudentInfoPort
{
    private readonly CircuitBreaker $circuitBreaker;

    public function __construct(
        private readonly UniversityAuthClient $authClient,
        private readonly string              $baseUrl,
        private readonly int                 $timeoutSeconds = 15,
        private readonly ?string             $loginUrl = null,
        private readonly ?string             $studentInfoBaseUrl = null,
    ) {
        $this->circuitBreaker = new CircuitBreaker('university_api', 5, 60);
    }

    /**
     * {@inheritdoc}
     */
    public function getStudent(string $studentCode): StudentInfo
    {
        $response = $this->callApi("GET", "/api/students/{$studentCode}");
        $data     = $response['data'];
        
        return new StudentInfo(
            id:                  $data['id'],
            fullName:            $data['fullName'],
            gender:              $data['gender'],
            dateOfBirth:         $data['dateOfBirth'],
            placeOfBirth:        $data['placeOfBirth'],
            personalEmail:       $data['personalEmail'],
            universityEmail:     $data['universityEmail'] ?? null,
            gpaScale10:          isset($data['gpaScale10']) ? (float) $data['gpaScale10'] : null,
            gpaScale4:           isset($data['gpaScale4']) ? (float) $data['gpaScale4'] : null,
            gradeClassification: $data['gradeClassification'] ?? null,
            totalCredits:        isset($data['totalCredits']) ? (int) $data['totalCredits'] : null,
            failedCredits:       isset($data['failedCredits']) ? (int) $data['failedCredits'] : null,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCourses(string $studentCode): array
    {
        $response = $this->callApi("GET", "/api/students/{$studentCode}/courses");

        return array_map(
            fn(array $item) => new SubjectResult(
                courseCode:       $item['courseCode'],
                subjectCode:      $item['subjectCode'],
                subjectName:      $item['subjectName'],
                credits:          (int) $item['credits'],
                classSectionCode: $item['classSectionCode'],
                semesterOrder:    (int) $item['semesterOrder'],
                academicYear:     (int) $item['academicYear'],
                finalScore:       isset($item['finalScore']) ? (float) $item['finalScore'] : null,
                gpaScore:         isset($item['gpaScore']) ? (float) $item['gpaScore'] : null,
                letterGrade:      $item['letterGrade'] ?? null,
                departmentCode:   isset($item['departmentId']) ? (int) $item['departmentId'] : null,
                departmentName:   $item['departmentName'] ?? null,
            ),
            $response['data']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRegisteredCoursesForSemester(string $studentCode, int $year, int $semester): array
    {
        $response = $this->callApi(
            'GET',
            "/api/students/{$studentCode}/registered-courses/{$year}/{$semester}"
        );

        return array_map(
            fn (array $item) => new TermRegisteredCourse(
                subjectCode:            $item['subjectCode'] ?? '',
                subjectName:            $item['subjectName'] ?? '',
                credits:                (int) ($item['credits'] ?? 0),
                classSectionCode:       $item['classSectionCode'] ?? '',
                plannedClass:           $item['plannedClass'] ?? '',
                registrationDate:       $item['registrationDate'] ?? null,
                registrationId:         (int) ($item['registrationId'] ?? 0),
                registrationStatusId:   (int) ($item['registrationStatusId'] ?? 0),
                registrationStatusName: $item['registrationStatusName'] ?? '',
                academicYearLabel:      $item['academicYearLabel'] ?? '',
                academicYear:           (int) ($item['academicYear'] ?? 0),
                semesterOrder:          (int) ($item['semesterOrder'] ?? 0),
                termName:               $item['termName'] ?? '',
                examDate:               $item['examDate'] ?? null,
            ),
            $response['data'] ?? []
        );
    }

    /**
     * {@inheritdoc}
     */
    public function verifyCredentials(string $studentCode, string $password): bool
    {
        try {
            $token = $this->authClient->getToken();
            $loginUrl = $this->resolveLoginUrl();

            Log::info("Đang gửi request xác thực đến University System: {$loginUrl} với studentCode={$studentCode}\n");

            $response = Http::timeout($this->timeoutSeconds)
                ->withToken($token)
                ->post($loginUrl, [
                    'student_id' => $studentCode,
                    'password'   => $password,
                ]);

            if ($response->status() === 200) {
                return $response->json('success', false);
            }

            return false;
        } catch (\Exception $e) {
            Log::error("[StudentInfoApiAdapter] Lỗi xác thực credentials", ['error' => $e->getMessage()]);
            throw new ExternalSystemException('Lỗi khi xác thực với University System: ' . $e->getMessage());
        }
    }

    /**
     * Thực hiện HTTP request đến University System với Bearer token.
     *
     * @param string $method   Phương thức HTTP (GET, POST, ...)
     * @param string $endpoint Endpoint URL (VD: /api/students/SV001)
     * @return array           Parsed JSON response
     *
     * @throws StudentNotFoundException   Nếu API trả về 404
     * @throws ExternalSystemException    Nếu API trả về lỗi khác hoặc không kết nối được
     */
    private function callApi(string $method, string $endpoint): array
    {
        return $this->circuitBreaker->execute(function () use ($method, $endpoint) {
            try {
                $token = $this->authClient->getToken();
                $url = $this->resolveEndpointUrl($endpoint);

                Log::info("Gửi request {$method} đến University System: {$url}\n");

                $response = Http::timeout($this->timeoutSeconds)
                    ->retry(2, 300)
                    ->withToken($token)
                    ->send($method, $url);

                // Nếu token hết hạn – làm mới và thử lại một lần
                if ($response->status() === 401) {
                    Log::warning("[StudentInfoApiAdapter] Token hết hạn, đang làm mới...");
                    $this->authClient->invalidateToken();
                    $token    = $this->authClient->getToken();
                    $response = Http::timeout($this->timeoutSeconds)
                        ->withToken($token)
                        ->send($method, $url);
                }

                if ($response->status() === 404) {
                    // Trích mã sinh viên từ URL (endpoint dạng /api/students/{id})
                    preg_match('/students\/([^\/]+)/', $endpoint, $matches);
                    throw new StudentNotFoundException($matches[1] ?? 'unknown');
                }

                if (! $response->successful()) {
                    Log::error("[StudentInfoApiAdapter] Lỗi từ University System", [
                        'endpoint' => $endpoint,
                        'status'   => $response->status(),
                        'body'     => $response->body(),
                    ]);
                    throw new ExternalSystemException(
                        'University System trả về lỗi: ' . $response->json('message', 'Unknown')
                    );
                }

                return $response->json();

            } catch (StudentNotFoundException | ExternalSystemException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error("[StudentInfoApiAdapter] Lỗi kết nối", ['error' => $e->getMessage()]);
                throw new ExternalSystemException('Không thể kết nối đến University System: ' . $e->getMessage());
            }
        });
    }

    private function resolveLoginUrl(): string
    {
        $override = $this->loginUrl ? trim($this->loginUrl) : '';
        if ($override !== '') {
            return $override;
        }

        return rtrim($this->baseUrl, '/') . '/api/student/login';
    }

    private function resolveEndpointUrl(string $endpoint): string
    {
        if (preg_match('/^https?:\/\//', $endpoint) === 1) {
            return $endpoint;
        }

        $baseUrl = rtrim($this->resolveStudentInfoBaseUrl(), '/');
        $normalizedEndpoint = $endpoint;

        if (str_contains($baseUrl, '/api/students') && str_starts_with($endpoint, '/api/students')) {
            $normalizedEndpoint = substr($endpoint, strlen('/api/students'));
        }

        return $baseUrl . $normalizedEndpoint;
    }

    private function resolveStudentInfoBaseUrl(): string
    {
        $override = $this->studentInfoBaseUrl ? trim($this->studentInfoBaseUrl) : '';

        if ($override === '' || str_contains($override, '?')) {
            return $this->baseUrl;
        }

        return $override;
    }
}
