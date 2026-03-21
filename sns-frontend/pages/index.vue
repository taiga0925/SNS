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
              <img src="/heart.png" alt="いいね" class="icon" :class="{ 'liked': post.likes.some(like => like.user_id === currentUserId) }" />
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
// (script部分は変更なし。そのまま引き継ぎます)
import firebase from '~/plugins/firebase'
import SideNav from '~/components/SideNav.vue'

export default {
  components: {
    SideNav
  },
  data() {
    return {
      posts: [],
      newPost: '',
      currentUserId: null
    }
  },
  created() {
    firebase.auth().onAuthStateChanged(user => {
      if (!user) {
        this.$router.push('/login')
      } else {
        this.fetchPosts()
      }
    })
  },
  async mounted() {
    this.currentUserId = Number(localStorage.getItem('user_id'))
    await this.fetchPosts()
  },
  methods: {
    async fetchPosts() {
      try {
        const res = await this.$axios.get('/posts')
        this.posts = res.data.data;
      } catch (error) {
        console.error('投稿一覧取得エラー:', error)
      }
    },
    async deletePost(postId) {
      if (!confirm('この投稿を削除してもよろしいですか？（コメントもすべて削除されます）')) {
        return
      }
      try {
        await this.$axios.delete(`/posts/${postId}`)
        await this.fetchPosts()
        alert('投稿を削除しました。')
      } catch (error) {
        console.error('投稿削除エラー:', error)
        alert('投稿の削除に失敗しました')
      }
    },
    async toggleLike(post) {
      if (!this.currentUserId) {
        alert('ログインが必要です。')
        return
      }
      const isLiked = post.likes.some(like => like.user_id === this.currentUserId)
      try {
        if (isLiked) {
          await this.$axios.delete('/likes', {
            data: { post_id: post.id, user_id: this.currentUserId }
          })
        } else {
          await this.$axios.post('/likes', {
            post_id: post.id, user_id: this.currentUserId
          })
        }
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
.container {
  display: flex;
  color: white;
  min-height: 100vh;
}

/* ★修正：ここで画面下部まで届く真っ直ぐな線を引く */
.left-side {
  width: 280px;
  border-right: 1px solid #fff; /* ★SideNav.vue からここへ移動 */
  box-sizing: border-box; /* paddingを含めた幅計算 */
}

.main-content {
  flex: 1;
}

/* 見出しの線と余白 */
.content-title {
  font-size: 20px;
  font-weight: bold;
  border-bottom: 1px solid #fff;
  padding: 20px 30px;
  margin: 0;
}

/* 投稿ごとの区切り線と余白 */
.post-item {
  border-bottom: 1px solid #fff;
  padding: 20px 30px;
}

/* ★修正：justify-content: space-between を削除して左寄せにする */
.post-header {
  display: flex;
  /* justify-content: space-between; ★これを削除 */
  align-items: center;
  margin-bottom: 15px;
}

/* ★修正：名前の右側に余白を設けて、ボタンと離す */
.user-name {
  font-weight: bold;
  color: white;
  text-decoration: none;
  font-size: 16px;
  margin-right: 20px; /* ★名前と最初のボタン(いいね)の間の隙間 */
}

.actions {
  display: flex;
  align-items: center;
}

/* ボタンとリンクの余白調整 */
.actions button, .actions a {
  background: none;
  border: none;
  cursor: pointer;
  color: white;
  display: flex;
  align-items: center;
  margin-left: 0; /* ★左側のマージンをリセット */
  margin-right: 15px; /* ★ボタン同士の間隔を右側で調整 */
  text-decoration: none;
  padding: 0;
}

/* 各アイコンの白化 */
.icon {
  width: 22px;
  height: auto;
  cursor: pointer;
  filter: brightness(0) invert(1);
}

.action-count {
  margin-left: 5px;
  font-size: 14px;
}

/* いいね済みの赤色フィルター */
.liked {
  filter: invert(27%) sepia(91%) saturate(7456%) hue-rotate(356deg) brightness(101%) contrast(114%);
}

.post-text {
  margin: 0;
  line-height: 1.5;
}
</style>
