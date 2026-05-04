<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export interface NavItem {
  icon: string   // Element Plus icon name (e.g. 'House', 'User')
  label: string
  route: string
  badge?: number | string
}

const props = defineProps<{
  navItems: NavItem[]
  collapsed?: boolean
  dark?: boolean     // true for admin (dark sidebar), false for student/enterprise
}>()

const route  = useRoute()
const router = useRouter()

function isActive(navRoute: string) {
  return route.path === navRoute || route.path.startsWith(navRoute + '/')
}
</script>

<template>
  <nav class="sidebar-nav">
    <a
      v-for="item in navItems"
      :key="item.route"
      class="nav-item"
      :class="[
        isActive(item.route) ? 'active' : '',
        dark ? 'dark' : 'light',
      ]"
      href="#"
      @click.prevent="router.push(item.route)"
    >
      <el-icon size="20">
        <component :is="item.icon" />
      </el-icon>
      <span v-if="!collapsed" class="nav-label">{{ item.label }}</span>
      <span v-if="item.badge && !collapsed" class="nav-badge">{{ item.badge }}</span>
    </a>
  </nav>
</template>

<style scoped>
.sidebar-nav {
  padding: 16px 0;
}

/* Light sidebar (student / enterprise) */
.nav-item.light {
  display: flex;
  align-items: center;
  padding: 12px 24px;
  gap: 12px;
  color: #6C757D;
  text-decoration: none;
  cursor: pointer;
  border-left: 3px solid transparent;
  transition: all 0.2s;
}
.nav-item.light:hover,
.nav-item.light.active {
  background: #E6F0FF;
  color: #003366;
  border-left-color: #003366;
}
.nav-item.light.active {
  font-weight: 500;
}

/* Dark sidebar (admin) */
.nav-item.dark {
  display: flex;
  align-items: center;
  padding: 14px 24px;
  gap: 12px;
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  cursor: pointer;
  border-left: 3px solid transparent;
  transition: all 0.2s;
}
.nav-item.dark:hover,
.nav-item.dark.active {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border-left-color: #FF6B35;
}

.nav-label {
  flex: 1;
  font-size: 14px;
  white-space: nowrap;
}

.nav-badge {
  margin-left: auto;
  background: #FF6B35;
  color: #fff;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 11px;
}
</style>
