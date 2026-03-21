<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * ユーザー詳細および関連投稿取得API
     * 指定されたIDのユーザー情報と、そのユーザーが作成した投稿一覧（いいね情報含む）を作成日時の降順で取得
     * * @param  int  $id 取得対象のユーザーID
     * @return \Illuminate\Http\JsonResponse JSONレスポンス（指定されたユーザー情報および関連する投稿データ）
     */
    public function show($id)
    {
        // ユーザー情報を取得する際に、関連する投稿postsを取得
        // クロージャを使用して「投稿を作成日時の降順（新しい順）に並べ替え」、
        // 各投稿ごとの「いいね（likes）」情報も同時に取得
        $user = User::with(['posts' => function ($query) {
            $query->orderBy('created_at', 'desc')->with('likes');
        }])->findOrFail($id);

        // 取得成功のレスポンス（ステータスコード200: OK）
        return response()->json([
            'data' => $user
        ], 200);
    }
}
