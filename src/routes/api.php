<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ユーザー登録
Route::post('/register', [AuthController::class, 'register']);
// ログイン（Laravel側で認証する場合）
Route::post('/login', [AuthController::class, 'login']);

// ユーザー関連
Route::get('/users/{id}', [UserController::class, 'show']);


// 投稿関連
Route::get('/posts', [PostController::class, 'index']);      // 一覧
Route::post('/posts', [PostController::class, 'store']);     // 作成
Route::get('/posts/{id}', [PostController::class, 'show']); // 詳細
Route::delete('/posts/{id}', [PostController::class, 'destroy']); // 削除

// いいね関連
Route::post('/likes', [LikeController::class, 'store']);
Route::delete('/likes', [LikeController::class, 'destroy']); // ID指定なしで削除する場合の工夫が必要ですが一旦定義

// コメント関連
Route::post('/comments', [CommentController::class, 'store']);
Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
