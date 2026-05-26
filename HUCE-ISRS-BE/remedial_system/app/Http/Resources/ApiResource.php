<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON resource chuẩn API – field snake_case (mặc định Eloquent).
 */
abstract class ApiResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
