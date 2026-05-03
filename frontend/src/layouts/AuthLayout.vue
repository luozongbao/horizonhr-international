<script setup lang="ts">
// AuthLayout — minimal wrapper for auth pages.
// Each auth page provides its own two-panel or centered-card layout.
</script>

<template>
  <div class="auth-shell">
    <slot />
  </div>
</template>

<style scoped>
.auth-shell {
  min-height: 100vh;
}
</style>

