<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
    ];

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // いいね機能用のリレーション
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // コメント機能用のリレーション
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
