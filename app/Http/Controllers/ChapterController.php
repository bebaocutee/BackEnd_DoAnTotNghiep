<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChapterResource;
use App\Models\Chapter;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->course_id) {
            $chapters = Chapter::where('course_id', $request->course_id)->with(['course', 'lessons'])->get();
        } else {
            $chapters = Chapter::with(['course', 'lessons'])->get();
        }
        return response()->json(ChapterResource::collection($chapters));
    }

    public function create(Request $request)
    {
        Chapter::create(array_merge(
            $request->only(['chapter_name', 'description']),
            ['course_id' => $request->course_id, 'teacher_id' => auth()->id()]
        ));
        return response()->json(['message' => 'Tạo chương thành công']);
    }

    public function show($id)
    {
        $chapter = Chapter::find($id);
        return response()->json(new ChapterResource($chapter));
    }

    public function update(Request $request, $id)
    {
        $chapter = Chapter::find($id);
        $chapter->update($request->only(['chapter_name', 'description', 'course_id']));
        return response()->json(['message' => 'Cập nhật chương thành công']);
    }

    public function delete($id)
    {
        $chapter = Chapter::find($id);
        if ($chapter) {
            $chapter->delete();
        }
        return response()->json(['message' => 'Xóa chương thành công']);
    }
}
