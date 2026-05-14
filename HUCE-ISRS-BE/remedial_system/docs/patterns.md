# Design Patterns trong Backend Remedial System

Tài liệu này mô tả các pattern đã có trong repo hiện tại và các pattern đề xuất để mở rộng kiến trúc backend đăng ký phụ đạo. Mục tiêu là giữ hệ thống dễ bảo trì, dễ thay đổi nghiệp vụ và giảm phụ thuộc giữa các tầng.

## 1. Pattern đã áp dụng trong repo

### 1.1. Adapter Pattern

**Vị trí hiện có**

- `app/Domain/Ports/StudentInfoPort.php`
- `app/Infrastructure/Adapters/StudentInfoApiAdapter.php`
- `app/Infrastructure/Adapters/CachedStudentInfoAdapter.php`

**Ý nghĩa**

Backend đăng ký phụ đạo không gọi trực tiếp hệ thống đào tạo. Thay vào đó, application service chỉ phụ thuộc vào `StudentInfoPort`. Khi hệ thống đào tạo đổi API, chỉ cần sửa hoặc thay adapter.

**Code trong repo**

```php
interface StudentInfoPort
{
    public function getStudent(string $studentCode): StudentInfo;

    public function getCourses(string $studentCode): array;

    public function verifyCredentials(string $studentCode, string $password): bool;
}
```

```php
class StudentInfoApiAdapter implements StudentInfoPort
{
    public function getStudent(string $studentCode): StudentInfo
    {
        // Gọi API University System và map dữ liệu về StudentInfo nội bộ.
    }
}
```

**Lợi ích**

- Tuân thủ Dependency Inversion Principle.
- Dễ thay đổi nguồn dữ liệu sinh viên.
- Dễ mock khi viết test.

---

### 1.2. Repository Pattern

**Vị trí hiện có**

- `app/Domain/Repositories/TutoringRequestRepositoryPort.php`
- `app/Infrastructure/Repositories/EloquentTutoringRequestRepository.php`
- Các repository khác: `CourseRepositoryPort`, `StudentRepositoryPort`, `TutoringTermRepositoryPort`.

**Ý nghĩa**

Application service không thao tác trực tiếp với Eloquent model. Việc truy vấn/lưu dữ liệu được ẩn sau repository interface.

**Code trong repo**

```php
interface TutoringRequestRepositoryPort
{
    public function save(TutoringRequest $request): TutoringRequest;

    public function update(TutoringRequest $request): void;

    public function findById(int $id): ?TutoringRequest;

    public function findByStudent(int $studentId): array;

    public function existsActiveRequest(int $studentId, int $courseId, int $tutoringTermId): bool;
}
```

```php
class EloquentTutoringRequestRepository implements TutoringRequestRepositoryPort
{
    public function findByStudent(int $studentId): array
    {
        $models = EloquentTutoringRequest::where('StudentId', $studentId)
            ->orderBy('CreatedAt', 'desc')
            ->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->toArray();
    }
}
```

**Lợi ích**

- Tách domain/application khỏi database framework.
- Dễ thay Eloquent bằng query khác nếu cần.
- Dễ kiểm thử service bằng fake repository.

---

### 1.3. State Pattern

**Vị trí hiện có**

- `app/Domain/Entities/TutoringRequest.php`
- `app/Domain/States/TutoringRequest/RequestState.php`
- `PendingState.php`, `ApprovedState.php`, `RejectedState.php`, `PaidState.php`

**Ý nghĩa**

Trạng thái đơn đăng ký phụ đạo có nhiều quy tắc chuyển trạng thái. State Pattern giúp gom logic chuyển trạng thái vào từng state thay vì dùng nhiều `if/else`.

**Code trong repo**

```php
class TutoringRequest
{
    private RequestState $state;

    public function approve(): void
    {
        $this->state->approve();
    }

    public function reject(string $reason): void
    {
        $this->state->reject($reason);
        $this->note = $reason;
    }
}
```

```php
interface RequestState
{
    public function approve(): void;

    public function reject(string $reason): void;

    public function pay(): void;
}
```

**Lợi ích**

- Tránh chuyển trạng thái sai.
- Dễ thêm trạng thái mới như `CancelledState`.
- Logic nghiệp vụ nằm trong domain thay vì controller.

---

### 1.4. Dependency Injection / Service Provider

**Vị trí hiện có**

- `app/Providers/RemedialServiceProvider.php`
- `app/Providers/AppServiceProvider.php`

**Ý nghĩa**

Laravel container quản lý việc bind interface với implementation. Đây là cách tổ chức gần với Singleton ở cấp container, vì service được cấu hình tập trung và được inject tự động.

**Code mẫu theo Laravel**

```php
class RemedialServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            StudentInfoPort::class,
            CachedStudentInfoAdapter::class
        );

        $this->app->bind(
            TutoringRequestRepositoryPort::class,
            EloquentTutoringRequestRepository::class
        );
    }
}
```

**Lợi ích**

- Controller/service không cần tự tạo dependency.
- Có thể thay implementation qua binding.
- Phù hợp nguyên tắc Dependency Inversion.

---

## 2. Pattern đề xuất áp dụng thêm

### 2.1. Facade Pattern cho luồng đăng ký phụ đạo

**Trạng thái hiện tại**

Repo đang có `RemedialRegistrationService`. Service này đang xử lý nhiều bước: lấy sinh viên, lấy môn học, kiểm tra điều kiện, kiểm tra trùng, tạo đơn và lưu.

**Đề xuất**

Tách thành `RemedialRegistrationFacade` để điều phối use case, còn validation, factory, repository, event được giao cho các class chuyên trách.

**Code mẫu**

```php
final class RemedialRegistrationFacade
{
    public function __construct(
        private readonly RegistrationValidator $validator,
        private readonly RegistrationFactory $factory,
        private readonly TutoringRequestRepositoryPort $requests,
        private readonly EventBus $events,
        private readonly UnitOfWork $unitOfWork,
    ) {}

    public function register(string $studentCode, string $courseCode): TutoringRequest
    {
        return $this->unitOfWork->transaction(function () use ($studentCode, $courseCode) {
            $context = $this->validator->validateRegistration($studentCode, $courseCode);

            $request = $this->factory->createPending(
                $context->studentId,
                $context->courseId,
                $context->termId,
            );

            $saved = $this->requests->save($request);
            $this->events->publish(new RegistrationCreatedEvent($saved->id, $saved->studentId, $saved->courseId));

            return $saved;
        });
    }
}
```

**Khi nào nên áp dụng**

- Khi đăng ký cần thêm gửi thông báo, tính phí, audit log, transaction.
- Khi controller/service hiện tại bắt đầu quá dài.

---

### 2.2. Factory Method cho tạo đơn đăng ký

**Trạng thái hiện tại**

Trong `RemedialRegistrationService`, đơn đăng ký đang được tạo trực tiếp bằng `new TutoringRequest(...)`.

**Đề xuất**

Tạo `RegistrationFactory` để gom logic khởi tạo đơn.

**Code mẫu**

```php
final class RegistrationFactory
{
    public function createPending(int $studentId, int $courseId, int $termId): TutoringRequest
    {
        return new TutoringRequest(
            id: null,
            studentId: $studentId,
            courseId: $courseId,
            tutoringTermId: $termId,
            requestedPeriods: null,
            status: TutoringRequestStatus::PENDING,
            createdAt: now(),
        );
    }
}
```

**Lợi ích**

- Tránh lặp logic tạo entity.
- Dễ thêm field mới như `source`, `fee`, `note`, `createdBy`.

---

### 2.3. Strategy Pattern cho tính giá tiền phụ đạo

**Bối cảnh nghiệp vụ**

`TutoringTerm` có các tham số:

- `heSoPD`: hệ số đợt phụ đạo.
- `donGia1Tiet`: đơn giá một tiết.
- `heSoDonGia`: hệ số đơn giá.

Không nên hard-code công thức trong `PaymentService` hoặc `TutoringTerm`, vì công thức có thể thay đổi theo từng năm học hoặc quy định.

**Đề xuất**

Dùng `PricingStrategy` để thay đổi công thức tính tiền linh hoạt.

**Code mẫu**

```php
final class PricingInput
{
    public function __construct(
        public readonly int $heSoPD,
        public readonly int $donGia1Tiet,
        public readonly float $heSoDonGia,
        public readonly int $totalPeriods,
        public readonly int $studentCount,
    ) {}
}
```

```php
interface PricingStrategy
{
    public function calculate(PricingInput $input): int;
}
```

```php
final class DefaultRemedialPricingStrategy implements PricingStrategy
{
    public function calculate(PricingInput $input): int
    {
        return (int) round(
            $input->totalPeriods
            * $input->donGia1Tiet
            * $input->heSoPD
            * $input->heSoDonGia
        );
    }
}
```

```php
final class PricingStrategyFactory
{
    public function make(TutoringTerm $term): PricingStrategy
    {
        return match ($term->pricing_type ?? 'default') {
            'discount' => new DiscountPricingStrategy(),
            'progressive' => new ProgressivePricingStrategy(),
            default => new DefaultRemedialPricingStrategy(),
        };
    }
}
```

```php
final class PricingService
{
    public function __construct(
        private readonly PricingStrategyFactory $factory,
    ) {}

    public function calculateTeacherPayment(TutoringClass $class, TutoringTerm $term): int
    {
        $strategy = $this->factory->make($term);

        return $strategy->calculate(new PricingInput(
            heSoPD: $term->heSoPD,
            donGia1Tiet: $term->donGia1Tiet,
            heSoDonGia: $term->heSoDonGia,
            totalPeriods: $class->totalPeriods,
            studentCount: $class->studentCount,
        ));
    }
}
```

**Lợi ích**

- Khi đổi công thức, thêm strategy mới thay vì sửa service cũ.
- Dễ test từng công thức riêng.
- Phù hợp Open/Closed Principle.

---

### 2.4. Pub-Sub / Observer Pattern cho thông báo

**Trạng thái hiện tại**

Repo có model `Notification`, nhưng class diagram ban đầu chưa thể hiện luồng thông báo.

**Đề xuất**

Khi nghiệp vụ xảy ra, service phát event. Listener nhận event và tạo notification. Như vậy đăng ký, thanh toán, email không phụ thuộc chặt vào nhau.

**Code mẫu**

```php
interface DomainEvent
{
    public function occurredAt(): DateTimeImmutable;
}
```

```php
final class RegistrationCreatedEvent implements DomainEvent
{
    public function __construct(
        public readonly int $requestId,
        public readonly int $studentId,
        public readonly int $courseId,
        private readonly DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
```

```php
interface EventBus
{
    public function publish(DomainEvent $event): void;
}
```

```php
final class LaravelEventBus implements EventBus
{
    public function publish(DomainEvent $event): void
    {
        event($event);
    }
}
```

```php
final class NotificationListener
{
    public function __construct(
        private readonly NotificationFactory $factory,
        private readonly NotificationService $notifications,
    ) {}

    public function handle(RegistrationCreatedEvent $event): void
    {
        $notification = $this->factory->registrationCreated($event);
        $this->notifications->notify($notification);
    }
}
```

**Lợi ích**

- Đăng ký không cần biết gửi email hay lưu notification thế nào.
- Dễ thêm listener mới: audit log, thống kê, email, SMS.
- Giảm coupling giữa các module.

---

### 2.5. Factory Method cho Notification

**Đề xuất**

Dùng factory để chuẩn hóa nội dung thông báo theo từng nghiệp vụ.

**Code mẫu**

```php
final class NotificationFactory
{
    public function registrationCreated(RegistrationCreatedEvent $event): Notification
    {
        return new Notification(
            receiverId: $event->studentId,
            type: 'registration_created',
            title: 'Đăng ký phụ đạo thành công',
            content: "Đơn đăng ký #{$event->requestId} đã được tạo và đang chờ duyệt.",
            status: 'unread',
            createdAt: now(),
        );
    }

    public function registrationApproved(RegistrationApprovedEvent $event): Notification
    {
        return new Notification(
            receiverId: $event->studentId,
            type: 'registration_approved',
            title: 'Đơn đăng ký đã được duyệt',
            content: "Đơn đăng ký #{$event->requestId} đã được bộ môn duyệt.",
            status: 'unread',
            createdAt: now(),
        );
    }
}
```

**Lợi ích**

- Text thông báo tập trung một nơi.
- Dễ chuẩn hóa đa ngôn ngữ hoặc template.
- Service nghiệp vụ không bị rải chuỗi thông báo.

---

### 2.6. Strategy / Bridge cho kênh gửi Notification

**Đề xuất**

Tách kênh gửi thông báo thành interface `NotificationChannel`.

**Code mẫu**

```php
interface NotificationChannel
{
    public function send(Notification $notification): void;
}
```

```php
final class DatabaseNotificationChannel implements NotificationChannel
{
    public function __construct(
        private readonly NotificationRepository $notifications,
    ) {}

    public function send(Notification $notification): void
    {
        $this->notifications->save($notification);
    }
}
```

```php
final class EmailNotificationChannel implements NotificationChannel
{
    public function send(Notification $notification): void
    {
        // Gửi email theo receiverId hoặc email đã resolve.
    }
}
```

```php
final class NotificationService
{
    /**
     * @param NotificationChannel[] $channels
     */
    public function __construct(
        private readonly array $channels,
    ) {}

    public function notify(Notification $notification): void
    {
        foreach ($this->channels as $channel) {
            $channel->send($notification);
        }
    }
}
```

**Lợi ích**

- Thêm SMS/push notification không sửa service chính.
- Có thể bật/tắt kênh theo config.
- Phù hợp Open/Closed Principle.

---

### 2.7. Proxy Pattern cho hệ thống đào tạo bên ngoài

**Trạng thái hiện tại**

Repo có `CircuitBreaker` trong `app/Infrastructure/Common/CircuitBreaker.php`. Có thể dùng class này để bọc adapter gọi University System.

**Code mẫu**

```php
final class CircuitBreakerStudentInfoProxy implements StudentInfoPort
{
    public function __construct(
        private readonly StudentInfoPort $inner,
        private readonly CircuitBreaker $breaker,
    ) {}

    public function getStudent(string $studentCode): StudentInfo
    {
        return $this->breaker->call(
            fn () => $this->inner->getStudent($studentCode)
        );
    }

    public function getCourses(string $studentCode): array
    {
        return $this->breaker->call(
            fn () => $this->inner->getCourses($studentCode)
        );
    }

    public function verifyCredentials(string $studentCode, string $password): bool
    {
        return $this->breaker->call(
            fn () => $this->inner->verifyCredentials($studentCode, $password)
        );
    }
}
```

**Lợi ích**

- Bảo vệ hệ thống khi University System chậm hoặc lỗi.
- Tránh gọi lặp lại API ngoài khi biết đang lỗi.
- Tăng độ ổn định cho đăng nhập và đăng ký.

---

### 2.8. Unit of Work cho transaction

**Đề xuất**

Đăng ký, tạo payment hoặc phát event có thể cần transaction. Nên bọc bằng `UnitOfWork`.

**Code mẫu**

```php
interface UnitOfWork
{
    public function transaction(Closure $callback): mixed;
}
```

```php
final class LaravelUnitOfWork implements UnitOfWork
{
    public function transaction(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
```

**Lợi ích**

- Application service không phụ thuộc trực tiếp `DB::transaction`.
- Dễ test transaction bằng fake implementation.
- Tránh dữ liệu dang dở khi một bước thất bại.

---

## 3. Tóm tắt áp dụng SOLID

- **Single Responsibility**: Controller nhận request, Facade điều phối use case, Validator kiểm tra điều kiện, Factory tạo entity, Repository lưu dữ liệu.
- **Open/Closed**: Thêm công thức tính tiền bằng `PricingStrategy` mới, không sửa `PaymentService`.
- **Liskov Substitution**: Có thể thay `StudentInfoApiAdapter` bằng adapter khác miễn implement `StudentInfoPort`.
- **Interface Segregation**: Tách các port nhỏ: `StudentInfoPort`, `TutoringRequestRepositoryPort`, `NotificationRepository`, `PaymentRepository`.
- **Dependency Inversion**: Application service phụ thuộc interface thay vì Eloquent hoặc HTTP client cụ thể.

## 4. Gợi ý thứ tự triển khai

1. Sửa contract giữa FE và BE cho luồng đăng ký.
2. Thêm `RegistrationFactory`.
3. Tách validation ra `RegistrationValidator`.
4. Thêm `UnitOfWork`.
5. Thêm event và notification listener.
6. Tách tính tiền bằng `PricingStrategy`.
7. Bọc `StudentInfoPort` bằng cache và circuit breaker ổn định hơn.
