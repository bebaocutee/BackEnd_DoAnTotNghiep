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
        //Lấy dữ liệu trong bảng lesson
        $lesson = Lesson::find($id)->load('chapter.course');

        // Tạo mới nếu chưa có dữ liệu của user lesson
        if (UserLesson::where(['user_id' => auth()->id(), 'lesson_id' => $id])->count() == 0) {
            UserLesson::create([
                'user_id' => auth()->id(),
                'lesson_id' => $id,
            ]);
        }

        // Lấy dữ liệu học sinh đã làm trong bảng user lesson
        $userLesson = UserLesson::where('user_id', auth()->id())->where('lesson_id', $id)->with(['results.answer'])->first();

        // Lấy câu hỏi theo id nếu có
        if ($request->has('question_id')) {
            $question = $lesson->questions()->where('questions.id', $request->question_id)->with('answers')->first();
        } else { // Lấy câu hỏi theo id nếu không có
            // Tìm câu hỏi chưa làm. Điều kiện của câu hỏi chưa làm là: Không nằm trong bảng result (Chính là where not in)
            $question = $lesson->questions()->whereNotIn('questions.id', $userLesson->results->pluck('question_id')->toArray())->with('answers')->first();
        }

        if ($lesson->questions()->count() == 0) {
            // Nếu bài tập không có câu hỏi thì trả về lỗi
            return response()->json([
                'message' => 'Không có câu hỏi nào!',
            ], 422);
        }

        // Kiểm tra xem đã hết câu hỏi chưa làm chưa
        $finishLesson = false;
        if (!$question) { // Nếu không tìm được câu hỏi thì là làm xong rồi
            // Sẽ lấy câu hỏi đầu tiên khi làm xong bài tập
            $question = $lesson->questions()->with('answers')->latest()->first();

            // Đánh dấu đã làm xong
            $finishLesson = true;
        }

        // Trả về dữ liệu
        $question->answers->map(function ($answer) {
            $answer->is_correct = $answer->is_correct ?? 0;
            $answer->image = $answer->image ? env('APP_URL') . Storage::url($answer->image) : null;
            return $answer;
        });
        $result = $userLesson->results->map(function ($result) {
            return [
                'is_correct' => $result->answer->is_correct ?? 0,
                'question_id' => $result->question_id,
                'answer_id' => $result->answer_id,
            ];
        });
        $score = 0;
        if ($finishLesson) {
            $score = $result->where('is_correct', 1)->count() > 0 ? round($result->where('is_correct', 1)->count() / $lesson->questions->count() * 10, 1) : 0;
        }
        $question->image = $question->image ? env('APP_URL') . Storage::url($question->image) : null;
        return response()->json([
            'results' => $result,
            'question' => $question,
            'course_id' => $lesson->chapter->course->id ?? null,
            'course_name' => $lesson->chapter->course->course_name ?? null,
            'chapter_name' => $lesson->chapter->chapter_name ?? null,
            'lesson_name' => $lesson->lesson_name ?? null,
            'finish_lesson' => $finishLesson,
            'score' => $score
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

        $correctCount = 0;
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
        $score = round($correctCount / $test->questions->count() * 10, 1);

        return response()->json(['message' => 'Nộp bài thành công!', 'score' => $score, 'course_id' => $test->chapter->course->id ?? null]);
    }

    public function history($id)
    {
        $user = User::find($id)->load(['lessonUsers.lesson', 'lessonUsers.results.answer']);
        if ($user && $user->lessonUsers) {
            $user->lessonUsers->map(function ($lessonUser) {
                $totalQuestion = $lessonUser->results->count() ?? 0;
                if ($totalQuestion == 0) {
                    return $lessonUser;
                }
                $totalCorrect = $lessonUser->results->filter(function ($result)  {
                    return $result->answer->is_correct == 1;
                })->count();
                $totalWrong = $totalQuestion - $totalCorrect;
                $lessonUser->total_question = $totalQuestion;
                $lessonUser->total_correct = $totalCorrect;
                $lessonUser->total_wrong = $totalWrong;
                $lessonUser->score = $totalCorrect == 0 ? 0 : round($totalCorrect / $totalQuestion * 10, 1) ?? 0;
                return $lessonUser;
            });
        }

        return response()->json($user->lessonUsers);
    }
}
