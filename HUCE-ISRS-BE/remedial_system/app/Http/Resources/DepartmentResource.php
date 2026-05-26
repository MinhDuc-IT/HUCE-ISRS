<?php

namespace App\Http\Resources;

use App\Domain\Entities\Department as DepartmentEntity;
use Illuminate\Http\Request;

class DepartmentResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $dept = $this->resource;

        if ($dept instanceof DepartmentEntity) {
            return [
                'id'              => $dept->id,
                'department_code' => $dept->departmentCode,
                'department_name' => $dept->departmentName,
                'faculty_code'    => $dept->facultyCode,
                'faculty_name'    => $dept->facultyName,
                'email'           => $dept->email,
                'phone_number'    => $dept->phoneNumber,
            ];
        }

        return parent::toArray($request);
    }
}
