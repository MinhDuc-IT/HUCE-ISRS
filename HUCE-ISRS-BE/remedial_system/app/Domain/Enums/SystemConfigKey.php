<?php

namespace App\Domain\Enums;

enum SystemConfigKey: string
{
    case DEFAULT_PERIODS      = 'default_periods';
    case MAIL_SUMMARY_SUBJECT = 'mail_summary_subject';
    case MAIL_SUMMARY_BODY    = 'mail_summary_body';
    case SENDER_EMAIL         = 'sender_email';
    case SENDER_PASSWORD      = 'sender_password';
    case ADMIN_EMAIL          = 'admin_email';
    case WEEKS_FROM_REG       = 'weeks_from_registration';
    case WS_LOGIN             = 'ws_login';
    case WS_STUDENT_INFO      = 'ws_student_info';
    case WS_HOST              = 'ws_host';

    public function description(): string
    {
        return match ($this) {
            self::DEFAULT_PERIODS      => 'Số tiết mặc định khi đăng ký phụ đạo',
            self::MAIL_SUMMARY_SUBJECT => 'Tiêu đề email tóm tắt gửi bộ môn',
            self::MAIL_SUMMARY_BODY    => 'Nội dung email tóm tắt gửi bộ môn',
            self::SENDER_EMAIL         => 'Email dùng để gửi email hệ thống',
            self::SENDER_PASSWORD      => 'Mật khẩu email gửi',
            self::ADMIN_EMAIL          => 'Email đơn vị quản lý',
            self::WEEKS_FROM_REG       => 'Số tuần tính từ tuần đăng ký',
            self::WS_LOGIN             => 'Webservice đăng nhập',
            self::WS_STUDENT_INFO      => 'Webservice lấy thông tin sinh viên',
            self::WS_HOST              => 'Host webservice học phụ đạo',
        };
    }
}
