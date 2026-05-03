<script setup lang="ts">
import { useLanguageStore, type SupportedLocale } from '@/stores/language'

const languageStore = useLanguageStore()

function select(lang: SupportedLocale) {
  languageStore.switchLanguage(lang)
}
</script>

<template>
  <el-dropdown trigger="click" @command="select">
    <button class="lang-trigger">
      <el-icon><Globe /></el-icon>
      <span class="lang-label">{{ languageStore.currentLocaleLabel }}</span>
      <el-icon size="12"><ArrowDown /></el-icon>
    </button>
    <template #dropdown>
      <el-dropdown-menu>
        <el-dropdown-item
          v-for="loc in languageStore.SUPPORTED_LOCALES"
          :key="loc.code"
          :command="loc.code"
          :class="{ 'is-active': languageStore.locale === loc.code }"
        >
          {{ loc.nativeLabel }}
        </el-dropdown-item>
      </el-dropdown-menu>
    </template>
  </el-dropdown>
</template>

<style scoped>
.lang-trigger {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  border: 1px solid #DEE2E6;
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  color: #1A1A2E;
  transition: all 0.2s;
}
.lang-trigger:hover {
  border-color: #003366;
  color: #003366;
}
.lang-label {
  max-width: 80px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
