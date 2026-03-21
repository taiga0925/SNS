<template>
  <div class="auth-header">
    <img src="/logo.png" class="logo" alt="Logo">
    <div class="header-links">
      <nuxt-link to="/register" class="link">新規登録</nuxt-link>
      <nuxt-link to="/login" class="link">ログイン</nuxt-link>
    </div>
  </div>
</template>

<style scoped>
.auth-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background-color: #131821;
}

.logo {
  height: 50px;
  filter: brightness(0) invert(1);
}

.header-links .link {
  margin-left: 20px;
  text-decoration: none;
  color: #fff; 
  font-weight: bold;
}
</style>
