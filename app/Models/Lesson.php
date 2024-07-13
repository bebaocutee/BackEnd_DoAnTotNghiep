<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $guarded = [];

    const TYPE_LESSON = 1;
    const TYPE_EXERCISE = 2;

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function questions()
    {
        return $this->hasManyThrough(Question::class, LessonQuestion::class, 'lesson_id', 'id', 'id', 'question_id');
    }
}
