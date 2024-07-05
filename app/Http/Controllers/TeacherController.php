<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\TeacherInfo;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = User::where(['role' => User::ROLE_TEACHER])->get();
        return response()->json(UserResource::collection($teachers));
    }

    public function create(Request $request)
    {
        User::create($request->only(['full_name', 'email', 'password']), ['role' => User::ROLE_TEACHER]);
        return response()->json(['message' => 'Tạo giáo viên thành công']);
    }

    public function show($id)
    {
        $teacher = User::find($id);
        return response()->json(new UserResource($teacher));
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
