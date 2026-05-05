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
  { icon: 'House',          label: t('enterprise.dashboard.pageTitle'),  route: '/enterprise/dashboard' },
  { icon: 'OfficeBuilding', label: t('enterprise.profile.title'),        route: '/enterprise/profile' },
  { icon: 'Briefcase',      label: t('enterprise.jobs.pageTitle'),       route: '/enterprise/jobs' },
  { icon: 'UserFilled',     label: t('enterprise.talent.pageTitle'),     route: '/enterprise/talent' },
  { icon: 'VideoCamera',    label: t('enterprise.interviews.pageTitle'), route: '/enterprise/interviews' },
])

const pageTitle = computed(() => {
  const match = navItems.value.find(
    (n) => route.path === n.route || route.path.startsWith(n.route + '/'),
  )
  return match?.label ?? 'Enterprise Portal'
})

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="portal-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed }">
      <div class="sidebar-logo">
        <router-link to="/" class="logo-text">
          Hubei <span class="logo-accent">Horizon</span>
          <span v-if="!collapsed" class="logo-sub">Enterprise Portal</span>
        </router-link>
      </div>

      <div v-if="!collapsed" class="sidebar-user">
        <el-avatar :size="44" class="user-avatar">
          {{ auth.user?.name?.charAt(0).toUpperCase() ?? 'E' }}
        </el-avatar>
        <div class="user-info">
          <p class="user-name">{{ auth.user?.name }}</p>
          <p class="user-role">Enterprise</p>
        </div>
      </div>

      <SidebarNav :nav-items="navItems" :collapsed="collapsed" :dark="false" />

      <button class="collapse-btn" @click="collapsed = !collapsed">
        <el-icon><DArrowLeft v-if="!collapsed" /><DArrowRight v-else /></el-icon>
      </button>
    </aside>

    <!-- Main -->
    <div class="portal-main" :class="{ 'main-collapsed': collapsed }">
      <header class="portal-topbar">
        <h1 class="page-title">{{ pageTitle }}</h1>
        <div class="topbar-right">
          <LanguageSwitcher />
          <el-dropdown trigger="click" @command="cmd => cmd === 'logout' && logout()">
            <div class="topbar-avatar">
              <el-avatar :size="36" class="user-avatar">
                {{ auth.user?.name?.charAt(0).toUpperCase() ?? 'E' }}
              </el-avatar>
            </div>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="logout">
                  <el-icon><SwitchButton /></el-icon> Logout
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </header>

      <main class="portal-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.portal-layout {
  display: flex;
  min-height: 100vh;
  background: #F5F7FA;
}
.sidebar {
  width: 260px;
  background: #fff;
  position: fixed;
  top: 0; left: 0;
  height: 100vh;
  overflow-y: auto;
  border-right: 1px solid #DEE2E6;
  display: flex;
  flex-direction: column;
  transition: width 0.25s ease;
  z-index: 50;
}
.sidebar.collapsed { width: 68px; }
.sidebar-logo {
  padding: 20px 20px 16px;
  border-bottom: 1px solid #DEE2E6;
  flex-shrink: 0;
}
.logo-text {
  font-size: 17px;
  font-weight: 700;
  color: #003366;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.logo-accent { color: #FF6B35; }
.logo-sub { font-size: 11px; font-weight: 400; color: #6C757D; }
.sidebar-user {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  border-bottom: 1px solid #DEE2E6;
  flex-shrink: 0;
}
.user-avatar { background: #003366; color: #fff; font-weight: 600; flex-shrink: 0; }
.user-name { font-size: 14px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.user-role { font-size: 11px; color: #6C757D; }
.collapse-btn {
  margin-top: auto;
  padding: 12px 20px;
  background: none;
  border: none;
  border-top: 1px solid #DEE2E6;
  cursor: pointer;
  color: #6C757D;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: color 0.2s;
}
.collapse-btn:hover { color: #003366; }
.portal-main {
  margin-left: 260px;
  flex: 1;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.25s ease;
  min-width: 0;
}
.portal-main.main-collapsed { margin-left: 68px; }
.portal-topbar {
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
.portal-content { padding: 32px; flex: 1; }
</style>
