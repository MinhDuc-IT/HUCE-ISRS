<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Application\Services\StudentProvisioningService;
use App\Domain\Ports\StudentInfoPort;
use App\Infrastructure\Adapters\StudentInfoApiAdapter;
use App\Infrastructure\Auth\UniversityAuthClient;
use App\Domain\Repositories\TutoringRequestRepositoryPort;
use App\Domain\Repositories\TutoringClassRepositoryPort;
use App\Domain\Repositories\CourseRepositoryPort;
use App\Domain\Repositories\TeacherRepositoryPort;
use App\Domain\Repositories\DepartmentRepositoryPort;
use App\Domain\Repositories\TutoringTermRepositoryPort;
use App\Infrastructure\Repositories\EloquentTutoringRequestRepository;
use App\Infrastructure\Repositories\EloquentTutoringClassRepository;
use App\Infrastructure\Repositories\EloquentCourseRepository;
use App\Infrastructure\Repositories\EloquentTeacherRepository;
use App\Infrastructure\Repositories\EloquentDepartmentRepository;
use App\Infrastructure\Repositories\EloquentTutoringTermRepository;
use App\Domain\Repositories\SystemConfigRepositoryPort;
use App\Infrastructure\Repositories\EloquentSystemConfigRepository;

/**
 * RemedialServiceProvider – Wire up Ports và Adapters (IoC binding).
 *
 * Đây là nơi duy nhất trong ứng dụng biết các concrete class của Infrastructure.
 * Domain và Application chỉ phụ thuộc vào Interfaces (Ports).
 */
class RemedialServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ─── Bind Auth Client ─────────────────────────────────────────────
        $this->app->singleton(UniversityAuthClient::class, function () {
            return new UniversityAuthClient(
                baseUrl:        config('remedial.university_base_url'),
                clientId:       config('remedial.university_client_id'),
                clientSecret:   config('remedial.university_client_secret'),
                timeoutSeconds: (int) config('remedial.http_timeout', 10),
            );
        });

        // ─── Bind Port → Adapter (Hexagonal Architecture binding) ─────────
        $this->app->bind(StudentInfoPort::class, function ($app) {
            return new StudentInfoApiAdapter(
                authClient:     $app->make(UniversityAuthClient::class),
                baseUrl:        config('remedial.university_base_url'),
                timeoutSeconds: (int) config('remedial.http_timeout', 15),
            );
        });

        // ─── Bind Repositories ────────────────────────────────────────────
        $this->app->bind(TutoringRequestRepositoryPort::class, EloquentTutoringRequestRepository::class);
        $this->app->bind(TutoringClassRepositoryPort::class, EloquentTutoringClassRepository::class);
        $this->app->bind(CourseRepositoryPort::class, EloquentCourseRepository::class);
        $this->app->bind(TeacherRepositoryPort::class, EloquentTeacherRepository::class);
        $this->app->bind(DepartmentRepositoryPort::class, EloquentDepartmentRepository::class);
        $this->app->bind(TutoringTermRepositoryPort::class, EloquentTutoringTermRepository::class);
        $this->app->bind(SystemConfigRepositoryPort::class, EloquentSystemConfigRepository::class);

        // ─── Bind Application Services ────────────────────────────────────
        $this->app->bind(StudentProvisioningService::class, function ($app) {
            return new StudentProvisioningService(
                studentInfoPort: $app->make(StudentInfoPort::class),
            );
        });

        $this->app->singleton(\App\Application\Services\TutoringClassService::class);
        $this->app->singleton(\App\Application\Services\DepartmentService::class);
    }

    public function boot(): void
    {
        // Load routes của module
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
    }
}
