<template>
  <div>
    <AuthHeader />

    <div class="auth-container">

      <div class="auth-box">
        <h2>ログイン</h2>

        <input type="email" v-model="email" placeholder="メールアドレス" />
        <p v-if="errors.email" class="error-text">{{ errors.email }}</p>

        <input type="password" v-model="password" placeholder="パスワード" />
        <p v-if="errors.password" class="error-text">{{ errors.password }}</p>

        <p v-if="errors.general" class="error-text text-center">{{ errors.general }}</p>

        <button @click="login">ログイン</button>
      </div>
    </div>
  </div>
</template>

<script>
// Firebaseプラグインとヘッダーコンポーネントの読み込み
import firebase from '~/plugins/firebase'
import AuthHeader from '~/components/AuthHeader.vue'

export default {
  // 使用するコンポーネントの登録
  components: {
    AuthHeader
  },
  data() {
    return {
      email: '',      // 入力されたメールアドレス
      password: '',   // 入力されたパスワード

      // バリデーションや通信時の各エラーメッセージを管理
      errors: {
        email: '',    // メール入力欄の下に出すエラー
        password: '', // パスワード入力欄の下に出すエラー
        general: ''   // 認証失敗時など、全体に関わるエラー
      }
    }
  },
  methods: {
    // --- ログイン処理 ---
    async login() {
      // 古いエラーメッセージをすべてリセット（初期化）
      this.errors = { email: '', password: '', general: '' }

      // エラーの有無を判定するためのフラグ
      let hasError = false

      // フロントエンドでの入力チェック（メールアドレスが空欄ではないか）
      if (!this.email) {
        this.errors.email = 'メールアドレスを入力してください'
        hasError = true
      }

      // フロントエンドでの入力チェック（パスワードが空欄ではないか）
      if (!this.password) {
        this.errors.password = 'パスワードを入力してください'
        hasError = true
      }

      // 入力漏れがあれば、ここで処理をストップ（APIの無駄打ち防止）
      if (hasError) return

      try {
        // Firebase Authentication を使ってメールとパスワードでログイン認証
        await firebase.auth().signInWithEmailAndPassword(this.email, this.password)

        // Firebaseの認証が通ったら、自作のLaravel APIにもログインリクエストを送る
        // Laravel側でユーザー情報を取得し、セッション等を確立するため
        const response = await this.$axios.post('/login', {
          email: this.email,
          password: this.password
        })

        // Laravelから返ってきたユーザー情報（IDや名前）を取り出す
        const user = response.data.user

        // ローカルストレージにユーザーIDと名前を保存（他の画面で「自分」を識別するため）
        localStorage.setItem('user_id', user.id)
        localStorage.setItem('user_name', user.name)

        // 認証とデータの保存が完了、ホーム画面へ遷移
        this.$router.push('/')

      } catch (error) {
        console.error(error)
        // Firebaseで「該当ユーザーなし」「パスワード間違い」等になった場合
        // またはLaravelのAPI通信で失敗した場合に、共通のエラーメッセージを表示
        this.errors.general = 'メールアドレスまたはパスワードが間違っています。'
      }
    }
  }
}
</script>

<style scoped>
/* =========================================
   全体のレイアウト
   ========================================= */
/* 画面全体 */
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: transparent;
  font-family: 'Helvetica Neue', Arial, sans-serif;
}

/* =========================================
   ログイン入力
   ========================================= */
/* メイン */
.auth-box {
  background: white;
  padding: 50px 40px;
  border-radius: 10px;
  text-align: center;
  width: 400px;
  box-shadow: none;
}

/* タイトル */
.auth-box h2 {
  font-weight: bold;
  margin-bottom: 30px;
}

/* =========================================
   入力フィールド・ボタン
   ========================================= */
/* メール・パスワード入力欄 */
input {
  display: block;
  width: 100%;
  margin: 15px 0 5px 0;
  padding: 12px 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-sizing: border-box;
}

/* 入力欄の入力文字 */
input::placeholder {
  color: #888;
}

/* ログインボタン */
button {
  margin-top: 25px;
  margin-left: auto;
  margin-right: auto;
  display: block;
  padding: 10px 40px;
  background-color: #3f51b5;
  color: white;
  border: none;
  border-bottom: 5px solid #283593;
  border-radius: 50px;
  cursor: pointer;
  width: auto;
  font-weight: bold;
  font-size: 14px;
}

/* =========================================
   エラーテキスト
   ========================================= */
/* バリデーションエラー用 */
.error-text {
  color: #ff4d4f;
  font-size: 12px;
  margin: 0 0 10px 5px;
  text-align: left;
}

/* エラー用*/
.text-center {
  text-align: center;
}
</style>
