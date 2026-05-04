<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useSettingsStore } from '@/stores/settings'
import LanguageSwitcher from './LanguageSwitcher.vue'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()
const settings = useSettingsStore()

const mobileMenuOpen = ref(false)

const navLinks = [
  { key: 'nav.home',       path: '/' },
  { key: 'nav.about',      path: '/about' },
  { key: 'nav.study',      path: '/study-in-china' },
  { key: 'nav.talent',     path: '/talent' },
  { key: 'nav.corporate',  path: '/corporate' },
  { key: 'nav.seminars',   path: '/seminars' },
  { key: 'nav.news',       path: '/news' },
  { key: 'nav.contact',    path: '/contact' },
]

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

function toDashboard() {
  router.push(auth.redirectForRole().path)
}

function handleCommand(cmd: string) {
  if (cmd === 'logout') handleLogout()
  else if (cmd === 'dashboard') toDashboard()
}
</script>

<template>
  <header class="app-header">
    <!-- Logo -->
    <router-link to="/" class="logo">
      <img
        v-if="settings.config?.site_logo ?? ''"
        :src="settings.config?.site_logo"
        :alt="settings.config?.site_name ?? 'HRINT'"
        class="logo-img"
      />
      <span v-else>
        Hubei <span class="logo-accent">Horizon</span> HR
      </span>
    </router-link>

    <!-- Desktop Nav -->
    <nav class="nav-desktop">
      <router-link
        v-for="link in navLinks"
        :key="link.path"
        :to="link.path"
        class="nav-link"
        active-class="nav-link--active"
      >
        {{ t(link.key) }}
      </router-link>
    </nav>

    <!-- Actions -->
    <div class="header-actions">
      <LanguageSwitcher />

      <!-- Logged-out state -->
      <template v-if="!auth.isLoggedIn">
        <router-link to="/login">
          <el-button type="default" size="default">{{ t('nav.login') }}</el-button>
        </router-link>
        <router-link to="/register/student">
          <el-button type="primary" size="default">{{ t('nav.register') }}</el-button>
        </router-link>
      </template>

      <!-- Logged-in state: avatar dropdown -->
      <template v-else>
        <el-dropdown trigger="click" @command="handleCommand">
          <div class="user-avatar-btn">
            <el-avatar
              v-if="auth.user?.avatar_url"
              :src="auth.user.avatar_url"
              :size="36"
            />
            <el-avatar v-else :size="36" class="avatar-fallback">
              {{ auth.user?.name?.charAt(0).toUpperCase() ?? 'U' }}
            </el-avatar>
            <span class="user-name">{{ auth.user?.name }}</span>
            <el-icon size="12"><ArrowDown /></el-icon>
          </div>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item command="dashboard">
                <el-icon><HomeFilled /></el-icon> Dashboard
              </el-dropdown-item>
              <el-dropdown-item divided command="logout">
                <el-icon><SwitchButton /></el-icon> {{ t('nav.logout') }}
              </el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>
      </template>

      <!-- Mobile hamburger -->
      <button class="hamburger" @click="mobileMenuOpen = !mobileMenuOpen">
        <el-icon size="22"><Menu /></el-icon>
      </button>
    </div>

    <!-- Mobile menu -->
    <div v-if="mobileMenuOpen" class="mobile-menu">
      <router-link
        v-for="link in navLinks"
        :key="link.path"
        :to="link.path"
        class="mobile-nav-link"
        @click="mobileMenuOpen = false"
      >
        {{ t(link.key) }}
      </router-link>
      <div class="mobile-actions">
        <router-link v-if="!auth.isLoggedIn" to="/login" @click="mobileMenuOpen = false">
          <el-button type="primary" class="w-full">{{ t('nav.login') }}</el-button>
        </router-link>
        <el-button v-else type="danger" plain class="w-full" @click="handleLogout">
          {{ t('nav.logout') }}
        </el-button>
      </div>
    </div>
  </header>
</template>

<style scoped>
.app-header {
  height: 72px;
  background: #fff;
  border-bottom: 1px solid #DEE2E6;
  display: flex;
  align-items: center;
  padding: 0 48px;
  position: sticky;
  top: 0;
  z-index: 100;
  gap: 32px;
}

.logo {
  font-size: 20px;
  font-weight: 700;
  color: #003366;
  text-decoration: none;
  flex-shrink: 0;
}
.logo-accent {
  color: #FF6B35;
}
.logo-img {
  height: 40px;
  object-fit: contain;
}

.nav-desktop {
  display: flex;
  gap: 28px;
  margin-right: auto;
}
.nav-link {
  color: #1A1A2E;
  text-decoration: none;
  font-weight: 500;
  font-size: 15px;
  transition: color 0.2s;
}
.nav-link:hover,
.nav-link--active {
  color: #003366;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-left: auto;
}

.user-avatar-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 8px;
  transition: background 0.2s;
}
.user-avatar-btn:hover {
  background: #E6F0FF;
}
.avatar-fallback {
  background: #003366;
  color: #fff;
  font-weight: 600;
}
.user-name {
  font-size: 14px;
  font-weight: 500;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.hamburger {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  color: #1A1A2E;
}

.mobile-menu {
  display: none;
  position: absolute;
  top: 72px;
  left: 0;
  right: 0;
  background: #fff;
  border-bottom: 1px solid #DEE2E6;
  padding: 16px;
  flex-direction: column;
  gap: 4px;
  z-index: 99;
}
.mobile-nav-link {
  display: block;
  padding: 10px 12px;
  color: #1A1A2E;
  text-decoration: none;
  border-radius: 6px;
  font-size: 15px;
}
.mobile-nav-link:hover {
  background: #E6F0FF;
  color: #003366;
}
.mobile-actions {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #DEE2E6;
}

@media (max-width: 900px) {
  .nav-desktop { display: none; }
  .hamburger   { display: block; }
  .mobile-menu { display: flex; }
  .app-header  { padding: 0 24px; }
}
</style>
