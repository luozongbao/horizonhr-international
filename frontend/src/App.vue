<script setup lang="ts">
import { onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'

const settings = useSettingsStore()

onMounted(() => {
  settings.fetchPublicConfig()
})
</script>

<template>
  <router-view />
</template>

