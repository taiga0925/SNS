<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * 特定のユーザー情報と、そのユーザーの投稿一覧を取得する
     */
    public function show($id)
    {
        // ユーザー情報を取得する際に、関連する「投稿（posts）」も一緒に引っ張ってきます。
        // さらに、投稿ごとの「いいね（likes）」情報も同時に取得し、新しい順に並べ替えます。
        $user = User::with(['posts' => function ($query) {
            $query->orderBy('created_at', 'desc')->with('likes');
        }])->findOrFail($id);

        return response()->json([
            'data' => $user
        ], 200);
    }
}
