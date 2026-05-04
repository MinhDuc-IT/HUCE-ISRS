<?php

namespace App\Domain\Enums;

enum SystemConfigKey: string
{
    case MIN_STUDENTS_PER_CLASS = 'min_students_per_class';
    case MAX_STUDENTS_PER_CLASS = 'max_students_per_class';
    case DEFAULT_PERIODS        = 'default_periods';
    case MAIL_SUMMARY_SUBJECT   = 'mail_summary_subject';
    case MAIL_SUMMARY_BODY      = 'mail_summary_body';

    public function description(): string
    {
        return match($this) {
            self::MIN_STUDENTS_PER_CLASS => 'Sĩ số tối thiểu để mở lớp',
            self::MAX_STUDENTS_PER_CLASS => 'Sĩ số tối đa mặc định',
            self::DEFAULT_PERIODS        => 'Số tiết mặc định cho một môn',
            self::MAIL_SUMMARY_SUBJECT   => 'Tiêu đề email tóm tắt gửi bộ môn',
            self::MAIL_SUMMARY_BODY      => 'Nội dung email tóm tắt gửi bộ môn',
        };
    }
}
