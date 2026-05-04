<?php

namespace App\Domain\Ports;

use App\Domain\Entities\CourseResult;
use App\Domain\Entities\StudentInfo;

/**
 * Port (interface) định nghĩa cổng giao tiếp với hệ thống trường đại học.
 *
 * Đây là ranh giới giữa Domain và Infrastructure.
 * Mọi tương tác với University System phải đi qua interface này.
 * Infrastructure sẽ implement interface này thông qua StudentInfoApiAdapter.
 */
interface StudentInfoPort
{
    /**
     * Lấy thông tin sinh viên từ hệ thống trường.
     *
     * @param  string $studentCode Mã sinh viên (MaSinhVien)
     * @return StudentInfo       Domain entity thông tin sinh viên
     *
     * @throws \App\Domain\Exceptions\StudentNotFoundException
     * @throws \App\Domain\Exceptions\ExternalSystemException
     */
    public function getStudent(string $studentCode): StudentInfo;

    /**
     * Lấy danh sách môn học (kết quả học tập) của sinh viên từ hệ thống trường.
     *
     * @param  string         $studentCode Mã sinh viên
     * @return CourseResult[]            Mảng domain entity kết quả môn học
     *
     * @throws \App\Domain\Exceptions\StudentNotFoundException
     * @throws \App\Domain\Exceptions\ExternalSystemException
     */
    public function getCourses(string $studentCode): array;

    /**
     * Xác thực thông tin đăng nhập của sinh viên với University System.
     *
     * @param string $studentCode Mã sinh viên
     * @param string $password    Mật khẩu
     * @return bool               True nếu hợp lệ, False nếu sai
     *
     * @throws \App\Domain\Exceptions\ExternalSystemException
     */
    public function verifyCredentials(string $studentCode, string $password): bool;
}
