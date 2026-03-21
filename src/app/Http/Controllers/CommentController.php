<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * コメントを保存する
     */
    public function store(Request $request)
    {
        // バリデーション（120文字以内のチェックなど）
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|max:120',
        ]);

        // データベースに保存
        $comment = Comment::create([
            'user_id' => $request->user_id,
            'post_id' => $request->post_id,
            'content' => $request->content,
        ]);

        // 保存したコメントに投稿者の情報（名前など）をくっつけて返す
        $comment->load('user');

        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => $comment
        ], 201);
    }

    /**
     * コメントを削除する
     */
    public function destroy(Comment $comment)
    {
        // コメントをデータベースから削除
        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully'
        ], 200);
    }
}
