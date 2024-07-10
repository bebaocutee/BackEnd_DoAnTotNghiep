<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\OtpMail;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if (User::where('email', $request->email)->count() > 0) {
            return response()->json(['message' => 'Email đã tồn tại'], 422);
        }

        User::create($request->only(['full_name', 'email', 'password']));
        return response()->json(['message' => 'Đăng ký thành công'], 200);
    }

    public function login(Request $request)
    {
        if (User::where('email', $request->email)->count() == 0) {
            return response()->json(['message' => 'Email không đúng!'], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (auth()->attempt($request->only(['email', 'password'])) == false) {
            return response()->json(['message' => 'Mật khẩu không đúng!'], 422);
        }

        $token = auth()->login($user);
        return response()->json([
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => [
                'email' => $user->email,
                'full_name' => $user->full_name,
                'short_name' => $this->getShortName($user->full_name),
                'role' => $user->role,
            ]
        ], 200);
    }

    private function getShortName($full_name)
    {
        $name = explode(' ', $full_name);
        $short_name = '';
        foreach ($name as $n) {
            $short_name .= strtoupper($n[0]);
        }
        return $short_name[0] . $short_name[strlen($short_name) - 1];
    }

    public function sendOtp(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null) {
            return response()->json(['message' => 'Email không tồn tại!'], 422);
        }

        $otp = rand(100000, 999999);
        // Gửi OTP qua email
        $user->notify(new OtpMail($otp, $user));
        Otp::create([
            'email' => $user->email,
            'otp' => $otp
        ]);
        return response()->json(['message' => 'Mã OTP đã được gửi qua email!'], 200);
    }

    public function forgetPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null) {
            return response()->json(['message' => 'Email không tồn tại!'], 422);
        }

        if (Otp::where('email', $request->email)->where('otp', $request->otp)->count() == 0) {
            return response()->json(['message' => 'Nhập sai mã OTP!'], 422);
        }

        User::where('email', $request->email)->update(['password' => $request->new_password]);
        return response()->json(['message' => 'Đổi mật khẩu thành công!'], 200);
    }

    public function changePassword(Request $request)
    {
        auth()->user()->update(['password' => $request->new_password]);
        return response()->json(['message' => 'Đổi mật khẩu thành công'], 200);
    }

    public function checkAuth()
    {
        if (auth()->check()) {
            return response()->json(['message' => 'Đã đăng nhập'], 200);
        }
        return response()->json(['message' => 'Chưa đăng nhập'], 401);
    }
}
