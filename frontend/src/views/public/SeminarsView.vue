<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'
import { usePageMeta } from '@/composables/usePageMeta'

const { t } = useI18n()

usePageMeta({
  title: t('seminar.pageTitle'),
  description: t('seminar.pageDesc'),
})

interface Speaker {
  name?: string
  title?: string
  photo_url?: string
}

interface Seminar {
  id: number
  title: string
  description?: string
  status: 'scheduled' | 'live' | 'ended'
  thumbnail_url?: string
  speaker?: Speaker
  speaker_name?: string
  start_time?: string
  duration_minutes?: number
  viewer_count?: number
  registered_count?: number
  language?: string
  has_recording?: boolean
}

interface PaginationMeta {
  current_page: number
  last_page: number
  total: number
  per_page: number
}

/* ─── State ──────────────────────────────────── */
const seminars = ref<Seminar[]>([])
const loading = ref(false)
const meta = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0, per_page: 9 })
const statusFilter = ref('')
const searchQuery = ref('')
const currentPage = ref(1)
const PER_PAGE = 9

const statusTabs = [
  { value: '', labelKey: 'seminar.allStatuses' },
  { value: 'scheduled', labelKey: 'seminar.status.scheduled' },
  { value: 'live', labelKey: 'seminar.status.live' },
  { value: 'ended', labelKey: 'seminar.status.ended' },
]

/* ─── Fetch ──────────────────────────────────── */
async function fetchSeminars() {
  loading.value = true
  try {
    const res = await publicApi.getSeminars({
      status: statusFilter.value || undefined,
      per_page: PER_PAGE,
      page: currentPage.value,
    })
    const data = res.data
    seminars.value = data?.data ?? []
    meta.value = data?.meta ?? { current_page: 1, last_page: 1, total: 0, per_page: PER_PAGE }
  } catch {
    seminars.value = []
  } finally {
    loading.value = false
  }
}

function resetAndFetch() {
  currentPage.value = 1
  fetchSeminars()
}

watch([statusFilter], resetAndFetch)

function handlePageChange(page: number) {
  currentPage.value = page
  fetchSeminars()
  document.getElementById('seminar-grid')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

/* ─── Helpers ────────────────────────────────── */
function formatDate(dateStr?: string): string {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function getSpeakerName(s: Seminar): string {
  return s.speaker?.name ?? s.speaker_name ?? ''
}

function getWatchRoute(s: Seminar): string {
  return `/seminars/${s.id}/watch`
}

onMounted(fetchSeminars)
</script>

<template>
  <div class="seminars-page">

    <!-- Hero -->
    <section class="page-hero">
      <div class="container">
        <h1 class="hero-title">{{ t('seminar.pageTitle') }}</h1>
        <nav class="breadcrumb">
          <router-link to="/">{{ t('nav.home') }}</router-link>
          <span class="sep">/</span>
          <span>{{ t('seminar.pageTitle') }}</span>
        </nav>
      </div>
    </section>

    <!-- Filters -->
    <section class="filter-section">
      <div class="container">
        <div class="filter-bar">
          <div class="status-tabs">
            <button
              v-for="tab in statusTabs"
              :key="tab.value"
              class="status-tab"
              :class="{ active: statusFilter === tab.value }"
              @click="statusFilter = tab.value"
            >
              {{ t(tab.labelKey) }}
            </button>
          </div>
          <el-input
            v-model="searchQuery"
            :placeholder="t('seminar.searchPlaceholder')"
            clearable
            class="filter-search"
            prefix-icon="Search"
            @change="resetAndFetch"
          />
        </div>
      </div>
    </section>

    <!-- Grid -->
    <section id="seminar-grid" class="section section-bg">
      <div class="container">
        <template v-if="loading">
          <div class="seminar-grid">
            <el-skeleton v-for="i in PER_PAGE" :key="i" style="height:320px" animated />
          </div>
        </template>
        <template v-else-if="seminars.length === 0">
          <el-empty :description="t('seminar.noSeminars')" />
        </template>
        <template v-else>
          <div class="seminar-grid">
            <article v-for="s in seminars" :key="s.id" class="seminar-card">
              <!-- Thumbnail -->
              <router-link :to="`/seminars/${s.id}`" class="card-thumb-link">
                <div class="card-thumb-wrap">
                  <img
                    v-if="s.thumbnail_url"
                    :src="s.thumbnail_url"
                    :alt="s.title"
                    class="card-thumb"
                  />
                  <div v-else class="card-thumb-placeholder">&#127897;</div>

                  <!-- Status badge -->
                  <span class="status-badge" :class="`status-${s.status}`">
                    <span v-if="s.status === 'live'" class="live-dot" />
                    {{ t(`seminar.status.${s.status}`) }}
                  </span>

                  <!-- Viewer count for live -->
                  <span v-if="s.status === 'live' && s.viewer_count" class="viewer-badge">
                    &#128064; {{ s.viewer_count }}
                  </span>
                </div>
              </router-link>

              <!-- Body -->
              <div class="card-body">
                <h3 class="card-title">
                  <router-link :to="`/seminars/${s.id}`">{{ s.title }}</router-link>
                </h3>
                <div v-if="getSpeakerName(s)" class="speaker-row">
                  <img
                    v-if="s.speaker?.photo_url"
                    :src="s.speaker.photo_url"
                    :alt="getSpeakerName(s)"
                    class="speaker-avatar"
                  />
                  <span class="speaker-name">{{ getSpeakerName(s) }}</span>
                  <span v-if="s.speaker?.title" class="speaker-title">· {{ s.speaker.title }}</span>
                </div>
                <p v-if="s.start_time" class="card-date">
                  &#128197; {{ formatDate(s.start_time) }}
                </p>
                <p v-if="s.duration_minutes" class="card-duration">
                  &#9200; {{ s.duration_minutes }} {{ t('seminar.minutes') }}
                </p>
              </div>

              <!-- Footer CTA -->
              <div class="card-footer">
                <router-link
                  v-if="s.status === 'live'"
                  :to="getWatchRoute(s)"
                  class="cta-btn cta-live"
                >
                  &#9654; {{ t('seminar.watchLive') }}
                </router-link>
                <router-link
                  v-else-if="s.status === 'ended' && s.has_recording"
                  :to="getWatchRoute(s)"
                  class="cta-btn cta-recording"
                >
                  &#9654; {{ t('seminar.watchRecording') }}
                </router-link>
                <router-link
                  v-else
                  :to="`/seminars/${s.id}`"
                  class="cta-btn cta-register"
                >
                  {{ t('seminar.register') }}
                </router-link>
              </div>
            </article>
          </div>

          <!-- Pagination -->
          <div v-if="meta.last_page > 1" class="pagination-wrap">
            <el-pagination
              :current-page="currentPage"
              :page-size="PER_PAGE"
              :total="meta.total"
              layout="prev, pager, next"
              @current-change="handlePageChange"
            />
          </div>
        </template>
      </div>
    </section>

  </div>
</template>

<style scoped>
.seminars-page {
  --c-primary: #003366;
  --c-secondary: #E6F0FF;
  --c-accent: #0066CC;
  --c-live: #e53e3e;
  --c-text: #1A1A2E;
  --c-muted: #6C757D;
  --c-border: #DEE2E6;
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 48px; }
.section { padding: 48px 0; }
.section-bg { background: #f8faff; }

/* Hero */
.page-hero {
  background: linear-gradient(135deg, var(--c-primary) 0%, #004080 100%);
  padding: 48px 0;
  color: #fff;
}
.hero-title { font-size: 40px; font-weight: 700; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.8; }
.breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.breadcrumb a:hover { color: #fff; }
.sep { opacity: 0.5; }

/* Filters */
.filter-section {
  background: #fff;
  border-bottom: 1px solid var(--c-border);
  padding: 16px 0;
}
.filter-bar { display: flex; gap: 16px; flex-wrap: wrap; align-items: center; }
.status-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
.status-tab {
  padding: 6px 18px;
  border: 1px solid var(--c-border);
  border-radius: 20px;
  background: #fff;
  color: var(--c-muted);
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}
.status-tab:hover { border-color: var(--c-accent); color: var(--c-accent); }
.status-tab.active { background: var(--c-primary); border-color: var(--c-primary); color: #fff; }
.filter-search { width: 240px; }

/* Grid */
.seminar-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; margin-bottom: 40px; }

.seminar-card {
  background: #fff;
  border: 1px solid var(--c-border);
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.2s, transform 0.2s;
}
.seminar-card:hover { box-shadow: 0 8px 24px rgba(0,51,102,0.12); transform: translateY(-2px); }

/* Thumb */
.card-thumb-link { display: block; }
.card-thumb-wrap { position: relative; }
.card-thumb { width: 100%; height: 185px; object-fit: cover; display: block; }
.card-thumb-placeholder {
  width: 100%;
  height: 185px;
  background: var(--c-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 52px;
}

/* Status badge */
.status-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 3px 10px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: flex;
  align-items: center;
  gap: 5px;
}
.status-scheduled { background: var(--c-primary); color: #fff; }
.status-live { background: var(--c-live); color: #fff; }
.status-ended { background: #555; color: #fff; }
.live-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #fff;
  animation: blink 1s infinite;
}
@keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0; } }
.viewer-badge {
  position: absolute;
  bottom: 8px;
  right: 8px;
  background: rgba(0,0,0,0.6);
  color: #fff;
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 10px;
}

/* Body */
.card-body { padding: 16px; flex: 1; }
.card-title { font-size: 16px; font-weight: 600; color: var(--c-text); margin-bottom: 10px; line-height: 1.4; }
.card-title a { color: inherit; text-decoration: none; }
.card-title a:hover { color: var(--c-accent); }
.speaker-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.speaker-avatar { width: 26px; height: 26px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.speaker-name { font-size: 13px; font-weight: 600; color: var(--c-text); }
.speaker-title { font-size: 12px; color: var(--c-muted); }
.card-date, .card-duration { font-size: 13px; color: var(--c-muted); margin-bottom: 4px; }

/* Footer */
.card-footer { padding: 12px 16px; border-top: 1px solid var(--c-border); }
.cta-btn {
  display: block;
  text-align: center;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: opacity 0.2s;
}
.cta-btn:hover { opacity: 0.85; }
.cta-live { background: var(--c-live); color: #fff; }
.cta-recording { background: #555; color: #fff; }
.cta-register { background: var(--c-primary); color: #fff; }

/* Pagination */
.pagination-wrap { display: flex; justify-content: center; padding-top: 16px; }

/* Responsive */
@media (max-width: 900px) {
  .seminar-grid { grid-template-columns: repeat(2, 1fr); }
  .container { padding: 0 24px; }
  .hero-title { font-size: 28px; }
}
@media (max-width: 600px) {
  .seminar-grid { grid-template-columns: 1fr; }
  .filter-search { width: 100%; }
}
</style>
