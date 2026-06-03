<?php

namespace App\Providers;

use App\Application\Services\Auth\AuthUserPresenter;
use App\Application\Services\Auth\AuthenticateUserService;
use App\Application\Services\Admin\AdminRemedialRegistrationQueryService;
use App\Application\Services\Admin\ManageDepartmentService;
use App\Application\Services\Admin\ManageRemedialTermService;
use App\Application\Services\Admin\ManageSubjectService;
use App\Application\Services\Admin\ManageSystemConfigurationService;
use App\Application\Services\Admin\ManageUserService;
use App\Application\Services\Admin\RemedialStatisticsService;
use App\Application\Services\Department\DepartmentManageRegistrationService;
use App\Application\Services\Department\DepartmentProfileService;
use App\Application\Services\Department\DepartmentRegistrationQueryService;
use App\Application\Services\Department\SendDepartmentSummaryEmailService;
use App\Application\Services\DepartmentService;
use App\Application\Services\RemedialRegistrationService;
use App\Application\Services\StudentProvisioningService;
use App\Application\Services\StudentRegistrationPresenter;
use App\Application\Services\StudentSyncService;
use App\Domain\Enums\SystemConfigKey;
use App\Domain\Ports\External\StudentInfoPort;
use App\Domain\Ports\Persistence\DepartmentRepositoryPort;
use App\Domain\Ports\Persistence\RemedialRegistrationQueryPort;
use App\Domain\Ports\Persistence\RemedialRegistrationRepositoryPort;
use App\Domain\Ports\Persistence\RemedialTermRepositoryPort;
use App\Domain\Ports\Persistence\StudentRepositoryPort;
use App\Domain\Ports\Persistence\SubjectRepositoryPort;
use App\Domain\Ports\Persistence\SystemConfigurationRepositoryPort;
use App\Domain\Ports\Persistence\UserRepositoryPort;
use App\Infrastructure\External\University\CachedStudentInfoAdapter;
use App\Infrastructure\External\University\StudentInfoApiAdapter;
use App\Infrastructure\External\University\UniversityAuthClient;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDepartmentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentRemedialRegistrationQueryRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentRemedialRegistrationRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentRemedialTermRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentStudentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSubjectRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSystemConfigurationRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Events\LecturerAssignedToSubject;
use App\Listeners\SendLecturerAssignmentEmail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class RemedialServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UniversityAuthClient::class, function ($app) {
            $configRepository = $app->make(SystemConfigurationRepositoryPort::class);
            $baseUrl = $this->resolveSystemConfigValue(
                $configRepository,
                SystemConfigKey::WS_HOST->value,
                config('remedial.university_base_url')
            );

            return new UniversityAuthClient(
                baseUrl:        $baseUrl,
                clientId:       config('remedial.university_client_id'),
                clientSecret:   config('remedial.university_client_secret'),
                timeoutSeconds: (int) config('remedial.http_timeout', 10),
            );
        });

        $this->app->bind(StudentInfoPort::class, function ($app) {
            $configRepository = $app->make(SystemConfigurationRepositoryPort::class);
            $baseUrl = $this->resolveSystemConfigValue(
                $configRepository,
                SystemConfigKey::WS_HOST->value,
                config('remedial.university_base_url')
            );
            $loginUrl = $this->resolveSystemConfigValue($configRepository, SystemConfigKey::WS_LOGIN->value, '');
            $studentInfoUrl = $this->resolveSystemConfigValue($configRepository, SystemConfigKey::WS_STUDENT_INFO->value, '');

            $baseAdapter = new StudentInfoApiAdapter(
                authClient:     $app->make(UniversityAuthClient::class),
                baseUrl:        $baseUrl,
                timeoutSeconds: (int) config('remedial.http_timeout', 15),
                loginUrl:       $loginUrl !== '' ? $loginUrl : null,
                studentInfoBaseUrl: $studentInfoUrl !== '' ? $studentInfoUrl : null,
            );

            return new CachedStudentInfoAdapter(
                innerAdapter: $baseAdapter,
                ttlSeconds:   (int) config('remedial.cache_ttl', 3600),
            );
        });

        $this->app->bind(UserRepositoryPort::class, EloquentUserRepository::class);
        $this->app->bind(StudentRepositoryPort::class, EloquentStudentRepository::class);
        $this->app->bind(RemedialRegistrationRepositoryPort::class, EloquentRemedialRegistrationRepository::class);
        $this->app->bind(RemedialRegistrationQueryPort::class, EloquentRemedialRegistrationQueryRepository::class);
        $this->app->bind(SubjectRepositoryPort::class, EloquentSubjectRepository::class);
        $this->app->bind(DepartmentRepositoryPort::class, EloquentDepartmentRepository::class);
        $this->app->bind(RemedialTermRepositoryPort::class, EloquentRemedialTermRepository::class);
        $this->app->bind(SystemConfigurationRepositoryPort::class, EloquentSystemConfigurationRepository::class);

        $this->app->bind(StudentSyncService::class, function ($app) {
            return new StudentSyncService(
                studentInfoPort:   $app->make(StudentInfoPort::class),
                studentRepository: $app->make(StudentRepositoryPort::class),
                subjectRepository: $app->make(SubjectRepositoryPort::class),
            );
        });

        $this->app->bind(StudentProvisioningService::class, function ($app) {
            return new StudentProvisioningService(
                studentInfoPort: $app->make(StudentInfoPort::class),
                userRepository:  $app->make(UserRepositoryPort::class),
                syncService:     $app->make(StudentSyncService::class),
            );
        });

        $this->app->singleton(AuthUserPresenter::class);
        $this->app->singleton(AuthenticateUserService::class);
        $this->app->singleton(DepartmentService::class);
        $this->app->singleton(DepartmentProfileService::class);
        $this->app->singleton(DepartmentRegistrationQueryService::class);
        $this->app->singleton(SendDepartmentSummaryEmailService::class);
        $this->app->singleton(DepartmentManageRegistrationService::class);
        $this->app->singleton(ManageRemedialTermService::class);
        $this->app->singleton(ManageUserService::class);
        $this->app->singleton(ManageDepartmentService::class);
        $this->app->singleton(ManageSubjectService::class);
        $this->app->singleton(ManageSystemConfigurationService::class);
        $this->app->singleton(AdminRemedialRegistrationQueryService::class);
        $this->app->singleton(RemedialStatisticsService::class);
        $this->app->singleton(RemedialRegistrationService::class);
        $this->app->singleton(StudentRegistrationPresenter::class);
    }

    public function boot(): void
    {
        Event::listen(LecturerAssignedToSubject::class, SendLecturerAssignmentEmail::class);
    }

    private function resolveSystemConfigValue(
        SystemConfigurationRepositoryPort $configRepository,
        string $key,
        string $default
    ): string {
        $value = $configRepository->get($key);

        if ($value === null) {
            return $default;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? $default : $trimmed;
    }
}
