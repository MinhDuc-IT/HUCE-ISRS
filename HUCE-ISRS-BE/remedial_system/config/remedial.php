<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cấu hình kết nối đến University System
    |--------------------------------------------------------------------------
    */

    // URL gốc của University System (không có trailing slash)
    'university_base_url' => env('UNIVERSITY_BASE_URL', 'http://localhost:8001'),

    // Client credentials để xác thực với University System
    'university_client_id'     => env('UNIVERSITY_CLIENT_ID', 'remedial_system'),
    'university_client_secret' => env('UNIVERSITY_CLIENT_SECRET', ''),

    // Timeout (giây) cho HTTP request đến University System
    'http_timeout' => env('UNIVERSITY_HTTP_TIMEOUT', 15),
];
