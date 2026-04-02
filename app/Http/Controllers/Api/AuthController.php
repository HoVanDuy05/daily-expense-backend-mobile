<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới - CHUẨN RESTFUL API
     */
    public function register(Request $request)
    {
        // Laravel tự động ném ra ValidationException nếu lỗi, trả về 422 JSON
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ], [
            'email.unique' => 'Email này đã được sử dụng rồi bạn ơi!',
            'email.required' => 'Đừng quên nhập email nhé.',
            'password.min' => 'Mật khẩu phải từ 6 ký tự trở lên cho bảo mật.',
            'name.required' => 'Vui lòng điền tên của bạn.'
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Tạo settings mặc định
        $user->settings()->create([
            'currency'    => 'VND',
            'notify_push' => true,
            'theme'       => 'light',
            'language'    => 'vi',
            'avatar'      => "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&background=7C3AED&color=fff"
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'user'  => $user->load('settings'),
            'token' => $token,
        ], 201);
    }

    /**
     * Đăng nhập - CHUẨN RESTFUL API
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Email hoặc mật khẩu không chính xác.'
                ], 401);
            }

            $user = Auth::user();

            // SỬA LỖI 500: Tự động tạo settings nếu chẳng may bị thiếu
            if (!$user->settings) {
                $user->settings()->create([
                    'currency'    => 'VND',
                    'notify_push' => true,
                    'theme'       => 'light',
                    'language'    => 'vi',
                    'avatar'      => "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&background=7C3AED&color=fff"
                ]);
            }

            return response()->json([
                'user'  => $user->load('settings'),
                'token' => $user->createToken('mobile-app')->plainTextToken,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Lỗi máy chủ: ' . $e->getMessage()
            ], 500);
        }
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('settings'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đăng xuất thành công.']);
    }
}
