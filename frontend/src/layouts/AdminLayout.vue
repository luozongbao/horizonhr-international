<script setup lang="ts">
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SidebarNav from '@/components/common/SidebarNav.vue'
import LanguageSwitcher from '@/components/common/LanguageSwitcher.vue'
import type { NavItem } from '@/components/common/SidebarNav.vue'

const { t } = useI18n()
const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()

const collapsed = ref(false)

const navItems = computed<NavItem[]>(() => [
  { icon: 'House',           label: t('admin.dashboard'),  route: '/admin/dashboard' },
  { icon: 'UserFilled',      label: t('admin.users'),      route: '/admin/users' },
  { icon: 'Document',        label: t('admin.resumes'),    route: '/admin/resumes' },
  { icon: 'VideoCamera',     label: t('admin.interviews'), route: '/admin/interviews' },
  { icon: 'Collection',      label: t('admin.seminars'),   route: '/admin/seminars' },
  { icon: 'Bell',            label: t('admin.news'),       route: '/admin/news' },
  { icon: 'Files',           label: t('admin.pages'),      route: '/admin/pages' },
  { icon: 'Setting',         label: t('admin.settings'),   route: '/admin/settings' },
  { icon: 'Translate',       label: t('admin.languages'),  route: '/admin/languages' },
])

const pageTitle = computed(() => {
  const match = navItems.value.find(
    (n) => route.path === n.route || route.path.startsWith(n.route + '/'),
  )
  return match?.label ?? 'Admin'
})

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="admin-layout">
    <!-- Dark sidebar -->
    <aside class="sidebar" :class="{ collapsed }">
      <div class="sidebar-logo">
        <router-link to="/" class="logo-text">
          Hubei <span class="logo-accent">Horizon</span>
          <span v-if="!collapsed" class="logo-sub">Admin</span>
        </router-link>
      </div>

      <SidebarNav :nav-items="navItems" :collapsed="collapsed" :dark="true" />

      <button class="collapse-btn" @click="collapsed = !collapsed">
        <el-icon><DArrowLeft v-if="!collapsed" /><DArrowRight v-else /></el-icon>
      </button>
    </aside>

    <!-- Main -->
    <div class="admin-main" :class="{ 'main-collapsed': collapsed }">
      <header class="admin-topbar">
        <h1 class="page-title">{{ pageTitle }}</h1>
        <div class="topbar-right">
          <LanguageSwitcher />
          <el-dropdown trigger="click" @command="cmd => cmd === 'logout' && logout()">
            <div class="topbar-avatar">
              <el-avatar :size="36" class="user-avatar">
                {{ auth.user?.name?.charAt(0).toUpperCase() ?? 'A' }}
              </el-avatar>
            </div>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item disabled>{{ auth.user?.name }}</el-dropdown-item>
                <el-dropdown-item divided command="logout">
                  <el-icon><SwitchButton /></el-icon> Logout
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </header>

      <main class="admin-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #F5F7FA;
}

/* Dark sidebar */
.sidebar {
  width: 260px;
  background: #003366;
  position: fixed;
  top: 0; left: 0;
  height: 100vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  transition: width 0.25s ease;
  z-index: 50;
}
.sidebar.collapsed { width: 68px; }

.sidebar-logo {
  padding: 24px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  flex-shrink: 0;
}
.logo-text {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.logo-accent { color: #FF6B35; }
.logo-sub { font-size: 11px; font-weight: 400; color: rgba(255,255,255,0.5); letter-spacing: 1px; text-transform: uppercase; }

.collapse-btn {
  margin-top: auto;
  padding: 14px 20px;
  background: none;
  border: none;
  border-top: 1px solid rgba(255,255,255,0.1);
  cursor: pointer;
  color: rgba(255,255,255,0.5);
  display: flex;
  align-items: center;
  gap: 8px;
  transition: color 0.2s;
}
.collapse-btn:hover { color: #fff; }

/* Main */
.admin-main {
  margin-left: 260px;
  flex: 1;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.25s ease;
  min-width: 0;
}
.admin-main.main-collapsed { margin-left: 68px; }

.admin-topbar {
  background: #fff;
  padding: 14px 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #DEE2E6;
  position: sticky;
  top: 0; z-index: 10;
}
.page-title { font-size: 22px; font-weight: 600; color: #003366; }
.topbar-right { display: flex; align-items: center; gap: 16px; }
.topbar-avatar { cursor: pointer; }
.user-avatar { background: #003366; color: #fff; font-weight: 600; }

.admin-content { padding: 32px; flex: 1; }
</style>
