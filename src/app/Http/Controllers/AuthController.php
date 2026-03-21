<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * 新規ユーザー登録API
     * フロントエンドから受け取ったユーザー情報をバリデーションし、パスワードをハッシュ化して保存
     * * @param  \Illuminate\Http\Request  $request リクエストデータ（name, email, password）
     * @return \Illuminate\Http\JsonResponse JSONレスポンス（成功メッセージと作成されたユーザー情報）
     */
    public function register(Request $request)
    {
        // リクエストデータのバリデーション
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

        // 登録成功のレスポンス（ステータスコード201: Created）
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * ユーザーログインAPI
     * メールアドレスとパスワードで認証を行い、成功した場合はユーザー情報を返す
     * * @param  \Illuminate\Http\Request  $request リクエストデータ（email, password）
     * @return \Illuminate\Http\JsonResponse JSONレスポンス（成功時はユーザー情報、失敗時はエラーメッセージ）
     */
    public function login(Request $request)
    {
        // リクエストデータのバリデーション
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // DB内のハッシュ化されたパスワードと照合（認証）
        if (auth()->attempt($validated)) {
            // 認証に成功した場合、ログインしたユーザー情報を取得
            $user = auth()->user();

            // ログイン成功のレスポンス（ステータスコード200: OK）
            return response()->json([
                'message' => 'Login success',
                'user' => $user
            ], 200);
        }

        // 認証に失敗した場合のレスポンス（ステータスコード401: Unauthorized）
        return response()->json(['message' => 'Login failed'], 401);
    }
}
