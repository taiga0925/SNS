<template>
  <div class="container">

    <div class="left-side">
      <SideNav @post-created="fetchPosts" />
    </div>

    <div class="main-content">
      <div class="content-title">ホーム</div>

      <div v-for="post in posts" :key="post.id" class="post-item">
        <div class="post-header">
          <span class="user-name">{{ post.user.name }}</span>

          <div class="actions">
            <button @click="toggleLike(post)">
              <img
                src="/heart.png"
                alt="いいね"
                class="icon"
                :class="{ 'liked': post.likes.some(like => like.user_id === currentUserId) }"
              />
              <span class="action-count">{{ post.likes.length }}</span>
            </button>

            <button v-if="post.user_id === currentUserId" @click="deletePost(post.id)">
              <img src="/cross.png" alt="削除" class="icon" />
            </button>

            <nuxt-link :to="`/posts/${post.id}`">
              <img src="/detail.png" alt="コメント" class="icon" />
            </nuxt-link>
          </div>
        </div>
        <p class="post-text">{{ post.content }}</p>
      </div>
    </div>
  </div>
</template>

<script>
// Firebaseとサイドナビコンポーネントの読み込み
import firebase from '~/plugins/firebase'
import SideNav from '~/components/SideNav.vue'

export default {
  // 使用するコンポーネントを登録
  components: {
    SideNav
  },
  data() {
    return {
      posts: [],           // 取得した投稿一覧を格納する配列
      newPost: '',         // 新規投稿内容
      currentUserId: null  // 現在ログインしているユーザーのID
    }
  },
  // インスタンスが作成された直後に実行（画面描画前）
  created() {
    // Firebaseの認証状態を監視し、ログインしていなければログイン画面へ強制移動
    firebase.auth().onAuthStateChanged(user => {
      if (!user) {
        this.$router.push('/login')
      } else {
        // ログインしていれば投稿一覧を取得する
        this.fetchPosts()
      }
    })
  },
  // 画面が描画された時に実行
  async mounted() {
    // ローカルストレージからユーザーIDを取得（数値型に変換してバグを防ぐ）
    this.currentUserId = Number(localStorage.getItem('user_id'))
    await this.fetchPosts()
  },
  methods: {
    // --- 投稿データ取得 ---
    // Laravel APIから投稿一覧データを取得、配列に格納
    async fetchPosts() {
      try {
        const res = await this.$axios.get('/posts')
        this.posts = res.data.data;
      } catch (error) {
        console.error('投稿一覧取得エラー:', error)
      }
    },

    // --- 投稿削除機能 ---
    // 投稿を削除する
    async deletePost(postId) {
      if (!confirm('この投稿を削除してもよろしいですか？（コメントもすべて削除されます）')) {
        return
      }
      try {
        await this.$axios.delete(`/posts/${postId}`)
        // 削除成功後、リストを最新の状態に更新
        await this.fetchPosts()
        alert('投稿を削除しました。')
      } catch (error) {
        console.error('投稿削除エラー:', error)
        alert('投稿の削除に失敗しました')
      }
    },

    // --- いいね機能 ---
    // いいねの追加・取り消しを切り替え
    async toggleLike(post) {
      // ログインチェック
      if (!this.currentUserId) {
        alert('ログインが必要です。')
        return
      }

      // 自分がすでにこの投稿にいいねしているか判定
      const isLiked = post.likes.some(like => like.user_id === this.currentUserId)

      try {
        if (isLiked) {
          // すでにいいね済みの場合は、いいねを削除
          await this.$axios.delete('/likes', {
            data: { post_id: post.id, user_id: this.currentUserId }
          })
        } else {
          // 未いいねの場合は、いいねを追加
          await this.$axios.post('/likes', {
            post_id: post.id, user_id: this.currentUserId
          })
        }
        // いいね状態データを再取得して画面を更新
        await this.fetchPosts()
      } catch (error) {
        console.error('いいね切り替えエラー:', error)
        alert('いいねの処理に失敗しました')
      }
    }
  }
}
</script>

<style scoped>
/* =========================================
   全体のレイアウト
   ========================================= */
/* 画面全体*/
.container {
  display: flex;
  color: white;
  min-height: 100vh;
}

/* サイドナビゲーション */
.left-side {
  width: 280px;
  border-right: 1px solid #fff;
  box-sizing: border-box;
}

/* メインコンテンツ */
.main-content {
  flex: 1;
}

/* =========================================
   各要素のスタイル（見出し・境界線）
   ========================================= */
/* ホーム画面のタイトル */
.content-title {
  font-size: 20px;
  font-weight: bold;
  border-bottom: 1px solid #fff;
  padding: 20px 30px;
  margin: 0;
}

/* 投稿ごとの区切り */
.post-item {
  border-bottom: 1px solid #fff;
  padding: 20px 30px;
}

/* 投稿上部 */
.post-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

/* 投稿者名 */
.user-name {
  font-weight: bold;
  color: white;
  text-decoration: none;
  font-size: 16px;
  margin-right: 20px;
}

/* =========================================
   アクションボタン・アイコンのスタイル
   ========================================= */
/* いいね、削除、詳細ボタンを横並び */
.actions {
  display: flex;
  align-items: center;
}

/* 各ボタン調整 */
.actions button, .actions a {
  background: none;
  border: none;
  cursor: pointer;
  color: white;
  display: flex;
  align-items: center;
  margin-left: 0;
  margin-right: 15px;
  text-decoration: none;
  padding: 0;
}

/* アイコン画像 */
.icon {
  width: 22px;
  height: auto;
  cursor: pointer;
  filter: brightness(0) invert(1);
}

/* いいねのカウント数字 */
.action-count {
  margin-left: 5px;
  font-size: 14px;
}

/* いいね済みの場合、アイコンを赤色に変換 */
.liked {
  filter: invert(27%) sepia(91%) saturate(7456%) hue-rotate(356deg) brightness(101%) contrast(114%);
}

/* 投稿本文 */
.post-text {
  margin: 0;
  line-height: 1.5;
}
</style>
