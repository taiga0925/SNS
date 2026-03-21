<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * 投稿一覧取得API
     * 投稿を作成日時が新しい順に取得し、ユーザー情報・いいね・コメントも得
     * * @return \Illuminate\Http\JsonResponse JSONレスポンス（投稿データ一覧）
     */
    public function index()
    {
        // 投稿を作成日時順に取得。ユーザー情報、いいね、コメントも一緒に取得
        $posts = Post::with(['user', 'likes', 'comments'])->latest()->get();
        return response()->json(['data' => $posts], 200);
    }

    /**
     * 新規投稿作成API
     * リクエストデータをバリデーションし、新しい投稿をデータベースに保存する
     * * @param  \Illuminate\Http\Request  $request リクエストデータ（user_id, content）
     * @return \Illuminate\Http\JsonResponse JSONレスポンス（作成完了メッセージと作成された投稿データ）
     */
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
     * 投稿詳細取得API
     * 指定されたIDの投稿詳細と、それに紐づくユーザー、コメント、コメントの投稿者、いいねを取得
     * * @param  int  $id 取得対象の投稿ID
     * @return \Illuminate\Http\JsonResponse JSONレスポンス（指定された投稿の詳細データ）
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
     * 投稿削除API
     * 指定されたIDの投稿をデータベースから削除
     * * @param  int  $id 削除対象の投稿ID
     * @return \Illuminate\Http\JsonResponse JSONレスポンス（削除完了メッセージ）
     */
    public function destroy($id)
    {
        // 受け取ったIDから投稿を探し出す
        $post = Post::findOrFail($id);

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ], 200);
    }
}
