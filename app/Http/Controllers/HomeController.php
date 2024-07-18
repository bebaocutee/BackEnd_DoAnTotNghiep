<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Http\Resources\ListLessonResource;
use App\Models\Answer;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Result;
use App\Models\User;
use App\Models\UserLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function getQuestion(Request$request, $id)
    {
        $lesson = Lesson::find($id)->load('chapter.course');
        UserLesson::firstOrCreate([
            'user_id' => auth()->id(),
            'lesson_id' => $id,
        ]);
        $userLesson = UserLesson::where('user_id', auth()->id())->where('lesson_id', $id)->with(['results.answer'])->first();
        if ($request->has('question_id')) {
            $question = $lesson->questions()->where('questions.id', $request->question_id)->with('answers')->first();
        } else {
            $question = $lesson->questions()->whereNotIn('questions.id', $userLesson->results->pluck('question_id')->toArray())->with('answers')->first();
        }
        if ($lesson->questions()->count() == 0) {
            return response()->json([
                'message' => 'Không có câu hỏi nào!',
            ], 422);
        }
        if (!$question) {
            $question = $lesson->questions()->with('answers')->latest()->first();
        }
        $question->answers->map(function ($answer) {
            $answer->is_correct = $answer->is_correct ?? 0;
            $answer->image = $answer->image ? env('APP_URL') . Storage::url($answer->image) : null;
            return $answer;
        });
        $question->image = $question->image ? env('APP_URL') . Storage::url($question->image) : null;
        return response()->json([
            'results' => $userLesson->results->map(function ($result) {
                return [
                    'is_correct' => $result->answer->is_correct ?? 0,
                    'question_id' => $result->question_id,
                    'answer_id' => $result->answer_id,
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

    public function test(Request $request)
    {
        $courses = Course::latest()->limit(8)->get();
        if ($request->has('course_id')) {
            $course = Course::find($request->course_id)->load('chapters.lessons');
        } else {
            $course = Course::latest()->first()->load('chapters.lessons');
        }
        if (!$course) {
            return response()->json([
                'message' => 'Không tìm thấy khóa học!',
            ], 422);
        }
        $course->chapters->map(function ($chapter) {
            $chapter->lessons = $chapter->lessons->filter(function ($lesson) {
                return $lesson->lesson_type == Lesson::TYPE_EXERCISE;
            });
            return $chapter;
        });

        return response()->json([
            'courses' => CourseResource::collection($courses),
            'course' => new ListLessonResource($course),
        ]);
    }

    public function getTest(Request $request, $id)
    {
        $test = Lesson::find($id)->load(['questions.answers', 'chapter.course']);
        if ($test->questions) {
            $test->questions->map(function ($question) {
                $question->image = $question->image ? env('APP_URL') . Storage::url($question->image) : null;
                $question->answers->map(function ($answer) {
                    $answer->is_correct = $answer->is_correct ?? 0;
                    $answer->image = $answer->image ? env('APP_URL') . Storage::url($answer->image) : null;
                    return $answer;
                });
                return $question;
            });
        }
        return response()->json($test);
    }

    public function submitTest(Request $request)
    {
        $test = Lesson::find($request->test_id);

        if (!$test) {
            return response()->json(['message' => 'Không tìm thấy bài thi!'], 422);
        }

        if (UserLesson::where('user_id', auth()->id())->where('lesson_id', $request->test_id)->exists()) {
            return response()->json(['message' => 'Bạn đã làm bài kiểm tra này rồi!'], 422);
        }

        $correctCount = 0;
        DB::transaction(function () use ($request, $test, &$correctCount) {
            $userLesson = UserLesson::firstOrCreate([
                'user_id' => auth()->id(),
                'lesson_id' => $request->test_id,
            ]);
            foreach ($request->answers as $answerId) {
                $answer = Answer::find($answerId)->load('question.correctAnswer');
                if (!$answer) {
                    continue;
                }
                if ($answer->question->correctAnswer->id == $answerId) {
                    $correctCount++;
                }
                Result::create([
                    'user_lesson_id' => $userLesson->id,
                    'question_id' => $answer->question_id,
                    'answer_id' => $answer->id,
                ]);
            }
        });
        $score = round($correctCount / $test->questions->count() * 10, 1);

        return response()->json(['message' => 'Nộp bài thành công!', 'score' => $score, 'course_id' => $test->chapter->course->id ?? null]);
    }

    public function history($id)
    {
        $user = User::find($id)->load(['lessonUsers.lesson.questions.correctAnswer', 'lessonUsers.results']);
        if ($user && $user->lessonUsers) {
            $user->lessonUsers->map(function ($lessonUser) {
                $totalQuestion = $lessonUser->lesson->questions->count() ?? 0;
                if ($totalQuestion == 0) {

                }
                $totalCorrect = $lessonUser->lesson->questions->filter(function ($question) use ($lessonUser) {
                    return $question->correctAnswer->answer_id ?? null == $lessonUser->results->where('question_id', $question->id)->first()->answer_id;
                })->count();
                $totalWrong = $totalQuestion - $totalCorrect;
                $lessonUser->total_question = $totalQuestion;
                $lessonUser->total_correct = $totalCorrect;
                $lessonUser->total_wrong = $totalWrong;
                $lessonUser->score = round($totalCorrect / $totalQuestion * 10, 1) ?? 0;
                return $lessonUser;
            });
        }

        return response()->json($user->lessonUsers);
    }
}
