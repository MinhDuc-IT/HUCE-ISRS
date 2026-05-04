<?php

namespace App\Domain\Exceptions;

/**
 * Exception – Lỗi khi giao tiếp với hệ thống trường đại học (University System)
 */
class ExternalSystemException extends \RuntimeException
{
    public function __construct(string $message = 'Không thể kết nối với hệ thống trường đại học', int $code = 503)
    {
        parent::__construct($message, $code);
    }
}
