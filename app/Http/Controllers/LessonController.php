<?php

namespace App\Http\Controllers;

use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $lesson = Lesson::all();
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
        Lesson::delete($id);
        return response()->json(['message' => 'Xóa bài học thành công']);
    }
}
