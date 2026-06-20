<?php

namespace App\Domain\Entities\TermStates;

use App\Domain\Enums\RemedialTermStatus;

/**
 * Trạng thái NHÁP – Đợt phụ đạo mới được tạo, chưa công bố.
 *
 * Quy tắc:
 *  - Cho phép cập nhật mọi trường (không giới hạn).
 *  - Có thể chuyển sang: REGISTRATION_OPEN, ACTIVE (bỏ qua đăng ký), CANCELLED.
 *  - KHÔNG thể chuyển thẳng sang COMPLETED từ DRAFT.
 */
class DraftState extends BaseTermState
{
    public function getStatus(): RemedialTermStatus
    {
        return RemedialTermStatus::DRAFT;
    }

    public function validateUpdate(array $data): void
    {
        // Trạng thái DRAFT cho phép cập nhật tất cả các trường, không có giới hạn nào.
    }

    public function nextStatus(): ?RemedialTermStatus
    {
        return null;
    }

    public function transitionTo(RemedialTermStatus $status): void
    {
        match ($status) {
            RemedialTermStatus::REGISTRATION_OPEN => $this->openRegistration(),
            RemedialTermStatus::ACTIVE => $this->activate(),
            RemedialTermStatus::CANCELLED => $this->cancel(),
            default => throw new \DomainException("Không thể chuyển sang trạng thái {$status->description()} từ trạng thái: {$this->getStatus()->description()}"),
        };
    }

    /**
     * Mở thời gian đăng ký.
     * Yêu cầu: đã cấu hình registration_start và registration_end trên entity.
     * ngày hiện tại phải >= ngày bắt đầu đăng ký.
     *
     * @throws \DomainException nếu các điều kiện ngày tháng không hợp lệ.
     */
    public function openRegistration(): void
    {
        if ($this->term->registrationStart === null || $this->term->registrationEnd === null) {
            throw new \DomainException(
                'Không thể mở đăng ký: vui lòng cấu hình ngày bắt đầu và ngày kết thúc đăng ký trước.'
            );
        }

        if ($this->term->registrationEnd->lessThanOrEqualTo($this->term->registrationStart)) {
            throw new \DomainException(
                'Không thể mở đăng ký: ngày kết thúc đăng ký phải sau ngày bắt đầu đăng ký.'
            );
        }

        if (now()->lessThan($this->term->registrationStart)) {
            throw new \DomainException(
                'Không thể mở đăng ký: thời gian đăng ký chưa đến (ngày hiện tại phải sau hoặc bằng ngày bắt đầu đăng ký).'
            );
        }
    }

    /**
     * Chuyển thẳng sang ACTIVE (bỏ qua giai đoạn đăng ký).
     * Yêu cầu: đã cấu hình start_date và end_date trên entity.
     *
     * @throws \DomainException nếu chưa cấu hình ngày học.
     */
    public function activate(): void
    {
        if ($this->term->startDate === null || $this->term->endDate === null) {
            throw new \DomainException(
                'Không thể bắt đầu đợt: vui lòng cấu hình ngày bắt đầu và ngày kết thúc đợt phụ đạo trước.'
            );
        }

        if ($this->term->endDate->lessThanOrEqualTo($this->term->startDate)) {
            throw new \DomainException(
                'Không thể bắt đầu đợt: ngày kết thúc phải sau ngày bắt đầu.'
            );
        }
    }

    /**
     * Huỷ đợt khi còn ở trạng thái nháp – luôn được phép.
     */
    public function cancel(): void
    {
        // Luôn cho phép huỷ từ trạng thái DRAFT.
    }
}
