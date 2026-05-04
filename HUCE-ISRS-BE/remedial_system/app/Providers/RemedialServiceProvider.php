<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Application\Services\StudentProvisioningService;
use App\Domain\Ports\StudentInfoPort;
use App\Domain\Repositories\TutoringRequestRepositoryPort;
use App\Infrastructure\Adapters\StudentInfoApiAdapter;
use App\Infrastructure\Auth\UniversityAuthClient;
use App\Infrastructure\Repositories\EloquentTutoringRequestRepository;

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

        // Bind Registration Repository (Lớp quản lý dữ liệu Database nội bộ)
        $this->app->bind(
            TutoringRequestRepositoryPort::class,
            EloquentTutoringRequestRepository::class
        );

        // ─── Bind StudentProvisioningService (Option B – auto-provision) ──
        $this->app->bind(StudentProvisioningService::class, function ($app) {
            return new StudentProvisioningService(
                studentInfoPort: $app->make(StudentInfoPort::class),
            );
        });
    }

    public function boot(): void
    {
        // Load routes của module
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
    }
}
