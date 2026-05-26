<?php

namespace App\Http\Resources;

use App\Domain\Entities\Subject as SubjectEntity;
use Illuminate\Http\Request;

class SubjectResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $subject = $this->resource;

        if ($subject instanceof SubjectEntity) {
            return [
                'id'            => $subject->id,
                'subject_code'  => $subject->subjectCode,
                'subjectCode'   => $subject->subjectCode,
                'name'          => $subject->name,
                'credits'       => $subject->credits,
                'department_id' => $subject->departmentId,
            ];
        }

        return parent::toArray($request);
    }
}
