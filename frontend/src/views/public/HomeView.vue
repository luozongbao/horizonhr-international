<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'
import { usePageMeta } from '@/composables/usePageMeta'

const { t } = useI18n()

usePageMeta({
  title: t('nav.home'),
  description: t('home.heroSubtitle'),
})

interface Seminar {
  id: number
  title: string
  start_time: string
  speaker_name?: string
  thumbnail_url?: string
  target_audience?: string
}

interface Post {
  id: number
  title: string
  slug: string
  excerpt?: string
  published_at: string
  thumbnail_url?: string
  category?: string
}

interface University {
  id: number
  name: string
  logo_url?: string
  website?: string
}

interface Slide {
  headline: string
  sub_headline?: string
  cta_text?: string
  cta_url?: string
}

const seminars = ref<Seminar[]>([])
const posts = ref<Post[]>([])
const universities = ref<University[]>([])

const slides = ref<Slide[]>([
  {
    headline: t('home.heroTitle'),
    sub_headline: t('home.heroSubtitle'),
    cta_text: t('home.studyChina'),
    cta_url: '/study',
  },
  {
    headline: t('home.slide2Title'),
    sub_headline: t('home.slide2Desc'),
    cta_text: t('home.talentPool'),
    cta_url: '/talent',
  },
  {
    headline: t('home.slide3Title'),
    sub_headline: t('home.slide3Desc'),
    cta_text: t('nav.seminars'),
    cta_url: '/seminars',
  },
])

const loading = ref({ seminars: true, posts: true, universities: true })

const stats = [
  { value: '50+', key: 'universities' },
  { value: '10,000+', key: 'students' },
  { value: '5,000+', key: 'jobs' },
]

onMounted(async () => {
  try {
    const res = await publicApi.getHomePage()
    const data = res.data?.data
    if (data?.banners?.length) {
      slides.value = data.banners
    }
  } catch {
    // keep defaults
  }

  const [univRes, semRes, postRes] = await Promise.allSettled([
    publicApi.getUniversities({ per_page: 20 }),
    publicApi.getSeminars({ status: 'scheduled', per_page: 3 }),
    publicApi.getPosts({ per_page: 3 }),
  ])

  if (univRes.status === 'fulfilled') {
    universities.value = univRes.value.data?.data ?? []
  }
  loading.value.universities = false

  if (semRes.status === 'fulfilled') {
    seminars.value = semRes.value.data?.data ?? []
  }
  loading.value.seminars = false

  if (postRes.status === 'fulfilled') {
    posts.value = postRes.value.data?.data ?? []
  }
  loading.value.posts = false
})

function formatDate(dateStr: string) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <div class="home-page">

    <!-- 1. Hero Banner Slider -->
    <section class="hero-section">
      <el-carousel height="480px" :interval="5000" arrow="always">
        <el-carousel-item v-for="(slide, i) in slides" :key="i">
          <div class="hero-slide">
            <h1 class="hero-title" v-html="slide.headline.replace(/\n/g, '<br>')"></h1>
            <p v-if="slide.sub_headline" class="hero-subtitle">{{ slide.sub_headline }}</p>
            <div class="hero-btns">
              <router-link :to="slide.cta_url || '/study'">
                <el-button size="large" class="btn-accent">
                  {{ slide.cta_text || t('home.studyChina') }}
                </el-button>
              </router-link>
              <router-link to="/register/student">
                <el-button size="large" class="btn-outline-white">
                  {{ t('auth.registerStudent') }}
                </el-button>
              </router-link>
            </div>
          </div>
        </el-carousel-item>
      </el-carousel>
    </section>

    <!-- 2. Feature Cards -->
    <section class="section section-white">
      <div class="container">
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">&#127891;</div>
            <h3>{{ t('home.featureCards.study') }}</h3>
            <p>{{ t('home.featureCards.studyDesc') }}</p>
            <router-link to="/study" class="feature-link">{{ t('home.learnMore') }} &rarr;</router-link>
          </div>
          <div class="feature-card">
            <div class="feature-icon">&#128101;</div>
            <h3>{{ t('home.featureCards.talent') }}</h3>
            <p>{{ t('home.featureCards.talentDesc') }}</p>
            <router-link to="/talent" class="feature-link">{{ t('home.learnMore') }} &rarr;</router-link>
          </div>
          <div class="feature-card">
            <div class="feature-icon">&#129309;</div>
            <h3>{{ t('home.featureCards.corporate') }}</h3>
            <p>{{ t('home.featureCards.corporateDesc') }}</p>
            <router-link to="/corporate" class="feature-link">{{ t('home.learnMore') }} &rarr;</router-link>
          </div>
          <div class="feature-card">
            <div class="feature-icon">&#127909;</div>
            <h3>{{ t('home.featureCards.seminar') }}</h3>
            <p>{{ t('home.featureCards.seminarDesc') }}</p>
            <router-link to="/seminars" class="feature-link">{{ t('home.learnMore') }} &rarr;</router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. Statistics Bar -->
    <section class="stats-bar">
      <div class="container">
        <div class="stats-row">
          <div v-for="stat in stats" :key="stat.key" class="stat-item">
            <div class="stat-value">{{ stat.value }}</div>
            <div class="stat-label">{{ t(`home.stats.${stat.key}`) }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- 4. Partner University Logos -->
    <section class="partner-section">
      <div class="partner-header">
        <h4>{{ t('home.sections.partnerUniversities') }}</h4>
      </div>
      <div v-if="loading.universities" class="partner-loading">
        <el-skeleton :rows="1" animated />
      </div>
      <div v-else class="marquee-wrapper">
        <div class="marquee-track">
          <template v-if="universities.length">
            <a
              v-for="(u, idx) in [...universities, ...universities]"
              :key="`u-${idx}`"
              :href="u.website || '/study-in-china'"
              :target="u.website ? '_blank' : '_self'"
              rel="noopener noreferrer"
              class="university-logo"
            >
              <img v-if="u.logo_url" :src="u.logo_url" :alt="u.name" class="logo-img" />
              <span v-else class="logo-placeholder">{{ u.name }}</span>
            </a>
          </template>
          <template v-else>
            <div v-for="n in 10" :key="n" class="university-logo logo-placeholder-wrap">
              <span class="logo-icon">&#127963;</span>
            </div>
          </template>
        </div>
      </div>
    </section>

    <!-- 5. Why Choose Us -->
    <section class="section section-secondary">
      <div class="container">
        <h2 class="section-title">{{ t('home.sections.whyChooseUs') }}</h2>
        <div class="advantages-grid">
          <div class="advantage-card">
            <div class="adv-icon">&#127757;</div>
            <h4>{{ t('home.advantages.channels') }}</h4>
            <p>{{ t('home.advantages.channelsDesc') }}</p>
          </div>
          <div class="advantage-card">
            <div class="adv-icon">&#127963;</div>
            <h4>{{ t('home.advantages.partners') }}</h4>
            <p>{{ t('home.advantages.partnersDesc') }}</p>
          </div>
          <div class="advantage-card">
            <div class="adv-icon">&#128202;</div>
            <h4>{{ t('home.advantages.recruitment') }}</h4>
            <p>{{ t('home.advantages.recruitmentDesc') }}</p>
          </div>
          <div class="advantage-card">
            <div class="adv-icon">&#128260;</div>
            <h4>{{ t('home.advantages.service') }}</h4>
            <p>{{ t('home.advantages.serviceDesc') }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 6. Upcoming Seminars -->
    <section class="section section-white">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title left">{{ t('home.sections.upcomingSeminars') }}</h2>
          <router-link to="/seminars" class="view-all-link">{{ t('home.viewAllSeminars') }} &rarr;</router-link>
        </div>
        <div v-if="loading.seminars" class="seminars-grid">
          <el-skeleton v-for="n in 3" :key="n" :rows="4" animated class="skeleton-card" />
        </div>
        <div v-else-if="seminars.length" class="seminars-grid">
          <div v-for="sem in seminars" :key="sem.id" class="seminar-card">
            <div
              class="seminar-thumb"
              :style="sem.thumbnail_url ? `background-image:url(${sem.thumbnail_url})` : ''"
            >
              <span v-if="!sem.thumbnail_url" class="thumb-icon">&#127891;</span>
            </div>
            <div class="seminar-body">
              <div class="seminar-date">{{ formatDate(sem.start_time) }}</div>
              <h3 class="seminar-title">{{ sem.title }}</h3>
              <p v-if="sem.speaker_name" class="seminar-speaker">{{ sem.speaker_name }}</p>
              <span v-if="sem.target_audience" class="seminar-tag">{{ sem.target_audience }}</span>
              <div>
                <router-link :to="`/seminars/${sem.id}`">
                  <el-button type="primary" size="small" class="seminar-btn">{{ t('home.register') }}</el-button>
                </router-link>
              </div>
            </div>
          </div>
        </div>
        <el-empty v-else :description="t('common.noData')" />
      </div>
    </section>

    <!-- 7. Latest News -->
    <section class="section section-light">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title left">{{ t('home.sections.latestNews') }}</h2>
          <router-link to="/news" class="view-all-link">{{ t('home.viewAllNews') }} &rarr;</router-link>
        </div>
        <div v-if="loading.posts" class="news-grid">
          <el-skeleton v-for="n in 3" :key="n" :rows="4" animated class="skeleton-card" />
        </div>
        <div v-else-if="posts.length" class="news-grid">
          <div v-for="post in posts" :key="post.id" class="news-card">
            <div
              class="news-thumb"
              :style="post.thumbnail_url ? `background-image:url(${post.thumbnail_url})` : ''"
            >
              <span v-if="!post.thumbnail_url" class="news-thumb-icon">&#128240;</span>
            </div>
            <div class="news-body">
              <span v-if="post.category" class="news-tag">{{ post.category }}</span>
              <h3 class="news-title">{{ post.title }}</h3>
              <p v-if="post.excerpt" class="news-excerpt">{{ post.excerpt }}</p>
              <div class="news-footer">
                <span class="news-date">{{ formatDate(post.published_at) }}</span>
                <router-link :to="`/news/${post.slug}`" class="news-read-more">
                  {{ t('home.readMore') }} &rarr;
                </router-link>
              </div>
            </div>
          </div>
        </div>
        <el-empty v-else :description="t('common.noData')" />
      </div>
    </section>

    <!-- 8. CTA Banner -->
    <section class="cta-section">
      <div class="container cta-inner">
        <h2 class="cta-title">{{ t('home.cta.title') }}</h2>
        <div class="cta-btns">
          <router-link to="/register/student">
            <el-button size="large" class="btn-accent">{{ t('home.cta.registerStudent') }}</el-button>
          </router-link>
          <router-link to="/corporate">
            <el-button size="large" class="btn-outline-white">{{ t('home.cta.corporate') }}</el-button>
          </router-link>
        </div>
      </div>
    </section>

  </div>
</template>

<style scoped>
.home-page {
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
.section-secondary { background: var(--c-secondary); }
.section-light { background: #F5F7FA; }

.section-title {
  font-size: 36px;
  font-weight: 600;
  color: var(--c-primary);
  text-align: center;
  margin-bottom: 48px;
}
.section-title.left { text-align: left; margin-bottom: 0; }

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 32px;
}
.view-all-link { color: var(--c-accent); font-weight: 500; text-decoration: none; font-size: 15px; white-space: nowrap; }
.view-all-link:hover { text-decoration: underline; }

.btn-accent {
  background: var(--c-accent) !important;
  border-color: var(--c-accent) !important;
  color: #fff !important;
  font-size: 15px !important;
  padding: 12px 28px !important;
  height: auto !important;
}
.btn-outline-white {
  background: transparent !important;
  border-color: rgba(255, 255, 255, 0.6) !important;
  color: #fff !important;
  font-size: 15px !important;
  padding: 12px 28px !important;
  height: auto !important;
}
.btn-outline-white:hover { background: rgba(255, 255, 255, 0.1) !important; }

/* Hero */
.hero-section :deep(.el-carousel__container) { height: 480px; }
.hero-section :deep(.el-carousel__button) { background: rgba(255, 255, 255, 0.5); }
.hero-section :deep(.el-carousel__button.is-active) { background: #fff; }
.hero-slide {
  height: 480px;
  background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-light) 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #fff;
  text-align: center;
  padding: 0 48px;
}
.hero-title { font-size: 44px; font-weight: 700; margin-bottom: 20px; line-height: 1.25; }
.hero-subtitle { font-size: 18px; opacity: 0.85; margin-bottom: 32px; max-width: 600px; }
.hero-btns { display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; }

/* Feature cards */
.features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.feature-card {
  background: #fff;
  border: 1px solid var(--c-border);
  border-radius: 8px;
  padding: 32px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 51, 102, 0.06);
  transition: transform 0.25s, box-shadow 0.25s;
}
.feature-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 51, 102, 0.12); }
.feature-icon { font-size: 48px; margin-bottom: 16px; }
.feature-card h3 { font-size: 20px; font-weight: 600; color: var(--c-primary); margin-bottom: 8px; }
.feature-card p { font-size: 14px; color: var(--c-muted); margin-bottom: 16px; }
.feature-link { color: var(--c-accent); font-weight: 500; text-decoration: none; font-size: 14px; }
.feature-link:hover { text-decoration: underline; }

/* Stats */
.stats-bar { background: var(--c-primary); padding: 40px 0; }
.stats-row { display: flex; justify-content: center; gap: 80px; }
.stat-item { text-align: center; color: #fff; }
.stat-value { font-size: 42px; font-weight: 700; line-height: 1; margin-bottom: 8px; }
.stat-label { font-size: 16px; opacity: 0.8; }

/* Partner logos */
.partner-section { background: var(--c-secondary); padding: 32px 0; overflow: hidden; }
.partner-header { text-align: center; margin-bottom: 20px; padding: 0 48px; }
.partner-header h4 { font-size: 13px; font-weight: 600; color: var(--c-muted); text-transform: uppercase; letter-spacing: 0.08em; }
.partner-loading { padding: 0 48px; }
.marquee-wrapper { overflow: hidden; width: 100%; }
.marquee-track {
  display: flex;
  gap: 20px;
  animation: marquee 30s linear infinite;
  width: max-content;
  padding: 4px 0;
}
.marquee-track:hover { animation-play-state: paused; }
@keyframes marquee {
  0%   { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
.university-logo {
  width: 100px;
  height: 76px;
  background: #fff;
  border-radius: 8px;
  border: 1px solid var(--c-border);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  text-decoration: none;
  overflow: hidden;
}
.logo-img { width: 100%; height: 100%; object-fit: contain; padding: 8px; }
.logo-placeholder { font-size: 10px; color: var(--c-muted); text-align: center; padding: 4px; line-height: 1.3; }
.logo-placeholder-wrap { cursor: default; }
.logo-icon { font-size: 32px; }

/* Advantages */
.advantages-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.advantage-card { text-align: center; padding: 32px 24px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 51, 102, 0.06); }
.adv-icon {
  width: 80px; height: 80px;
  background: var(--c-secondary);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 36px;
  margin: 0 auto 20px;
}
.advantage-card h4 { font-size: 18px; font-weight: 600; color: var(--c-primary); margin-bottom: 8px; }
.advantage-card p { font-size: 14px; color: var(--c-muted); }

/* Seminars */
.seminars-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.seminar-card { background: #fff; border: 1px solid var(--c-border); border-radius: 8px; overflow: hidden; transition: box-shadow 0.2s; }
.seminar-card:hover { box-shadow: 0 4px 16px rgba(0, 51, 102, 0.1); }
.seminar-thumb {
  height: 160px;
  background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-secondary) 100%);
  background-size: cover;
  background-position: center;
  display: flex; align-items: center; justify-content: center;
}
.thumb-icon { font-size: 48px; }
.seminar-body { padding: 20px; }
.seminar-date { font-size: 13px; font-weight: 600; color: var(--c-primary); margin-bottom: 6px; }
.seminar-title { font-size: 17px; font-weight: 600; color: var(--c-text); margin-bottom: 6px; line-height: 1.4; }
.seminar-speaker { font-size: 13px; color: var(--c-muted); margin-bottom: 8px; }
.seminar-tag { display: inline-block; padding: 3px 10px; background: var(--c-secondary); color: var(--c-primary); border-radius: 12px; font-size: 12px; margin-bottom: 12px; }
.seminar-btn { margin-top: 4px; }

/* News */
.news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.news-card { background: #fff; border: 1px solid var(--c-border); border-radius: 8px; overflow: hidden; transition: box-shadow 0.2s; }
.news-card:hover { box-shadow: 0 4px 16px rgba(0, 51, 102, 0.1); }
.news-thumb {
  height: 200px;
  background: linear-gradient(135deg, var(--c-secondary) 0%, var(--c-border) 100%);
  background-size: cover;
  background-position: center;
  display: flex; align-items: center; justify-content: center;
}
.news-thumb-icon { font-size: 48px; }
.news-body { padding: 20px; }
.news-tag { display: inline-block; padding: 3px 10px; background: var(--c-primary); color: #fff; border-radius: 12px; font-size: 12px; margin-bottom: 10px; }
.news-title { font-size: 17px; font-weight: 600; color: var(--c-text); margin-bottom: 8px; line-height: 1.4; }
.news-excerpt {
  font-size: 14px; color: var(--c-muted); margin-bottom: 12px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.news-footer { display: flex; justify-content: space-between; align-items: center; }
.news-date { font-size: 13px; color: var(--c-muted); }
.news-read-more { font-size: 13px; font-weight: 500; color: var(--c-accent); text-decoration: none; }
.news-read-more:hover { text-decoration: underline; }

/* CTA */
.cta-section { background: var(--c-primary); padding: 64px 0; }
.cta-inner { text-align: center; }
.cta-title { font-size: 36px; font-weight: 600; color: #fff; margin-bottom: 32px; }
.cta-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }

/* Skeleton */
.skeleton-card { background: #fff; border: 1px solid var(--c-border); border-radius: 8px; padding: 20px; }

/* Responsive */
@media (max-width: 1024px) {
  .features-grid, .advantages-grid { grid-template-columns: repeat(2, 1fr); }
  .seminars-grid, .news-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .container { padding: 0 24px; }
  .section { padding: 40px 0; }
  .hero-title { font-size: 28px; }
  .hero-subtitle { font-size: 15px; }
  .hero-slide { padding: 0 24px; }
  .features-grid, .advantages-grid, .seminars-grid, .news-grid { grid-template-columns: 1fr; }
  .stats-row { gap: 32px; flex-wrap: wrap; }
  .stat-value { font-size: 32px; }
  .section-title { font-size: 26px; }
  .section-header { flex-direction: column; align-items: flex-start; gap: 8px; }
  .cta-title { font-size: 24px; }
}
</style>
