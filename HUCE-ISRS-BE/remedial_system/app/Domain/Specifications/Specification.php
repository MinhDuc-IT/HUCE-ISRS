<?php

namespace App\Domain\Specifications;

interface Specification
{
    /**
     * Kiểm tra xem đối tượng có thỏa mãn quy tắc nghiệp vụ không.
     */
    public function isSatisfiedBy(mixed $candidate): bool;

    /**
     * Lấy thông báo lỗi nếu không thỏa mãn.
     */
    public function getMessage(): string;
}
