<?php

namespace App\Domain\Exceptions;

/**
 * Exception – Không tìm thấy sinh viên trong hệ thống trường
 */
class StudentNotFoundException extends \RuntimeException
{
    public function __construct(string $studentCode)
    {
        parent::__construct("Không tìm thấy sinh viên với mã: {$studentCode}", 404);
    }
}
