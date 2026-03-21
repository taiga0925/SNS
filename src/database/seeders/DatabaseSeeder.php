<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;

class DatabaseSeeder extends Seeder
{
    public function run()
    {

        // ダミーユーザーを10人作成
        User::factory(10)->create();

        // そのユーザーたちの投稿を20件作成
        Post::factory(20)->create();

        // 投稿に対するコメントを30件作成
        Comment::factory(30)->create();

        // いいねをランダムに40件作成
        // （※すでにいいね済みの組み合わせでエラーが出るのを防ぐため try-catch を使用）
        for ($i = 0; $i < 40; $i++) {
            try {
                Like::factory()->create();
            } catch (\Exception $e) {
                // 重複エラーの場合はスキップ
                continue;
            }
        }
    }
}
