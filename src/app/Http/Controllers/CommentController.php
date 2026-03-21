<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * コメント作成API
     * 投稿に対するコメントをデータベースに保存し、投稿者(ユーザー)情報を付与
     * * @param  \Illuminate\Http\Request  $request リクエストデータ（user_id, post_id, content）
     * @return \Illuminate\Http\JsonResponse JSONレスポンス（成功メッセージと作成されたコメントデータ）
     */
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|max:120',
        ]);

        // DBに新しいコメントを保存
        $comment = Comment::create([
            'user_id' => $request->user_id,
            'post_id' => $request->post_id,
            'content' => $request->content,
        ]);

        // 保存したコメントに投稿者の情報（名前など）をリレーションで結合して取得
        $comment->load('user');

        // コメント作成成功のレスポンス（ステータスコード201: Created）
        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => $comment
        ], 201);
    }

    /**
     * コメント削除API
     * 指定されたコメントをデータベースから削除する
     * * @param  \App\Models\Comment  $comment 削除対象のコメントモデル
     * @return \Illuminate\Http\JsonResponse JSONレスポンス（削除完了メッセージ）
     */
    public function destroy(Comment $comment)
    {
        // コメントをデータベースから削除
        $comment->delete();

        // コメント削除成功のレスポンス（ステータスコード200: OK）
        return response()->json([
            'message' => 'Comment deleted successfully'
        ], 200);
    }
}
