<template>
  <div>
    <AuthHeader />

    <div class="auth-container">

      <div class="auth-box">
        <h2>新規登録</h2>

        <input type="text" v-model="name" placeholder="ユーザーネーム" />
        <p v-if="errors.name" class="error-text">{{ errors.name }}</p>

        <input type="email" v-model="email" placeholder="メールアドレス" />
        <p v-if="errors.email" class="error-text">{{ errors.email }}</p>

        <input type="password" v-model="password" placeholder="パスワード" />
        <p v-if="errors.password" class="error-text">{{ errors.password }}</p>

        <p v-if="errors.general" class="error-text text-center">{{ errors.general }}</p>

        <button @click="register">新規登録</button>
      </div>
    </div>
  </div>
</template>

<script>
// Firebaseプラグインとヘッダーコンポーネントの読み込み
import firebase from '~/plugins/firebase'
import AuthHeader from '~/components/AuthHeader.vue'

export default {
  components: {
    AuthHeader
  },
  data() {
    return {
      name: '',       // 入力されたユーザーネーム
      email: '',      // 入力されたメールアドレス
      password: '',   // 入力されたパスワード
      // 各入力欄および全体のエラーメッセージを管理
      errors: {
        name: '',
        email: '',
        password: '',
        general: ''
      }
    }
  },
  methods: {
    // --- 新規登録処理 ---
    async register() {
      // 古いエラーメッセージを全てリセット（初期化）
      this.errors = { name: '', email: '', password: '', general: '' }
      let hasError = false // エラーの有無を判定するためのフラグ

      // フロントエンドでのユーザーネームのバリデーション
      if (!this.name) {
        this.errors.name = 'ユーザーネームを入力してください'
        hasError = true
      } else if (this.name.length > 20) {
        // 20文字以内
        this.errors.name = 'ユーザーネームは20文字以内で入力してください'
        hasError = true
      }

      // フロントエンドでのメールアドレスのバリデーション
      // メールアドレスが正しい形式（○○@○○.○○）になっているかを確認
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

      if (!this.email) {
        this.errors.email = 'メールアドレスを入力してください'
        hasError = true
      } else if (!emailRegex.test(this.email)) {
        this.errors.email = '有効なメールアドレス形式で入力してください'
        hasError = true
      }

      // フロントエンドでのパスワードのバリデーション
      if (!this.password) {
        this.errors.password = 'パスワードを入力してください'
        hasError = true
      } else if (this.password.length < 6) {
        // セキュリティのため、最低6文字以上を要求
        this.errors.password = 'パスワードは6文字以上で入力してください'
        hasError = true
      }

      // 入力エラーがあれば、ここで処理をストップ
      if (hasError) return

      try {
        // Firebase Authentication を使って、新しいユーザーアカウントを作成
        await firebase.auth().createUserWithEmailAndPassword(this.email, this.password)

        // Firebaseでの作成が成功したら、自作のLaravel APIにもユーザー情報を送信しDBに保存
        await this.$axios.post('/register', {
          name: this.name,
          email: this.email,
          password: this.password, // Laravel側でハッシュ化して保存
        })

        // 登録がすべて成功したらアラートを出し、ログイン画面へ遷移
        alert('登録が完了しました')
        this.$router.push('/login')

      } catch (error) {
        console.error(error)
        // Firebaseで「すでに登録されているメールアドレス」と弾かれた場合や、通信エラーの場合のメッセージ
        this.errors.general = '登録に失敗しました。別のメールアドレスをお試しください。'
      }
    }
  }
}
</script>

<style scoped>
/* =========================================
   子コンポーネント(AuthHeader)のスタイル上書き
   ========================================= */
/* ::v-deep を使うことで、scoped (このファイル限定) の制限を越えて、
   子コンポーネントのクラスに対してスタイルを強制的に適用 */
::v-deep .header-links a {
  color: white !important;
}
::v-deep .header-logo img {
  filter: brightness(0) invert(1);
}

/* =========================================
   全体のレイアウト
   ========================================= */
/* 画面全体 */
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #131821;
  font-family: 'Helvetica Neue', Arial, sans-serif;
}

/* =========================================
   新規登録ボックス
   ========================================= */
/* メインボックス */
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
/* 各入力欄 */
input {
  display: block;
  width: 100%;
  margin: 15px 0 5px 0;
  padding: 12px 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-sizing: border-box;
}

/* 入力前の入力文字 */
input::placeholder {
  color: #888;
}

/* 登録ボタン  */
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
/* 入力欄赤文字エラー */
.error-text {
  color: #ff4d4f;
  font-size: 12px;
  margin: 0 0 10px 5px;
  text-align: left;
}

/* 全体エラーメッセージ用 */
.text-center {
  text-align: center;
}
</style>
