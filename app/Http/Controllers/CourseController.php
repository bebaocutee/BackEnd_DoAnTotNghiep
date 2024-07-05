<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::all();
        return response()->json(CourseResource::collection($courses));
    }

    public function create(Request $request)
    {
        Course::create(array_merge($request->only(['name', 'description']), ['admin_id' => auth()->id()]));
        return response()->json(['message' => 'Tạo khóa học thành công']);
    }

    public function show($id)
    {
        $course = Course::find($id);
        return response()->json(new CourseResource($course));
    }

    public function update(Request $request, $id)
    {
        $course = Course::find($id);
        $course->update($request->only(['name', 'description']));
        return response()->json(['message' => 'Cập nhật khóa học thành công']);
    }

    public function delete($id)
    {
        Course::delete($id);
        return response()->json(['message' => 'Xóa khóa học thành công']);
    }
}
