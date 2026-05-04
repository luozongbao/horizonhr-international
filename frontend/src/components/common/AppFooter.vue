<script setup lang="ts">
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
  <footer class="app-footer">
    <div class="footer-inner">
      <div class="footer-brand">
        <p class="brand-name">Hubei <span class="brand-accent">Horizon</span> HR</p>
        <p class="brand-tagline">Connecting Southeast Asian talent with Chinese universities & enterprises</p>
      </div>

      <nav class="footer-links">
        <router-link to="/about">{{ t('nav.about') }}</router-link>
        <router-link to="/study-in-china">{{ t('nav.study') }}</router-link>
        <router-link to="/talent">{{ t('nav.talent') }}</router-link>
        <router-link to="/seminars">{{ t('nav.seminars') }}</router-link>
        <router-link to="/contact">{{ t('nav.contact') }}</router-link>
        <router-link to="/pages/privacy-policy">{{ t('footer.privacy') }}</router-link>
        <router-link to="/pages/terms">{{ t('footer.terms') }}</router-link>
      </nav>

      <p class="footer-copy">{{ t('footer.copyright') }}</p>
    </div>
  </footer>
</template>

<style scoped>
.app-footer {
  background: #003366;
  color: rgba(255, 255, 255, 0.85);
  padding: 40px 48px 24px;
}
.footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
  text-align: center;
}
.brand-name {
  font-size: 20px;
  font-weight: 700;
  color: #fff;
}
.brand-accent {
  color: #FF6B35;
}
.brand-tagline {
  font-size: 13px;
  opacity: 0.75;
  margin-top: 4px;
}
.footer-links {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 8px 24px;
}
.footer-links a {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  font-size: 14px;
  transition: color 0.2s;
}
.footer-links a:hover {
  color: #fff;
}
.footer-copy {
  font-size: 12px;
  opacity: 0.6;
}
</style>
