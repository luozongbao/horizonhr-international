<script setup lang="ts">
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<template>
  <div class="min-h-screen">
    <!-- Header placeholder -->
    <header class="h-18 bg-white border-b border-brand-border flex items-center px-8 justify-between sticky top-0 z-50">
      <div class="text-xl font-bold text-primary">
        Hubei <span class="text-accent">Horizon</span> HR
      </div>
      <nav class="flex gap-8">
        <router-link to="/" class="text-brand-text hover:text-primary font-medium">{{ t('nav.home') }}</router-link>
        <router-link to="/about" class="text-brand-text hover:text-primary font-medium">{{ t('nav.about') }}</router-link>
        <router-link to="/study" class="text-brand-text hover:text-primary font-medium">{{ t('nav.study') }}</router-link>
        <router-link to="/seminars" class="text-brand-text hover:text-primary font-medium">{{ t('nav.seminars') }}</router-link>
        <router-link to="/news" class="text-brand-text hover:text-primary font-medium">{{ t('nav.news') }}</router-link>
        <router-link to="/contact" class="text-brand-text hover:text-primary font-medium">{{ t('nav.contact') }}</router-link>
      </nav>
      <router-link to="/login">
        <el-button type="primary">{{ t('nav.login') }}</el-button>
      </router-link>
    </header>

    <!-- Hero -->
    <section class="bg-primary text-white py-24 text-center px-8">
      <h1 class="text-5xl font-bold mb-4">Hubei Horizon International</h1>
      <p class="text-xl opacity-80 mb-8">Connecting Southeast Asian talent with Chinese universities and enterprises</p>
      <div class="flex gap-4 justify-center">
        <router-link to="/study">
          <el-button size="large" style="background:#FF6B35;border-color:#FF6B35;color:white">Study in China</el-button>
        </router-link>
        <router-link to="/talent">
          <el-button size="large" plain style="color:white;border-color:rgba(255,255,255,0.5)">Talent Pool</el-button>
        </router-link>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-primary text-white text-center py-8 mt-auto">
      <div class="flex justify-center gap-6 mb-4">
        <a href="#" class="opacity-70 hover:opacity-100 text-sm">{{ t('footer.privacy') }}</a>
        <a href="#" class="opacity-70 hover:opacity-100 text-sm">{{ t('footer.terms') }}</a>
        <a href="#" class="opacity-70 hover:opacity-100 text-sm">{{ t('footer.sitemap') }}</a>
      </div>
      <p class="text-sm opacity-60">{{ t('footer.copyright') }}</p>
    </footer>
  </div>
</template>
