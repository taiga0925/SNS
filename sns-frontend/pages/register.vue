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
import firebase from '~/plugins/firebase'
import AuthHeader from '~/components/AuthHeader.vue'

export default {
  components: {
    AuthHeader
  },
  data() {
    return {
      name: '',
      email: '',
      password: '',
      errors: {
        name: '',
        email: '',
        password: '',
        general: ''
      }
    }
  },
  methods: {
    async register() {
      // 1. まず前回の古いエラーメッセージを全てリセットする
      this.errors = { name: '', email: '', password: '', general: '' }
      let hasError = false // エラーがあるかどうかの目印

      // 2. ユーザーネームのバリデーション（必須、20文字以内）
      if (!this.name) {
        this.errors.name = 'ユーザーネームを入力してください'
        hasError = true
      } else if (this.name.length > 20) {
        this.errors.name = 'ユーザーネームは20文字以内で入力してください'
        hasError = true
      }

      // 3. メールアドレスのバリデーション（必須、メール形式）
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!this.email) {
        this.errors.email = 'メールアドレスを入力してください'
        hasError = true
      } else if (!emailRegex.test(this.email)) {
        this.errors.email = '有効なメールアドレス形式で入力してください'
        hasError = true
      }

      // 4. パスワードのバリデーション（必須、6文字以上）
      if (!this.password) {
        this.errors.password = 'パスワードを入力してください'
        hasError = true
      } else if (this.password.length < 6) {
        this.errors.password = 'パスワードは6文字以上で入力してください'
        hasError = true
      }

      // 1つでもエラーがあればストップ
      if (hasError) return

      try {
        // 5. Firebaseでユーザー作成
        await firebase.auth().createUserWithEmailAndPassword(this.email, this.password)

        // 6. LaravelのDBにユーザー情報を保存
        await this.$axios.post('/register', {
          name: this.name,
          email: this.email,
          password: this.password,
        })

        // 登録成功したらログイン画面へ
        alert('登録が完了しました')
        this.$router.push('/login')

      } catch (error) {
        console.error(error)
        this.errors.general = '登録に失敗しました。別のメールアドレスをお試しください。'
      }
    }
  }
}
</script>



<style scoped>
::v-deep .header-links a {
  color: white !important;
}
::v-deep .header-logo img {
  filter: brightness(0) invert(1);
}

/* 全体の背景  */
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #131821;
  font-family: 'Helvetica Neue', Arial, sans-serif;
}

/* 認証ボックス  */
.auth-box {
  background: white;
  padding: 50px 40px;
  border-radius: 10px;
  text-align: center;
  width: 400px;
  box-shadow: none;
}

.auth-box h2 {
  font-weight: bold;
  margin-bottom: 30px;
}

/* 入力 */
input {
  display: block;
  width: 100%;
  margin: 15px 0 5px 0;
  padding: 12px 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-sizing: border-box;
}

input::placeholder {
  color: #888;
}

/* ボタンのスタイル */
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

/* エラーテキストのスタイル */
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
