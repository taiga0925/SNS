<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * 新規登録
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', // 重複チェック
            'password' => 'required|string|min:6',
        ]);

        // パスワードをハッシュ化（暗号化）して保存
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * ログイン
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // DB内のハッシュ化されたパスワードと照合
        if (auth()->attempt($validated)) {
            $user = auth()->user();
            return response()->json([
                'message' => 'Login success',
                'user' => $user
            ], 200);
        }

        return response()->json(['message' => 'Login failed'], 401);
    }
}
