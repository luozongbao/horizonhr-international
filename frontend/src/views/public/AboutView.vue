<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'
import { useSanitize } from '@/composables/useSanitize'

const { t } = useI18n()
const { sanitize } = useSanitize()

interface CmsPage {
  title?: string
  content?: string
  meta?: Record<string, unknown>
}

interface University {
  id: number
  name: string
  logo_url?: string
  website?: string
}

const page = ref<CmsPage | null>(null)
const universities = ref<University[]>([])
const loading = ref(true)

onMounted(async () => {
  const [pageRes, univRes] = await Promise.allSettled([
    publicApi.getAboutPage(),
    publicApi.getUniversities({ per_page: 20 }),
  ])

  if (pageRes.status === 'fulfilled') {
    page.value = pageRes.value.data?.data ?? null
  }
  if (univRes.status === 'fulfilled') {
    universities.value = univRes.value.data?.data ?? []
  }
  loading.value = false
})

function getMeta(key: string, fallback: string): string {
  const val = page.value?.meta?.[key]
  return typeof val === 'string' ? val : fallback
}
</script>

<template>
  <div class="about-page">

    <!-- Page Hero -->
    <section class="page-hero">
      <div class="container">
        <h1 class="hero-title">{{ t('about.pageTitle') }}</h1>
        <nav class="breadcrumb">
          <router-link to="/">{{ t('nav.home') }}</router-link>
          <span class="sep">/</span>
          <span>{{ t('about.pageTitle') }}</span>
        </nav>
      </div>
    </section>

    <div v-if="loading" class="loading-container">
      <el-skeleton :rows="8" animated />
    </div>

    <template v-else>

      <!-- Mission & Vision -->
      <section class="section section-white">
        <div class="container">
          <div class="mission-grid">
            <div class="mission-card">
              <div class="mission-icon">&#127919;</div>
              <h2>{{ t('about.mission') }}</h2>
              <p>{{ getMeta('mission', t('about.defaultMission')) }}</p>
            </div>
            <div class="mission-card vision-card">
              <div class="mission-icon">&#127944;</div>
              <h2>{{ t('about.vision') }}</h2>
              <p>{{ getMeta('vision', t('about.defaultVision')) }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Our Story -->
      <section class="section section-light">
        <div class="container">
          <div class="story-layout">
            <div class="story-image-wrap">
              <div class="story-image-placeholder">
                <span class="story-img-icon">&#127968;</span>
              </div>
            </div>
            <div class="story-content">
              <h2 class="story-heading">{{ t('about.ourStory') }}</h2>
              <div
                v-if="page?.content"
                class="rich-content"
                v-html="sanitize(page.content)"
              ></div>
              <p v-else class="story-text">{{ t('about.defaultStory') }}</p>
              <div class="story-tags">
                <span class="tag">Professional</span>
                <span class="tag">International</span>
                <span class="tag">Reliable</span>
                <span class="tag">Closed-loop</span>
              </div>
              <div class="story-actions">
                <router-link to="/contact">
                  <el-button type="primary">{{ t('about.contactUs') }}</el-button>
                </router-link>
                <router-link to="/register/student">
                  <el-button>{{ t('about.joinUs') }}</el-button>
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Partner Universities (logos strip) -->
      <section v-if="universities.length" class="section section-secondary">
        <div class="container">
          <h2 class="section-title">{{ t('about.partners') }}</h2>
          <div class="logos-grid">
            <a
              v-for="u in universities"
              :key="u.id"
              :href="u.website || '/study-in-china'"
              :target="u.website ? '_blank' : '_self'"
              rel="noopener noreferrer"
              class="logo-item"
              :title="u.name"
            >
              <img v-if="u.logo_url" :src="u.logo_url" :alt="u.name" class="logo-img" />
              <span v-else class="logo-name">{{ u.name }}</span>
            </a>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="cta-section">
        <div class="container cta-inner">
          <h2 class="cta-title">{{ t('home.cta.title') }}</h2>
          <div class="cta-btns">
            <router-link to="/register/student">
              <el-button size="large" class="btn-accent">{{ t('home.cta.registerStudent') }}</el-button>
            </router-link>
            <router-link to="/contact">
              <el-button size="large" class="btn-outline-white">{{ t('about.contactUs') }}</el-button>
            </router-link>
          </div>
        </div>
      </section>

    </template>
  </div>
</template>

<style scoped>
.about-page {
  --c-primary: #003366;
  --c-primary-light: #004080;
  --c-secondary: #E6F0FF;
  --c-accent: #FF6B35;
  --c-text: #1A1A2E;
  --c-muted: #6C757D;
  --c-border: #DEE2E6;
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 48px; }
.section { padding: 64px 0; }
.section-white { background: #fff; }
.section-light { background: #F5F7FA; }
.section-secondary { background: var(--c-secondary); }

/* Hero */
.page-hero {
  background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-light) 100%);
  padding: 48px 0;
  color: #fff;
}
.hero-title { font-size: 40px; font-weight: 700; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.8; }
.breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.breadcrumb a:hover { color: #fff; }
.sep { opacity: 0.5; }

.loading-container { max-width: 1200px; margin: 48px auto; padding: 0 48px; }

/* Mission & Vision */
.mission-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
}
.mission-card {
  background: #fff;
  border: 1px solid var(--c-border);
  border-radius: 8px;
  padding: 40px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,51,102,0.06);
}
.vision-card { border-top: 4px solid var(--c-accent); }
.mission-icon { font-size: 48px; margin-bottom: 16px; }
.mission-card h2 { font-size: 24px; font-weight: 600; color: var(--c-primary); margin-bottom: 16px; }
.mission-card p { font-size: 16px; color: var(--c-muted); line-height: 1.8; }

/* Story */
.story-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  align-items: center;
}
.story-image-placeholder {
  background: var(--c-secondary);
  border-radius: 12px;
  height: 360px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.story-img-icon { font-size: 80px; }
.story-heading { font-size: 32px; font-weight: 600; color: var(--c-primary); margin-bottom: 20px; }
.story-text { font-size: 16px; color: var(--c-muted); line-height: 1.8; margin-bottom: 24px; }
.rich-content { font-size: 16px; color: var(--c-text); line-height: 1.8; margin-bottom: 24px; }
.rich-content :deep(p) { margin-bottom: 12px; }
.rich-content :deep(h2),
.rich-content :deep(h3) { color: var(--c-primary); margin: 20px 0 12px; }

.story-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; }
.tag { padding: 6px 14px; background: var(--c-secondary); color: var(--c-primary); border-radius: 20px; font-size: 13px; font-weight: 500; }

.story-actions { display: flex; gap: 12px; flex-wrap: wrap; }

/* Partner logos */
.section-title { font-size: 32px; font-weight: 600; color: var(--c-primary); text-align: center; margin-bottom: 40px; }
.logos-grid { display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; }
.logo-item {
  width: 120px;
  height: 80px;
  background: #fff;
  border-radius: 8px;
  border: 1px solid var(--c-border);
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  overflow: hidden;
  transition: box-shadow 0.2s;
}
.logo-item:hover { box-shadow: 0 4px 12px rgba(0,51,102,0.12); }
.logo-img { width: 100%; height: 100%; object-fit: contain; padding: 8px; }
.logo-name { font-size: 11px; color: var(--c-muted); text-align: center; padding: 6px; line-height: 1.3; }

/* CTA */
.cta-section { background: var(--c-primary); padding: 64px 0; }
.cta-inner { text-align: center; }
.cta-title { font-size: 36px; font-weight: 600; color: #fff; margin-bottom: 32px; }
.cta-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
.btn-accent { background: var(--c-accent) !important; border-color: var(--c-accent) !important; color: #fff !important; font-size: 15px !important; padding: 12px 28px !important; height: auto !important; }
.btn-outline-white { background: transparent !important; border-color: rgba(255,255,255,0.6) !important; color: #fff !important; font-size: 15px !important; padding: 12px 28px !important; height: auto !important; }
.btn-outline-white:hover { background: rgba(255,255,255,0.1) !important; }

/* Responsive */
@media (max-width: 1024px) {
  .mission-grid { grid-template-columns: 1fr; }
  .story-layout { grid-template-columns: 1fr; }
  .story-image-placeholder { height: 240px; }
}
@media (max-width: 768px) {
  .container { padding: 0 24px; }
  .section { padding: 40px 0; }
  .hero-title { font-size: 28px; }
  .story-heading { font-size: 24px; }
  .cta-title { font-size: 24px; }
}
</style>
