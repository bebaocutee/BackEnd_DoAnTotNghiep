<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\TeacherCourse;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role == User::ROLE_ADMIN) {
            $courses = Course::with(['teachers'])->get();
        } else {
            $courses = auth()->user()->courses()->with(['teachers'])->get();
        }
        return response()->json(CourseResource::collection($courses));
    }

    public function create(Request $request)
    {
        $course = Course::create(array_merge($request->only(['course_name', 'description']), ['admin_id' => auth()->id()]));
        if ($request->teachers) {
            foreach ($request->teachers as $teacherId) {
                TeacherCourse::create([
                    'course_id' => $course->id,
                    'teacher_id' => $teacherId
                ]);
            }
        }
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
        $course->update($request->only(['course_name', 'description']));
        TeacherCourse::where('course_id', $course->id)->delete();
        if ($request->teachers) {
            foreach ($request->teachers as $teacherId) {
                TeacherCourse::create([
                    'course_id' => $course->id,
                    'teacher_id' => $teacherId
                ]);
            }
        }
        return response()->json(['message' => 'Cập nhật khóa học thành công']);
    }

    public function delete($id)
    {
        $course = Course::find($id);
        if ($course) {
            $course->delete();
        }
        return response()->json(['message' => 'Xóa khóa học thành công']);
    }
}
