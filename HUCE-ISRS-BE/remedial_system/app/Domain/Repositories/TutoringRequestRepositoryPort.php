<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\TutoringRequest;

/**
 * Port (Interface) cho Repository quản lý đơn đăng ký phụ đạo.
 */
interface TutoringRequestRepositoryPort
{
    /**
     * Lưu đơn đăng ký mới hoặc cập nhật.
     */
    public function save(TutoringRequest $request): TutoringRequest;

    /**
     * Cập nhật đơn đăng ký hiện có.
     */
    public function update(TutoringRequest $request): void;

    /**
     * Tìm đơn đăng ký theo ID.
     */
    public function findById(int $id): ?TutoringRequest;

    /**
     * Kiểm tra xem sinh viên đã có đơn đăng ký môn này trong học kỳ này chưa
     * (trạng thái pending hoặc approved).
     */
    public function existsActiveRequest(int $studentId, int $courseId, int $tutoringTermId): bool;

    /**
     * Lấy danh sách đăng ký của sinh viên.
     * @return TutoringRequest[]
     */
    public function findByStudent(int $studentId): array;
}
