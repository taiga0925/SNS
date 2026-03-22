SNSアプリ「Share」
Firebase認証、Laravel（API）、Nuxt.jsを組み合わせたフルスタックなSNSプラットフォームです。

使用技術
バックエンド
・PHP: 8.4.4

・Laravel: 8.83.8

フロントエンド
・Nuxt.js: 2.x

・Firebase: Authentication（認証）

インフラ・ツール
・Docker / Docker Compose: 開発環境構築

・phpMyAdmin: データベース管理

環境構築手順
1. リポジトリのクローンとDockerビルド
git clone git@github.com:coachtech-material/laravel-docker-template.git
mv laravel-docker-template marekt
cd marekt
docker-compose up -d --build
2. Laravel（API）の設定
コンテナ内に入り、ライブラリのインストールと設定を行います。

docker-compose exec php bash

# ライブラリインストール
composer install

# 環境変数の設定
cp .env.example .env
.env ファイルを開き、以下のDB接続情報を設定してください。

Plaintext
DB_CONNECTION=mysql

DB_HOST=mysql

DB_PORT=3306

DB_DATABASE=laravel_db

DB_USERNAME=laravel_user

DB_PASSWORD=laravel_pass

※その他、Firebaseとの連携に必要な環境変数も必要に応じて追記してください。

3. キー生成・DBセットアップ
# アプリケーションキーの生成
php artisan key:generate

# マイグレーションとシーディングの実行
php artisan migrate
php artisan db:seed
4. フロントエンド（Nuxt.js）の設定
フロントエンド側のディレクトリへ移動し、起動します。

Bash
cd ../frontend
npm install
npm run dev

データベース設計（ER図）
以下の構成でデータベースを構築しています。
<img width="591" height="691" alt="ER drawio" src="https://github.com/user-attachments/assets/1075ba0d-f5c4-462c-9ff8-56d769633ef5" />

・users: ユーザー情報

・posts: 投稿内容

・comments: 投稿に対するコメント

・likes: 投稿に対する「いいね」

URL
開発環境（フロントエンド）: http://localhost:3000/

APIベース（バックエンド）: http://localhost/

phpMyAdmin: http://localhost:8080/
