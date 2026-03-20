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
import firebase from '~/plugins/firebase'
import AuthHeader from '~/components/AuthHeader.vue'

export default {
  components: {
    AuthHeader
  },
  data() {
    return {
      email: '',
      password: '',
      // 各項目のエラーメッセージを入れる箱
      errors: {
        email: '',
        password: '',
        general: ''
      }
    }
  },
  methods: {
    async login() {
      // 1. まず前回の古いエラーメッセージをリセット
      this.errors = { email: '', password: '', general: '' }
      let hasError = false

      // 2. メールアドレスの必須チェック
      if (!this.email) {
        this.errors.email = 'メールアドレスを入力してください'
        hasError = true
      }

      // 3. パスワードの必須チェック
      if (!this.password) {
        this.errors.password = 'パスワードを入力してください'
        hasError = true
      }

      // エラーがあればここで処理をストップ
      if (hasError) return

      try {
        // Firebase認証
        await firebase.auth().signInWithEmailAndPassword(this.email, this.password)

        // Laravel APIへログインリクエスト
        const response = await this.$axios.post('/login', {
          email: this.email,
          password: this.password
        })

        // レスポンスからユーザー情報を取得して保存
        const user = response.data.user
        localStorage.setItem('user_id', user.id)
        localStorage.setItem('user_name', user.name)

        this.$router.push('/') // トップページへ移動

      } catch (error) {
        console.error(error)
        // パスワード間違いや、登録されていないメールアドレスの場合のエラー
        this.errors.general = 'メールアドレスまたはパスワードが間違っています。'
      }
    }
  }
}
</script>

<style scoped>
/* 全体の背景 */
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  /* ヘッダーの高さを考慮して画面中央に配置するため min-height を調整 */
  min-height: 100vh;
  background-color: transparent; /* 背景色はグローバルCSS(main.css)に任せるため透明でOK */
  font-family: 'Helvetica Neue', Arial, sans-serif;
}

/* 認証ボックス (影を消し、余白を調整) */
.auth-box {
  background: white;
  padding: 50px 40px;
  border-radius: 10px;
  text-align: center;
  width: 400px;
  box-shadow: none; /* 目標デザインに合わせて影を削除 */
}

.auth-box h2 {
  font-weight: bold;
  margin-bottom: 30px;
}

/* 入力フィールド (角丸、余白、サイズ調整) */
input {
  display: block;
  width: 100%;
  margin: 15px 0 5px 0;
  padding: 12px 10px;
  border: 1px solid #ddd;
  border-radius: 8px; /* スムーズな角丸 */
  box-sizing: border-box;
}

/* プレースホルダー（入力欄のうすいヒント文字）をグレーに */
input::placeholder {
  color: #888;
}

/* ボタン (青色、丸薬型、立体感) */
button {
  margin-top: 25px;
  margin-left: auto;
  margin-right: auto;
  display: block;
  padding: 10px 40px;
  background-color: #3f51b5; /* 新規登録と同じ青色 */
  color: white;
  border: none;
  border-bottom: 5px solid #283593; /* 立体感を出すための下部ボーダー */
  border-radius: 50px; /* 完全な角丸（丸薬型） */
  cursor: pointer;
  width: auto;
  font-weight: bold;
  font-size: 14px;
}

/* エラーテキスト */
.error-text {
  color: #ff4d4f;
  font-size: 12px;
  margin: 0 0 10px 5px;
  text-align: left;
}
.text-center {
  text-align: center;
}
</style>
