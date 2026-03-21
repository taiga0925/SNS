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
import firebase from '~/plugins/firebase'

export default {
  data() {
    return {
      content: '',
      errorMessage: '',
      currentUserId: null
    }
  },
  mounted() {
    this.currentUserId = localStorage.getItem('user_id')
  },
  methods: {
    goToProfile() {
      if (this.currentUserId) {
        this.$router.push(`/users/${this.currentUserId}`)
      } else {
        alert('ログイン情報が見つかりません。')
      }
    },
    async logout() {
      if (!confirm('ログアウトしてもよろしいですか？')) {
        return
      }
      try {
        await firebase.auth().signOut()
        localStorage.removeItem('user_id')
        this.$router.push('/login')
      } catch (error) {
        console.error('ログアウトエラー:', error)
        alert('ログアウトに失敗しました')
      }
    },
    async createPost() {
      this.errorMessage = ''

      if (!this.content) {
        this.errorMessage = '投稿内容を入力してください'
        return
      }

      const userId = localStorage.getItem('user_id')
      if (!userId) {
        this.errorMessage = 'ログイン情報が見つかりません。再度ログインしてください。'
        this.$router.push('/login')
        return
      }

      try {
        await this.$axios.post('/posts', {
          user_id: userId,
          content: this.content
        })

        this.content = ''
        alert('シェアしました！')

        this.$emit('post-created')
      } catch (error) {
        console.error('投稿エラー:', error)
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
/* ★修正：途切れてしまうボーダーを削除し、パディングを調整 */
.side-nav {
  padding: 20px 30px;
  height: auto; /* height: 100vh から auto に変更 */
  width: 280px;
  box-sizing: border-box;
  /* border-right: 1px solid #fff; ★これを削除 */
}

/* ★修正：ロゴを白く反転 */
.logo {
  width: 120px;
  margin-bottom: 40px; /* 余白を広めに */
  filter: brightness(0) invert(1);
}

.menu {
  margin-bottom: 40px;
}

/* ★修正：メニューの文字色とアイコンの白化 */
.menu-item {
  display: flex;
  align-items: center;
  margin-bottom: 25px;
  cursor: pointer;
  color: white; /* 文字を白に */
  font-weight: bold;
  font-size: 16px;
}
.icon {
  width: 25px;
  margin-right: 15px;
  filter: brightness(0) invert(1); /* 黒いアイコンを白に */
}

.post-area {
  margin-top: 30px;
}

/* ★追加：見出しのスタイル */
.section-title {
  color: white;
  font-weight: bold;
  margin-bottom: 10px;
}

/* ★修正：テキストエリアの背景を透明にして白枠に */
textarea {
  width: 100%;
  height: 120px;
  background-color: transparent; /* 背景をなくす */
  color: white; /* 入力文字を白に */
  border: 1px solid white; /* 白い枠線 */
  border-radius: 10px;
  padding: 10px;
  resize: none;
  margin-bottom: 15px;
  box-sizing: border-box;
  font-family: inherit;
}
textarea::placeholder {
  color: #888; /* プレースホルダーは見やすいグレーに */
}

/* ★修正：ボタンの色、形、右寄せ配置 */
.share-btn {
  background-color: #5c38ff; /* 画像に近い紫色 */
  color: white;
  border: none;
  padding: 10px 30px;
  border-radius: 50px; /* 丸薬型 */
  cursor: pointer;
  display: block;
  margin-left: auto; /* 右に寄せるための魔法の記述 */
  font-weight: bold;
  font-size: 14px;
}

.error-text {
  color: #ff4d4f;
  font-size: 14px;
  margin-top: -5px;
  margin-bottom: 10px;
}
</style>
