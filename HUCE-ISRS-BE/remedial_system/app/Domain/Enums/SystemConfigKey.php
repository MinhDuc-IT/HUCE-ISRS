<?php

namespace App\Domain\Enums;

enum SystemConfigKey: string
{
    case DEFAULT_PERIODS      = 'default_periods';
    case MAIL_SUMMARY_SUBJECT = 'mail_summary_subject';
    case MAIL_SUMMARY_BODY    = 'mail_summary_body';

    public function description(): string
    {
        return match ($this) {
            self::DEFAULT_PERIODS      => 'Số tiết mặc định khi đăng ký phụ đạo',
            self::MAIL_SUMMARY_SUBJECT => 'Tiêu đề email tóm tắt gửi bộ môn',
            self::MAIL_SUMMARY_BODY    => 'Nội dung email tóm tắt gửi bộ môn',
        };
    }
}
