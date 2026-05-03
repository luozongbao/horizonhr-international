<script setup lang="ts">
import LanguageSwitcher from '@/components/common/LanguageSwitcher.vue'
</script>

<template>
  <div class="auth-layout">
    <div class="auth-top">
      <router-link to="/" class="auth-logo">
        Hubei <span class="logo-accent">Horizon</span> HR
      </router-link>
      <LanguageSwitcher />
    </div>
    <main class="auth-main">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.auth-layout {
  min-height: 100vh;
  background: #E6F0FF;
  display: flex;
  flex-direction: column;
}
.auth-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 40px;
}
.auth-logo {
  font-size: 20px;
  font-weight: 700;
  color: #003366;
  text-decoration: none;
}
.logo-accent {
  color: #FF6B35;
}
.auth-main {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 16px 64px;
}
</style>
