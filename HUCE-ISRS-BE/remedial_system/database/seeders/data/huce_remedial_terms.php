<?php

/**
 * Seed đợt phụ đạo HUCE — theo mốc thời gian trong README (2022–2024).
 *
 * Định dạng mỗi dòng (khớp cột remedial_terms):
 * [name, year, semester, start_date, end_date, registration_start, registration_end, status, is_current_term]
 *
 * Quy ước (theo README + RemedialTerm::getLogicStatus):
 * - registration_start / registration_end = "thời gian đợt phụ đạo" (cửa đăng ký).
 * - start_date / end_date = khoảng lớp phụ đạo diễn ra (quanh ngày thi).
 * - status: 0=DRAFT, 1=REGISTRATION_OPEN, 2=ACTIVE, 3=COMPLETED, 4=CANCELLED.
 *
 * Với FAKE_TODAY = 2024-02-07 (FakeTodayMiddleware):
 * - HK1 block2 2023-24 (ĐK đã đóng 4/12/2023, ACTIVE) = ĐỢT HIỆN TẠI:
 *   test PHÂN CÔNG GIẢNG VIÊN (can_assign_lecturer = true vì cửa đăng ký đã đóng).
 * - HK2 block1 2023-24 (ĐK 4/2–4/3/2024): đang trong khung đăng ký nhưng KHÔNG phải
 *   đợt hiện tại; để test SV đăng ký thì đổi is_current_term sang đợt này.
 */
return [
    // ── Năm học 2022-2023 (đã hoàn thành) ─────────────────────────────
    ['HK1 Block 2 (2022-2023)', 2022, 2,
        '2023-01-03', '2023-01-14', '2022-12-03', '2023-01-03', 3, false],
    ['HK2 Block 1 (2022-2023)', 2022, 3,
        '2023-04-13', '2023-04-27', '2023-03-13', '2023-04-13', 3, false],
    ['HK2 Block 2 (2022-2023)', 2022, 4,
        '2023-06-21', '2023-07-01', '2023-05-21', '2023-06-21', 3, false],

    // ── Năm học 2023-2024 ─────────────────────────────────────────────
    ['HK1 Block 1 (2023-2024)', 2023, 1,
        '2023-09-28', '2023-10-06', '2023-08-28', '2023-09-28', 3, false],

    // ĐỢT HIỆN TẠI: ĐK đã đóng (4/12/2023), đợt ACTIVE → test phân công GV.
    ['HK1 Block 2 (2023-2024)', 2023, 2,
        '2023-12-04', '2023-12-18', '2023-11-04', '2023-12-04', 2, true],

    // Để nháp (DRAFT) nên KHÔNG được findCurrent() chọn → đợt hiện tại là HK1 Block 2.
    // Muốn test SV đăng ký: đổi status=1 và dời registration_start <= FAKE_TODAY.
    ['HK2 Block 1 (2023-2024)', 2023, 3,
        '2024-03-20', '2024-03-22', '2024-02-20', '2024-03-20', 0, false],

    // Chưa tới cửa đăng ký (nháp).
    ['HK2 Block 2 (2023-2024)', 2023, 4,
        '2024-05-14', '2024-05-29', '2024-04-14', '2024-05-14', 0, false],
];
