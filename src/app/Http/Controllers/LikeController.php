<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Post;

class LikeController extends Controller
{
    /**
     * いいね追加API
     * 指定された投稿に対して、ユーザーからのいいねをデータベースに保存
     * * @param  \Illuminate\Http\Request  $request リクエストデータ（post_id, user_id）
     * @return \Illuminate\Http\JsonResponse JSONレスポンス（成功メッセージと作成されたいいねデータ、または重複エラー）
     */
    public function store(Request $request)
    {
        // リクエストデータのバリデーション（対象の投稿とユーザーが存在するか確認）
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id',
        ]);

        // 既に同じユーザーが同じ投稿にいいねしていないか確認（二重いいね防止）
        $existingLike = Like::where('post_id', $validated['post_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        // 既にいいねが存在する場合は、重複エラー（ステータスコード409: Conflict）
        if ($existingLike) {
            return response()->json(['message' => 'Already liked'], 409);
        }

        // データベースに新しいいいねを保存
        $like = Like::create($validated);

        // いいね作成成功のレスポンス（ステータスコード201: Created）
        return response()->json([
            'message' => 'Liked successfully',
            'data' => $like
        ], 201);
    }

    /**
     * いいね削除（解除）API
     * 投稿IDとユーザーIDの組み合わせから該当するいいねレコードを特定し、削除する
     * * @param  \Illuminate\Http\Request  $request リクエストデータ（post_id, user_id）
     * @return \Illuminate\Http\JsonResponse JSONレスポンス（削除完了メッセージ、または対象が見つからない場合のエラー）
     */
    public function destroy(Request $request)
    {
        // 削除対象を特定するための情報をバリデーション
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id',
        ]);

        // 対象の投稿IDとユーザーIDに一致するいいねレコードをデータベースから検索
        $like = Like::where('post_id', $validated['post_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($like) {
            // 対象が見つかった場合はデータベースから削除し、成功レスポンス（ステータスコード200: OK）を返す
            $like->delete();
            return response()->json(['message' => 'Like removed'], 200);
        } else {
            // 対象が見つからなかった場合は、エラーレスポンス（ステータスコード404: Not Found）を返す
            return response()->json(['message' => 'Like not found'], 404);
        }
    }
}
