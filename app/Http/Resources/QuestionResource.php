<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'teacher' => $this->teacher->full_name ?? null,
            'image' => $this->image ? env('APP_URL') . Storage::url($this->image) : null,
            'answers' => $this->answers->map(function ($answer) {
                return [
                    'id' => $answer->id,
                    'answer_content' => $answer->answer_content,
                    'image' => $answer->image ? env('APP_URL') . Storage::url($answer->image) : null,
                    'is_correct' => $answer->is_correct
                ];
            })
        ]);
    }
}
