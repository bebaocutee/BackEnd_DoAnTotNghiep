<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = User::where(['role' => User::ROLE_STUDENT])->get();
        return response()->json(UserResource::collection($user));
    }

    public function create(Request $request)
    {
        User::create($request->only(['full_name', 'email', 'password']), ['role' => User::ROLE_STUDENT]);
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
        $user->update($request->only(['full_name', 'email', 'password']));
        return response()->json(['message' => 'Cập nhật học sinh thành công']);
    }

    public function delete($id)
    {
        User::delete($id);
        return response()->json(['message' => 'Xóa học sinh thành công']);
    }
}
