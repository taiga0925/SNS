<template>
  <div class="container">

    <div class="left-side">
      <SideNav />
    </div>

    <div class="main-content">
      <div class="content-title">コメント</div>

      <div v-if="post" class="post-item">
        <div class="post-header">
          <span class="user-name">{{ post.user.name }}</span>

          <div class="actions">
            <button @click="toggleLike(post)">
              <img
                src="/heart.png"
                alt="いいね"
                class="icon"
                :class="{ 'liked': (post.likes || []).some(like => like.user_id === currentUserId) }"
              />
              <span class="action-count">{{ (post.likes || []).length }}</span>
            </button>

            <button v-if="post.user_id === currentUserId" @click="deletePost(post.id)">
              <img src="/cross.png" alt="削除" class="icon" />
            </button>
          </div>
        </div>
        <p class="post-text">{{ post.content }}</p>
      </div>

      <div class="comments-section" v-if="post && post.comments">
        <div class="section-divider">コメント</div>

        <div v-for="comment in post.comments" :key="comment.id" class="comment-item">
          <div class="post-header">
            <span class="user-name">{{ comment.user.name }}</span>

            <div class="actions">
              <button v-if="comment.user_id === currentUserId" @click="deleteComment(comment.id)">
                <img src="/cross.png" alt="削除" class="icon" />
              </button>
            </div>
          </div>
          <p class="post-text">{{ comment.content }}</p>
        </div>
      </div>

      <div class="comment-input-area">
        <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>

        <div class="comment-form">
          <input
            type="text"
            v-model="newComment"
            maxlength="120"
            placeholder="コメントを入力（120文字以内）"
          >
          <button class="submit-btn" @click="submitComment">コメント</button>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
// 共通コンポーネント（サイドナビ）をインポート
import SideNav from '~/components/SideNav.vue'

export default {
  // 使用するコンポーネントを登録
  components: {
    SideNav
  },
  data() {
    return {
      post: null,           // 取得した投稿とコメントのデータ
      newComment: '',        // 入力中のコメント内容
      currentUserId: null,   // 現在ログインしているユーザーのID（ローカルストレージから取得）
      errorMessage: ''       // バリデーションエラーメッセージを入れる箱
    }
  },
  // 画面が表示された時（マウント時）に実行
  async mounted() {
    // ローカルストレージからユーザーIDをNumber型として取得（バグ防止）
    this.currentUserId = Number(localStorage.getItem('user_id'))
    // APIから投稿データを取得
    await this.fetchPost()
  },
  methods: {
    // --- データ取得 ---
    // APIから単一の投稿（およびコメント、いいね、ユーザー情報）を取得
    async fetchPost() {
      try {
        const postId = this.$route.params.id
        const response = await this.$axios.get(`/posts/${postId}`)
        this.post = response.data.post
      } catch (error) {
        console.error('詳細取得エラー:', error)
        alert('投稿の取得に失敗しました')
      }
    },

    // --- いいね機能 ---
    // いいねの追加/削除を切り替える
    async toggleLike(post) {
      if (!this.currentUserId) {
        alert('ログインが必要です。')
        return
      }
      // いいね状態の判定（ post.likes || [] でundefinedエラーを防ぐ ）
      const isLiked = (post.likes || []).some(like => like.user_id === this.currentUserId)
      try {
        if (isLiked) {
          // すでにいいね済みの場合は削除APIを実行
          await this.$axios.delete('/likes', {
            data: { post_id: post.id, user_id: this.currentUserId }
          })
        } else {
          // 未いいねの場合は追加APIを実行
          await this.$axios.post('/likes', {
            post_id: post.id, user_id: this.currentUserId
          })
        }
        // 成功したらデータを再取得して画面を更新
        await this.fetchPost()
      } catch (error) {
        console.error('いいね切り替えエラー:', error)
      }
    },

    // --- 投稿削除機能 ---
    // 投稿を削除し、ホーム画面にリダイレクトする
    async deletePost(postId) {
      if (!confirm('この投稿を削除してもよろしいですか？（コメントもすべて削除されます）')) {
        return
      }
      try {
        await this.$axios.delete(`/posts/${postId}`)
        alert('投稿を削除しました。')
        // 削除後はホーム画面へ移動
        this.$router.push('/')
      } catch (error) {
        console.error('投稿削除エラー:', error)
        alert('投稿の削除に失敗しました')
      }
    },

    // --- コメント機能 ---
    // 新しいコメントを送信する
    async submitComment() {
      // ボタンを押すたびにエラーメッセージをリセット
      this.errorMessage = ''

      // フロントエンドでのバリデーション
      if (!this.newComment) {
        this.errorMessage = 'コメントを入力してください'
        return
      }
      if (this.newComment.length > 120) {
        this.errorMessage = 'コメントは120文字以内で入力してください'
        return
      }

      // ログインチェック
      const userId = localStorage.getItem('user_id')
      if (!userId) {
        this.errorMessage = 'ログインが必要です。再度ログインしてください。'
        this.$router.push('/login')
        return
      }

      try {
        // コメント追加APIを実行
        await this.$axios.post('/comments', {
          user_id: userId,
          post_id: this.$route.params.id,
          content: this.newComment
        })
        // 成功したら入力欄を空にし、データを再取得して一覧を更新
        this.newComment = ''
        await this.fetchPost()
      } catch (error) {
        console.error('コメント送信エラー:', error)
        // Laravel側のバリデーションエラーを受け取って表示
        if (error.response && error.response.status === 422) {
          const backendErrors = error.response.data.errors
          if (backendErrors.content) {
            this.errorMessage = backendErrors.content[0]
          } else {
            this.errorMessage = '入力内容に誤りがあります'
          }
        } else {
          this.errorMessage = 'コメントの送信に失敗しました'
        }
      }
    },

    // コメントを削除する
    async deleteComment(commentId) {
      if (!confirm('このコメントを削除してもよろしいですか？')) {
        return
      }
      try {
        // コメント削除APIを実行
        await this.$axios.delete(`/comments/${commentId}`)
        // 成功したらデータを再取得して一覧を更新
        await this.fetchPost()
      } catch (error) {
        console.error('コメント削除エラー:', error)
        alert('コメントの削除に失敗しました')
      }
    }
  }
}
</script>

<style scoped>
/* =========================================
   全体のレイアウト
   （ホーム画面index.vueと同じ Flexbox 構造）
   ========================================= */
.container {
  display: flex;
  color: white;
  min-height: 100vh;
}

/* 左側エリア：サイドナビゲーション（幅280px固定、白い境界線） */
.left-side {
  width: 280px;
  border-right: 1px solid #fff;
  box-sizing: border-box;
}

/* 右側エリア：メインコンテンツ（残りの幅をすべて使う） */
.main-content {
  flex: 1;
}

/* =========================================
   共通スタイル（見出し、区切り線）
   ========================================= */
/* ページタイトル、区切りテキストの下の実線（白） */
.content-title, .section-divider {
  border-bottom: 1px solid #fff;
}

/* ページタイトル */
.content-title {
  font-size: 20px;
  font-weight: bold;
  padding: 20px 30px;
  margin: 0;
}

/* コメント一覧の前の区切りテキスト（中央揃え） */
.section-divider {
  text-align: center;
  font-size: 16px;
  padding: 15px 0;
}

/* =========================================
   投稿、コメントアイテムのスタイル
   ========================================= */
/* 投稿、コメントごとの下の区切り線（白）と余白 */
.post-item, .comment-item {
  border-bottom: 1px solid #fff;
  padding: 20px 30px;
}

/* ヘッダー周り（名前とアクションボタン群の横並び配置） */
.post-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

/* ユーザー名（ホーム画面より少し大きく、右側に余白） */
.user-name {
  font-weight: bold;
  font-size: 16px;
  margin-right: 20px;
}

/* アクションボタン群（いいね、削除アイコンを横並びに） */
.actions {
  display: flex;
  align-items: center;
}

/* 各アクションボタン特有の背景や枠線を消す */
.actions button {
  background: none;
  border: none;
  cursor: pointer;
  color: white;
  display: flex;
  align-items: center;
  margin-left: 0;
  margin-right: 15px;
  padding: 0;
}

/* 各アイコンのスタイル（ダーク背景用に白く反転） */
.icon {
  width: 22px;
  height: auto;
  cursor: pointer;
  filter: brightness(0) invert(1);
}

/* いいねの数（数字） */
.action-count {
  margin-left: 5px;
  font-size: 14px;
}

/* いいね済みのハート（赤色に変換するCSSフィルター） */
.liked {
  filter: invert(27%) sepia(91%) saturate(7456%) hue-rotate(356deg) brightness(101%) contrast(114%);
}

/* 投稿・コメントの本文 */
.post-text {
  margin: 0;
  line-height: 1.5;
}

/* =========================================
   コメント入力エリア
   ========================================= */
/* コメント入力エリア全体の余白 */
.comment-input-area {
  padding: 20px 30px;
}

/* コメント入力フォーム（入力欄とボタンの横並び） */
.comment-form {
  display: flex;
  gap: 15px;
  align-items: flex-start;
}

/* コメント入力欄（白い枠線、角丸、透明背景、入力文字を白に） */
.comment-form input {
  flex: 1;
  padding: 12px 15px;
  border: 1px solid #fff;
  border-radius: 8px;
  background-color: transparent;
  color: white;
  font-size: 14px;
  box-sizing: border-box;
}

/* 入力欄のプレースホルダーをグレーに */
.comment-form input::placeholder {
  color: #888;
}

/* 送信ボタン（サンプルのデザイン：紫色、完全な角丸、太字、横幅自動） */
.submit-btn {
  background-color: #5c38ff;
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 50px;
  cursor: pointer;
  font-weight: bold;
  font-size: 14px;
  white-space: nowrap;
}

/* =========================================
   エラーメッセージ
   ========================================= */
/* バリデーションエラー用の赤文字スタイル */
.error-text {
  color: #ff4d4f;
  font-size: 14px;
  margin-top: 0;
  margin-bottom: 10px;
  margin-left: 5px;
}
</style>
