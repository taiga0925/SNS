# SNSアプリ

Firebase認証、Laravel（API）、Nuxt.jsを組み合わせたフルスタックなSNSプラットフォームです。

## 使用技術

**バックエンド**
・PHP: 8.4.4
・Laravel: 8.83.8

**フロントエンド**
・Nuxt.js: 2.x
・Firebase: Authentication（認証）

**インフラ・ツール**
・Docker / Docker Compose: 開発環境構築
・phpMyAdmin: データベース管理

---

## 環境構築手順

### 1. リポジトリのクローンとDockerビルド
本リポジトリをクローンし、Dockerコンテナを立ち上げます。

```bash
git clone git@github.com:taiga0925/SNS.git
cd SNS
docker-compose up -d --build
```

### 2. Laravel（API）の設定
コンテナ内に入り、ライブラリのインストールと各種設定を行います。

```bash
docker-compose exec php bash
```

# ライブラリインストール
```bash
composer install
```

# 環境変数の設定
```bash
cp .env.example .env
```
.env ファイルを開き、以下のDB接続情報を設定してください。
※ DB_HOST は 127.0.0.1 ではなく mysql を指定してください。

Plaintext
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
※その他、Firebaseとの連携に必要な環境変数も必要に応じて追記してください。

### 3. キー生成・DBセットアップと権限付与
引き続きPHPコンテナ内で、Laravelの初期設定とデータベースの構築を行います。

# アプリケーションキーの生成
```bash
php artisan key:generate
```

# 【重要】ストレージ等の書き込み権限を付与（500エラー防止）
```bash
chmod -R 777 storage bootstrap/cache
```

# マイグレーションとシーディング（ダミーデータの投入）の実行
```bash
php artisan migrate
php artisan db:seed
```
完了したら exit でコンテナから抜けてください。

### 4. フロントエンド（Nuxt.js）の設定
フロントエンド側のディレクトリへ移動し、環境構築と起動を行います。

```bash
cd sns-frontend
```

# （※実際のフロントエンドのディレクトリ名に合わせてください）
```bash
npm install
npm run dev
```

注意事項（Firebase認証について）

フロントエンドとFirebaseを連携させるため、plugins/firebase.js （またはフロントエンド側の .env）にご自身のFirebaseプロジェクトのAPIキー等の設定情報を記述してください。

Firebaseの仕様上、すでに登録済みのメールアドレスで「新規登録」を行うと 400 Bad Request (EMAIL_EXISTS) エラーが発生します。テスト時は新しいメールアドレスを使用してください。

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
