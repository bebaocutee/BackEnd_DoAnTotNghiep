<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'date_of_birth' => $this->teacherInfo->date_of_birth ?? null,
            'experience' => $this->teacherInfo->experience ?? null,
            'work_unit' => $this->teacherInfo->work_unit ?? null,
            'introduction' => $this->teacherInfo->introduction ?? null
        ]);
    }
}
