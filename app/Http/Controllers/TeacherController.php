<?php

namespace App\Http\Controllers;

use App\Http\Resources\TeacherResource;
use App\Models\TeacherInfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = User::where(['role' => User::ROLE_TEACHER])->with(['teacherInfo'])->get();
        return response()->json(TeacherResource::collection($teachers));
    }

    public function create(Request $request)
    {
        DB::transaction(function () use ($request) {
            $teacher = User::create(array_merge($request->only(['full_name', 'email', 'password', 'phone_number']), ['role' => User::ROLE_TEACHER]));
            TeacherInfo::create([
                'teacher_id' => $teacher->id, 
                'date_of_birth' => $request->date_of_birth, 
                'experience' => $request->experience, 
                'work_unit' => $request->work_unit, 
                'introduction' => $request->introduction
            ]);
        });
        return response()->json(['message' => 'Tạo giáo viên thành công']);
    }

    public function show($id)
    {
        $teacher = User::find($id);
        return response()->json(new TeacherResource($teacher));
    }

    public function update(Request $request, $id)
    {
        $teacher = User::find($id);
        $teacher->update($request->only(['full_name', 'email', 'password']));
        return response()->json(['message' => 'Cập nhật giáo viên thành công']);
    }

    public function delete($id)
    {
        User::delete($id);
        return response()->json(['message' => 'Xóa giáo viên thành công']);
    }

    public function updateInfo(Request $request)
    {
        TeacherInfo::updateOrCreate(
            ['teacher_id' => auth()->id()],
            $request->only(['date_of_birth', 'experience', 'work_unit', 'introduction'])
        );
        return response()->json(['message' => 'Cập nhật thông tin giáo viên thành công']);
    }
}
