<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

abstract class ApiFormRequest extends FormRequest
{
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Không có quyền truy cập.');
    }
}
