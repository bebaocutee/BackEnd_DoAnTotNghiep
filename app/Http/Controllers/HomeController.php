<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Http\Resources\ListLessonResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\UserLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function topCourses()
    {
        $courses = Course::latest()->limit(8)->get();
        return response()->json(CourseResource::collection($courses));
    }

    public function courses()
    {
        $courses = Course::latest()->get();
        return response()->json(CourseResource::collection($courses));
    }

    public function listLesson($id)
    {
        $course = Course::find($id)->load('chapters.lessons');
        return response()->json(new ListLessonResource($course));
    }

    public function getQuestion($id)
    {
        $lesson = Lesson::find($id)->load('chapter.course');
        UserLesson::firstOrCreate([
            'user_id' => auth()->id(),
            'lesson_id' => $id,
        ]);
        $userLesson = UserLesson::where('user_id', auth()->id())->where('lesson_id', $id)->with(['results.answer'])->first();
        $question = $lesson->questions()->whereNotIn('questions.id', $userLesson->results->pluck('question_id')->toArray())->with('answers')->first();
        if (!$question) {
            $question = $lesson->questions()->with('answers')->latest()->first();
        }
        $question->answers->map(function ($answer) {
            $answer->is_correct = $answer->is_correct ?? 0;
            $answer->image = $answer->image ? env('APP_URL') . Storage::url($answer->image) : null;
            return $answer;
        });
        return response()->json([
            'results' => $userLesson->results->map(function ($result) {
                return [
                    'is_correct' => $result->answer->is_correct ?? 0,
                ];
            }),
            'question' => $question,
            'course_id' => $lesson->chapter->course->id ?? null,
            'course_name' => $lesson->chapter->course->course_name ?? null,
            'chapter_name' => $lesson->chapter->chapter_name ?? null,
            'lesson_name' => $lesson->lesson_name ?? null,
        ]);
    }

    public function submitLesson(Request $request, $id)
    {
        $userLesson = UserLesson::firstOrCreate([
            'user_id' => auth()->id(),
            'lesson_id' => $id,
        ]);
        $userLesson->results()->create([
            'question_id' => $request->question_id,
            'answer_id' => $request->answer_id,
        ]);
        return response()->json([
            'message' => 'Nộp bài thành công!',
        ]);
    }
}
