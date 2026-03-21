<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * コメント（Comment）モデル
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // コメントしたユーザーのID
        'post_id', // コメントされた投稿のID
        'content', // コメントの本文
    ];

    /**
     * ユーザーとのリレーション（多対1）
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 投稿とのリレーション（多対1）
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
