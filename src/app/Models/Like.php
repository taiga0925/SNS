<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * いいねモデル
 */
class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id'
    ];

    /**
     * ユーザーとのリレーション（多対1）
     * このいいねは、どのユーザー（User）によってされたものかを取得するため
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 投稿とのリレーション（多対1）
     * このいいねは、どの投稿（Post）に対してされたものかを取得するため
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
