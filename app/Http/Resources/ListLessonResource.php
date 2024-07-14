<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListLessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_name' => $this->course_name,
            'chapters' => $this->chapters->map(function ($chapter) {
                return [
                    'id' => $chapter->id,
                    'chapter_name' => $chapter->chapter_name,
                    'lessons' => $chapter->lessons->map(function ($lesson) {
                        return [
                            'id' => $lesson->id,
                            'lesson_name' => $lesson->lesson_name,
                            'lesson_type' => $lesson->lesson_type,
                        ];
                    })->values()->all(),
                ];
            }),
        ];
    }
}
