<template>
  <div class="side-nav">

    <div class="logo-area">
      <img src="/logo.png" alt="Logo" class="logo">
    </div>

    <div class="menu">
      <div class="menu-item" @click="$router.push('/')">
        <img src="/home.png" alt="Home" class="icon" /> <span>ホーム</span>
      </div>

      <div class="menu-item" @click="logout">
        <img src="/logout.png" alt="Logout" class="icon" /> <span>ログアウト</span>
      </div>
    </div>

    <div class="post-area">
      <p class="section-title">シェア</p>

      <textarea v-model="content" placeholder="今の気持ちをシェアしよう" maxlength="120"></textarea>

      <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>

      <button @click="createPost" class="share-btn">
        シェアする
      </button>
    </div>
  </div>
</template>

<script>
// Firebaseのプラグインを読み込み（ログアウト処理で使用）
import firebase from '~/plugins/firebase'

export default {
  data() {
    return {
      content: '',         // 入力中の投稿内容
      errorMessage: '',    // エラーメッセージを入れる箱
      currentUserId: null  // 現在ログインしているユーザーのID
    }
  },
  // コンポーネントが画面に表示された時に実行
  mounted() {
    // ローカルストレージからログイン中のユーザーIDを取得
    this.currentUserId = localStorage.getItem('user_id')
  },

  methods: {
    // --- ログアウト処理 ---
    async logout() {
      // 誤操作防止の確認ダイアログ
      if (!confirm('ログアウトしてもよろしいですか？')) {
        return
      }
      try {
        // Firebase側のログアウト処理を実行
        await firebase.auth().signOut()
        // ローカルストレージに保存していたユーザー情報を削除
        localStorage.removeItem('user_id')
        localStorage.removeItem('user_name')

        // ログイン画面へ遷移
        this.$router.push('/login')
      } catch (error) {
        console.error('ログアウトエラー:', error)
        alert('ログアウトに失敗しました')
      }
    },

    // --- 新規投稿（シェア）処理 ---
    async createPost() {
      // ボタンを押すたびに過去のエラーメッセージをリセット
      this.errorMessage = ''

      // フロントエンドの入力チェック（空のまま送信するのを防ぐ）
      if (!this.content) {
        this.errorMessage = '投稿内容を入力してください'
        return
      }

      // ログイン情報の確認
      const userId = localStorage.getItem('user_id')
      if (!userId) {
        this.errorMessage = 'ログイン情報が見つかりません。再度ログインしてください。'
        this.$router.push('/login')
        return
      }

      try {
        // LaravelのAPIへ新規投稿リクエストを送信
        await this.$axios.post('/posts', {
          user_id: userId,
          content: this.content
        })

        // 成功したら入力欄を空
        this.content = ''
        alert('シェアしました！')

        // 親コンポーネント(index.vueなど)に「投稿完了」を知らせ、タイムラインを更新
        this.$emit('post-created')

      } catch (error) {
        console.error('投稿エラー:', error)
        // Laravel側のバリデーションエラーを受け取って画面に表示する処理
        if (error.response && error.response.status === 422) {
          const backendErrors = error.response.data.errors
          if (backendErrors.content) {
            this.errorMessage = backendErrors.content[0]
          } else {
            this.errorMessage = '入力内容に誤りがあります'
          }
        } else {
          this.errorMessage = '通信エラーが発生しました'
        }
      }
    }
  }
}
</script>

<style scoped>
/* =========================================
   サイドナビ全体のレイアウト
   ========================================= */
/* サイドナビ全体 */
.side-nav {
  padding: 20px 30px;
  height: auto;
  width: 280px;
  box-sizing: border-box;
}

/* =========================================
   ロゴとメニューのスタイル
   ========================================= */
/* ロゴ画像 */
.logo {
  width: 120px;
  margin-bottom: 40px;
  filter: brightness(0) invert(1);
}

.menu {
  margin-bottom: 40px;
}

/* 各メニュー項目 */
.menu-item {
  display: flex;
  align-items: center;
  margin-bottom: 25px;
  cursor: pointer;
  color: white;
  font-weight: bold;
  font-size: 16px;
}

/* メニューアイコン */
.icon {
  width: 25px;
  margin-right: 15px;
  filter: brightness(0) invert(1);
}

/* =========================================
   シェア（新規投稿）エリアのスタイル
   ========================================= */
.post-area {
  margin-top: 30px;
}

/* 見出し */
.section-title {
  color: white;
  font-weight: bold;
  margin-bottom: 10px;
}

/* 投稿用のテキストエリア */
textarea {
  width: 100%;
  height: 120px;
  background-color: transparent;
  color: white;
  border: 1px solid white;
  border-radius: 10px;
  padding: 10px;
  resize: none;
  margin-bottom: 15px;
  box-sizing: border-box;
  font-family: inherit;
}

/* テキストエリアの入力文字*/
textarea::placeholder {
  color: #888;
}

/* シェアボタン */
.share-btn {
  background-color: #5c38ff;
  color: white;
  border: none;
  padding: 10px 30px;
  border-radius: 50px;
  cursor: pointer;
  display: block;
  margin-left: auto; /* display: block; と組み合わせて右寄せ */
  font-weight: bold;
  font-size: 14px;
}

/* =========================================
   エラーメッセージのスタイル
   ========================================= */
.error-text {
  color: #ff4d4f;
  font-size: 14px;
  margin-top: -5px;
  margin-bottom: 10px;
}
</style>
