<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    // 一覧表示
    public function index()
    {
        // 投稿を作成日時順に取得。ユーザー情報、いいね、コメントも一緒に取得
        $posts = Post::with(['user', 'likes', 'comments'])->latest()->get();
        return response()->json(['data' => $posts], 200);
    }

    // 投稿作成
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // フロントからuser_idを送ってもらう想定
            'content' => 'required|string|max:120',
        ]);

        $post = Post::create($validated);

        return response()->json(['message' => 'Post created', 'data' => $post], 201);
    }

    /**
     * 指定した投稿の詳細とコメントを取得する
     */
    public function show($id)
    {
        // 投稿情報と一緒に、投稿者(user)と、コメント(comments)と、コメントの投稿者(comments.user)を全てまとめて取得
        $post = Post::with(['user', 'comments.user', 'likes'])->findOrFail($id);

        return response()->json([
            'post' => $post
        ], 200);
    }

    /**
     * 投稿を削除する
     */
    public function destroy($id)
    {
        // 受け取ったIDから確実に投稿を探し出す
        $post = Post::findOrFail($id);

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ], 200);
    }
}
