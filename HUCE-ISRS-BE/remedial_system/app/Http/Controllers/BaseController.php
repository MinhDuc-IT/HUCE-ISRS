<?php

namespace App\Http\Controllers;

abstract class BaseController extends Controller
{
    /**
     * Trả về phản hồi thành công theo chuẩn API.
     *
     * @param  mixed  $data    Dữ liệu trả về
     * @param  string $message Thông báo thành công
     * @param  int    $status  HTTP status code
     */
    protected function success(mixed $data = null, string $message = 'Thành công', int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
        ], $status);
    }

    /**
     * Trả về phản hồi lỗi theo chuẩn API.
     *
     * @param  string     $message Thông báo lỗi
     * @param  mixed|null $errors  Chi tiết lỗi validation
     * @param  int        $status  HTTP status code
     */
    protected function error(string $message = 'Có lỗi xảy ra', mixed $errors = null, int $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $status);
    }
}
