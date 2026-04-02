<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Tạo settings mặc định cho user mới
            $user->settings()->create([
                'currency'    => 'VND',
                'notify_push' => true,
                'theme'       => 'light',
                'language'    => 'vi',
                'avatar'      => "https://ui-avatars.com/api/?name=" . urlencode($request->name) . "&background=7C3AED&color=fff"
            ]);

            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'user'  => $user->load('settings'),
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi máy chủ: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Đăng nhập
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng.'],
            ]);
        }

        $user  = Auth::user()->load('settings');
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function me(Request $request)
    {
        return response()->json($request->user()->load('settings'));
    }

    /**
     * Đăng xuất (Thu hồi token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đăng xuất thành công.']);
    }
}
