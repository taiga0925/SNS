<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Post;

class LikeController extends Controller
{
    /**
     * いいね追加
     * POST /api/likes
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id',
        ]);

        // 既にいいねしていないか確認（二重いいね防止）
        $existingLike = Like::where('post_id', $validated['post_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existingLike) {
            return response()->json(['message' => 'Already liked'], 409); // 409 Conflict
        }

        $like = Like::create($validated);

        return response()->json(['message' => 'Liked successfully', 'data' => $like], 201);
    }

    /**
     * いいね削除（解除）
     * DELETE /api/likes
     * ※今回はID指定ではなく、user_idとpost_idの組み合わせで削除する方式を採ります
     */
    public function destroy(Request $request)
    {
        // 削除対象を特定するための情報をバリデーション
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $like = Like::where('post_id', $validated['post_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($like) {
            $like->delete();
            return response()->json(['message' => 'Like removed'], 200);
        } else {
            return response()->json(['message' => 'Like not found'], 404);
        }
    }
}
