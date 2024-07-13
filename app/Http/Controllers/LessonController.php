<?php

namespace App\Http\Controllers;

use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $query = Lesson::query();
        if ($request->chapter_id) {
            $query->where('chapter_id', $request->chapter_id);
        }
        if ($request->course_id) {
            $query->whereHas('chapter', function ($query) use ($request) {
                $query->where('course_id', $request->course_id);
            });
        }
        $lesson = $query->with(['chapter.course', 'questions'])->get();
        return response()->json(LessonResource::collection($lesson));
    }

    public function create(Request $request)
    {
        Lesson::create(array_merge(
            $request->only(['lesson_name', 'lesson_type', 'time_limit', 'description']),
            ['chapter_id' => $request->chapter_id, 'teacher_id' => auth()->id()]
        ));
        if ($request->lesson_type == Lesson::TYPE_EXERCISE) {
            return response()->json(['message' => 'Tạo bài kiểm tra thành công']);
        }
        return response()->json(['message' => 'Tạo bài học thành công']);
    }

    public function show($id)
    {
        $lesson = Lesson::find($id);
        return response()->json(new LessonResource($lesson));
    }

    public function update(Request $request, $id)
    {
        $lesson = Lesson::find($id);
        $lesson->update($request->only(['lesson_name', 'lesson_type', 'time_limit', 'description']));
        return response()->json(['message' => 'Cập nhật bài học thành công']);
    }

    public function delete($id)
    {
        Lesson::destroy($id);
        return response()->json(['message' => 'Xóa bài học thành công']);
    }

    public function getSelected($id)
    {
        $selected = LessonQuestion::where('lesson_id', $id)->pluck('question_id')->toArray();
        return response()->json($selected);
    }

    public function saveSelected(Request $request, $id)
    {
        LessonQuestion::where('lesson_id', $id)->delete();
        foreach ($request->selected as $questionId) {
            LessonQuestion::create(['lesson_id' => $id, 'question_id' => $questionId]);
        }
        return response()->json(['message' => 'Lưu câu hỏi thành công']);
    }
}
