<?php

namespace App\Domain\Entities;

use App\Domain\Enums\PaymentStatus;
use Carbon\Carbon;

/**
 * Domain Entity – Thanh toán cho giảng viên dạy học bổ sung
 *
 * Tương ứng bảng PAYMENT trong ERD.
 * Được tạo tự động sau khi lớp học bổ sung kết thúc.
 *
 * @property int         $id              ID thanh toán
 * @property int         $teacherId       FK → TEACHER
 * @property int         $tutoringTermId  FK → TUTORING_TERM
 * @property float       $totalHours      Tổng số giờ dạy
 * @property float       $amount          Số tiền thanh toán (VND)
 * @property string      $status          Trạng thái: pending | paid | cancelled
 * @property Carbon      $createdAt       Thời điểm tạo bản ghi thanh toán
 */
class Payment
{

    public function __construct(
        public readonly ?int    $id,
        public readonly int     $teacherId,
        public readonly int     $tutoringTermId,
        public readonly ?float  $totalHours = null,
        public readonly ?float  $unitPrice = null,
        public readonly ?float  $coefficient = null,
        public readonly ?float  $amount = null,
        public PaymentStatus|string $status = PaymentStatus::PENDING,
        public readonly Carbon  $createdAt = new Carbon(),
    ) {
        if (is_string($status)) {
            $status = PaymentStatus::from($status);
        }
        $this->status = $status;
    }

    /**
     * Đánh dấu đã thanh toán.
     *
     * @throws \DomainException Nếu không ở trạng thái pending
     */
    public function markAsPaid(): void
    {
        if ($this->status !== PaymentStatus::PENDING) {
            throw new \DomainException('Chỉ có thể xác nhận thanh toán cho khoản ở trạng thái chờ.');
        }

        $this->status = PaymentStatus::PAID;
    }

    /**
     * Hủy khoản thanh toán.
     *
     * @throws \DomainException Nếu đã thanh toán xong
     */
    public function cancel(): void
    {
        if ($this->status === PaymentStatus::PAID) {
            throw new \DomainException('Không thể hủy khoản thanh toán đã hoàn thành.');
        }

        $this->status = PaymentStatus::CANCELLED;
    }

    /**
     * Số tiền hiển thị dạng có định dạng (VD: "1.500.000 VND").
     */
    public function formattedAmount(): string
    {
        return number_format($this->amount, 0, ',', '.') . ' VND';
    }

    /**
     * Đơn giá mỗi giờ dạy.
     */
    public function hourlyRate(): float
    {
        if ($this->totalHours <= 0 || $this->totalHours === null) {
            return 0.0;
        }

        return round(($this->amount ?? 0) / $this->totalHours, 2);
    }
}
