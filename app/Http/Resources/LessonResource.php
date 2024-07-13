<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'chapter' => $this->chapter->chapter_name ?? null,
            'course' => $this->chapter->course->course_name ?? null,
            'course_id' => $this->chapter->course->id ?? null,
            'questions_count' => $this->questions->count() ?? 0,
        ]);
    }
}
