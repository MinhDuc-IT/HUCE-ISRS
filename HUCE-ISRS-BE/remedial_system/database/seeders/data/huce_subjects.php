<?php

/**
 * Seed data môn học phụ đạo — HUCE ISRS.
 *
 * Định dạng mỗi dòng: [subject_code, name, credits, department_code]
 * - department_code phải khớp department_code trong huce_departments.php
 *   (SubjectSeeder sẽ tra ra department_id tương ứng).
 *
 * Ưu tiên các môn đại cương / cơ sở mà sinh viên hay phải học phụ đạo.
 */
return [
    // Bộ môn Toán học (58)
    ['MTH101', 'Giải tích 1', 3, 58],
    ['MTH102', 'Giải tích 2', 3, 58],
    ['MTH103', 'Đại số tuyến tính', 3, 58],
    ['MTH201', 'Xác suất thống kê', 2, 58],
    ['MTH202', 'Phương trình vi phân', 2, 59],

    // Bộ môn Vật lý (19)
    ['PHY101', 'Vật lý đại cương 1', 3, 19],
    ['PHY102', 'Vật lý đại cương 2', 3, 19],
    ['PHY103', 'Thí nghiệm Vật lý', 1, 19],

    // Bộ môn Sức bền vật liệu (6)
    ['MEC201', 'Sức bền vật liệu 1', 3, 6],
    ['MEC202', 'Sức bền vật liệu 2', 2, 6],

    // Bộ môn Cơ học lý thuyết (2)
    ['MEC101', 'Cơ học lý thuyết', 3, 2],

    // Bộ môn Cơ học kết cấu (1)
    ['STR301', 'Cơ học kết cấu 1', 3, 1],
    ['STR302', 'Cơ học kết cấu 2', 2, 1],

    // Khoa Công nghệ thông tin — Công nghệ phần mềm (54)
    ['INT101', 'Nhập môn lập trình', 3, 54],
    ['INT102', 'Kỹ thuật lập trình', 3, 54],
    ['INT201', 'Công nghệ phần mềm', 3, 54],

    // Khoa học máy tính (60)
    ['INT202', 'Cấu trúc dữ liệu và giải thuật', 3, 60],
    ['INT203', 'Cơ sở dữ liệu', 3, 60],

    // Kỹ thuật hệ thống và Mạng máy tính (55)
    ['INT301', 'Mạng máy tính', 3, 55],

    // Bộ môn tiếng Anh (144)
    ['ENG101', 'Tiếng Anh 1', 3, 144],
    ['ENG102', 'Tiếng Anh 2', 3, 144],
    ['ENG201', 'Tiếng Anh chuyên ngành', 2, 144],
];
