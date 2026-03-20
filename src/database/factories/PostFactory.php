<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            // すでに存在するユーザーの中からランダムにIDを取得
            'user_id' => User::inRandomOrder()->first()->id,
            // 20〜50文字程度のランダムな日本語テキスト
            'content' => $this->faker->realText(rand(20, 50)),
        ];
    }
}
