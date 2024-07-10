<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where(['role' => User::ROLE_STUDENT])->with(['lessons.chapter.course'])->get();
        $users = $users->map(function ($user) {
            $user->courses = $user->lessons->map(function ($lesson) {
                return $lesson->chapter->course;
            })->unique()->values();
            return $user;
        });
        return response()->json(UserResource::collection($users));
    }

    public function create(Request $request)
    {
        User::create($request->only(['full_name', 'email', 'password', 'phone_number']), ['role' => User::ROLE_STUDENT]);
        return response()->json(['message' => 'Tạo học sinh thành công']);
    }

    public function show($id)
    {
        $user = User::find($id);
        return response()->json(new UserResource($user));
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if ($request->password) {
            $data = $request->only(['full_name', 'email', 'password', 'phone_number']);
        } else {
            $data = $request->only(['full_name', 'email', 'phone_number']);
        }
        $user->update($data);
        return response()->json(['message' => 'Cập nhật học sinh thành công']);
    }

    public function delete($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
        }
        return response()->json(['message' => 'Xóa học sinh thành công']);
    }
}
