<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'
import { useAuthStore } from '@/stores/auth'
import { useSanitize } from '@/composables/useSanitize'
import { usePageMeta } from '@/composables/usePageMeta'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const { sanitize } = useSanitize()

/* ─── Types ──────────────────────────────────── */
interface Speaker {
  name?: string
  title?: string
  bio?: string
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
  is_registered?: boolean
}

/* ─── State ──────────────────────────────────── */
const seminar = ref<Seminar | null>(null)
const loading = ref(true)
const error = ref(false)
const isRegistered = ref(false)
const registering = ref(false)
const actionMsg = ref('')
const actionError = ref('')

usePageMeta({
  title: computed(() => seminar.value?.title ?? t('seminar.pageTitle')),
  description: computed(() => seminar.value?.description),
  image: computed(() => seminar.value?.thumbnail_url),
})

/* ─── Fetch ──────────────────────────────────── */
async function fetchSeminar() {
  loading.value = true
  error.value = false
  const id = Number(route.params.id)

  try {
    const res = await publicApi.getSeminar(id)
    seminar.value = res.data?.data ?? null
    isRegistered.value = seminar.value?.is_registered ?? false
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}

/* ─── Registration ───────────────────────────── */
async function register() {
  if (!auth.isLoggedIn) {
    router.push({ path: '/login', query: { redirect: route.fullPath } })
    return
  }
  registering.value = true
  actionMsg.value = ''
  actionError.value = ''
  try {
    await publicApi.registerSeminar(seminar.value!.id)
    isRegistered.value = true
    if (seminar.value) seminar.value.registered_count = (seminar.value.registered_count ?? 0) + 1
    actionMsg.value = t('seminar.registrationSuccess')
  } catch {
    actionError.value = t('common.error')
  } finally {
    registering.value = false
  }
}

async function cancelRegistration() {
  registering.value = true
  actionMsg.value = ''
  actionError.value = ''
  try {
    await publicApi.unregisterSeminar(seminar.value!.id)
    isRegistered.value = false
    if (seminar.value && seminar.value.registered_count)
      seminar.value.registered_count -= 1
    actionMsg.value = t('seminar.registrationCancelled')
  } catch {
    actionError.value = t('common.error')
  } finally {
    registering.value = false
  }
}

/* ─── Helpers ────────────────────────────────── */
function formatDate(dateStr?: string): string {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function getSpeakerName(s: Seminar): string {
  return s.speaker?.name ?? s.speaker_name ?? ''
}

onMounted(fetchSeminar)
</script>

<template>
  <div class="seminar-detail-page">

    <!-- Loading -->
    <section v-if="loading" class="section">
      <div class="container"><el-skeleton :rows="10" animated /></div>
    </section>

    <!-- Error -->
    <section v-else-if="error || !seminar" class="section">
      <div class="container">
        <el-empty :description="t('seminar.noSeminars')" />
        <div class="back-link-wrap">
          <router-link to="/seminars" class="back-link">← {{ t('seminar.backToSeminars') }}</router-link>
        </div>
      </div>
    </section>

    <template v-else>
      <!-- Hero / Thumbnail -->
      <section
        class="seminar-hero"
        :style="seminar.thumbnail_url ? `background-image:url('${seminar.thumbnail_url}')` : ''"
      >
        <div class="hero-overlay">
          <div class="container">
            <nav class="breadcrumb">
              <router-link to="/">{{ t('nav.home') }}</router-link>
              <span class="sep">/</span>
              <router-link to="/seminars">{{ t('seminar.pageTitle') }}</router-link>
              <span class="sep">/</span>
              <span class="breadcrumb-title">{{ seminar.title }}</span>
            </nav>
            <div class="hero-status-row">
              <span class="status-badge" :class="`status-${seminar.status}`">
                <span v-if="seminar.status === 'live'" class="live-dot" />
                {{ t(`seminar.status.${seminar.status}`) }}
              </span>
              <span v-if="seminar.language" class="lang-badge">{{ seminar.language }}</span>
            </div>
            <h1 class="seminar-title">{{ seminar.title }}</h1>
            <div class="seminar-meta-row">
              <span v-if="seminar.start_time">&#128197; {{ formatDate(seminar.start_time) }}</span>
              <span v-if="seminar.duration_minutes">&#9200; {{ seminar.duration_minutes }} {{ t('seminar.minutes') }}</span>
              <span v-if="seminar.registered_count">&#128100; {{ t('seminar.registeredCount', { count: seminar.registered_count }) }}</span>
              <span v-if="seminar.status === 'live' && seminar.viewer_count">&#128064; {{ t('seminar.viewerCount', { count: seminar.viewer_count }) }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Body -->
      <section class="section">
        <div class="container detail-layout">

          <!-- Left: description + speaker -->
          <div class="detail-main">
            <!-- Description -->
            <div v-if="seminar.description" class="description-block">
              <!-- eslint-disable-next-line vue/no-v-html -->
              <div class="rich-text" v-html="sanitize(seminar.description)" />
            </div>

            <!-- Speaker -->
            <div v-if="getSpeakerName(seminar)" class="speaker-block">
              <h2 class="block-title">{{ t('seminar.speaker') }}</h2>
              <div class="speaker-card">
                <img
                  v-if="seminar.speaker?.photo_url"
                  :src="seminar.speaker.photo_url"
                  :alt="getSpeakerName(seminar)"
                  class="speaker-avatar"
                />
                <div v-else class="speaker-avatar-placeholder">
                  {{ getSpeakerName(seminar).charAt(0) }}
                </div>
                <div class="speaker-info">
                  <h3 class="speaker-name">{{ getSpeakerName(seminar) }}</h3>
                  <p v-if="seminar.speaker?.title" class="speaker-role">{{ seminar.speaker.title }}</p>
                  <p v-if="seminar.speaker?.bio" class="speaker-bio">{{ seminar.speaker.bio }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Registration panel -->
          <aside class="registration-panel">
            <!-- Action message -->
            <div v-if="actionMsg" class="action-success">{{ actionMsg }}</div>
            <div v-if="actionError" class="action-error">{{ actionError }}</div>

            <!-- Live: Watch Now -->
            <template v-if="seminar.status === 'live'">
              <router-link :to="`/seminars/${seminar.id}/watch`" class="panel-btn btn-live">
                &#9654; {{ t('seminar.watchLive') }}
              </router-link>
            </template>

            <!-- Recorded -->
            <template v-else-if="seminar.status === 'ended'">
              <template v-if="seminar.has_recording">
                <router-link :to="`/seminars/${seminar.id}/watch`" class="panel-btn btn-recording">
                  &#9654; {{ t('seminar.watchRecording') }}
                </router-link>
              </template>
              <template v-else>
                <p class="no-recording-note">{{ t('seminar.noRecording') }}</p>
              </template>
            </template>

            <!-- Scheduled: Registration -->
            <template v-else>
              <template v-if="isRegistered">
                <div class="registered-badge">&#10003; {{ t('seminar.registered') }}</div>
                <button
                  class="panel-btn btn-cancel"
                  :disabled="registering"
                  @click="cancelRegistration"
                >
                  {{ t('seminar.cancelRegistration') }}
                </button>
              </template>
              <template v-else>
                <button
                  class="panel-btn btn-register"
                  :disabled="registering"
                  @click="register"
                >
                  <span v-if="registering">...</span>
                  <span v-else>{{ auth.isLoggedIn ? t('seminar.register') : t('seminar.loginToRegister') }}</span>
                </button>
              </template>
            </template>

            <!-- Meta info -->
            <div class="panel-meta">
              <div v-if="seminar.start_time" class="meta-item">
                <span class="meta-label">{{ t('seminar.startTime') }}</span>
                <span class="meta-value">{{ formatDate(seminar.start_time) }}</span>
              </div>
              <div v-if="seminar.duration_minutes" class="meta-item">
                <span class="meta-label">{{ t('seminar.duration') }}</span>
                <span class="meta-value">{{ seminar.duration_minutes }} {{ t('seminar.minutes') }}</span>
              </div>
              <div v-if="seminar.language" class="meta-item">
                <span class="meta-label">{{ t('seminar.language') }}</span>
                <span class="meta-value">{{ seminar.language }}</span>
              </div>
            </div>
          </aside>
        </div>
      </section>

      <!-- Back link -->
      <div class="container back-link-wrap">
        <router-link to="/seminars" class="back-link">← {{ t('seminar.backToSeminars') }}</router-link>
      </div>

    </template>
  </div>
</template>

<style scoped>
.seminar-detail-page {
  --c-primary: #003366;
  --c-secondary: #E6F0FF;
  --c-accent: #0066CC;
  --c-live: #e53e3e;
  --c-text: #1A1A2E;
  --c-muted: #6C757D;
  --c-border: #DEE2E6;
}

.container { max-width: 1100px; margin: 0 auto; padding: 0 48px; }
.section { padding: 48px 0; }

/* Hero */
.seminar-hero {
  min-height: 340px;
  background: linear-gradient(135deg, var(--c-primary) 0%, #004080 100%);
  background-size: cover;
  background-position: center;
  position: relative;
}
.hero-overlay {
  min-height: 340px;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: flex-end;
  padding-bottom: 48px;
  padding-top: 48px;
}
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #fff;
  opacity: 0.8;
  margin-bottom: 14px;
  flex-wrap: wrap;
}
.breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.breadcrumb a:hover { color: #fff; }
.breadcrumb-title { opacity: 0.65; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sep { opacity: 0.5; }
.hero-status-row { display: flex; gap: 8px; margin-bottom: 12px; }
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.status-scheduled { background: var(--c-primary); color: #fff; border: 1px solid rgba(255,255,255,0.3); }
.status-live { background: var(--c-live); color: #fff; }
.status-ended { background: rgba(80,80,80,0.9); color: #fff; }
.live-dot { width: 7px; height: 7px; border-radius: 50%; background: #fff; animation: blink 1s infinite; }
@keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0; } }
.lang-badge { padding: 4px 10px; background: rgba(255,255,255,0.15); color: #fff; border-radius: 4px; font-size: 12px; }
.seminar-title { font-size: 34px; font-weight: 700; color: #fff; line-height: 1.3; margin-bottom: 12px; }
.seminar-meta-row { display: flex; gap: 20px; flex-wrap: wrap; font-size: 14px; color: rgba(255,255,255,0.8); }

/* Detail layout */
.detail-layout { display: grid; grid-template-columns: 1fr 300px; gap: 48px; align-items: start; }
.detail-main { }

/* Description */
.description-block { margin-bottom: 40px; }
.rich-text :deep(p) { line-height: 1.8; margin-bottom: 14px; color: var(--c-text); }
.rich-text :deep(h2), .rich-text :deep(h3) { color: var(--c-primary); margin: 20px 0 10px; }
.rich-text :deep(ul), .rich-text :deep(ol) { padding-left: 24px; margin-bottom: 14px; }
.rich-text :deep(li) { margin-bottom: 5px; line-height: 1.7; }

/* Speaker block */
.block-title { font-size: 20px; font-weight: 700; color: var(--c-primary); margin-bottom: 16px; }
.speaker-card { display: flex; gap: 16px; padding: 20px; background: var(--c-secondary); border-radius: 10px; }
.speaker-avatar { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.speaker-avatar-placeholder {
  width: 72px; height: 72px; border-radius: 50%;
  background: var(--c-primary); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 28px; font-weight: 700; flex-shrink: 0;
}
.speaker-name { font-size: 18px; font-weight: 700; margin-bottom: 4px; color: var(--c-text); }
.speaker-role { font-size: 14px; color: var(--c-accent); margin-bottom: 8px; }
.speaker-bio { font-size: 14px; color: var(--c-muted); line-height: 1.6; }

/* Registration panel */
.registration-panel {
  background: #fff;
  border: 1px solid var(--c-border);
  border-radius: 12px;
  padding: 28px;
  position: sticky;
  top: 24px;
}
.panel-btn {
  display: block;
  width: 100%;
  padding: 13px;
  text-align: center;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  border: none;
  cursor: pointer;
  margin-bottom: 16px;
  transition: opacity 0.2s;
}
.panel-btn:hover:not(:disabled) { opacity: 0.85; }
.panel-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-live { background: var(--c-live); color: #fff; }
.btn-recording { background: #555; color: #fff; }
.btn-register { background: var(--c-primary); color: #fff; }
.btn-cancel { background: transparent; border: 1px solid var(--c-border); color: var(--c-muted); margin-top: 8px; }

.registered-badge {
  padding: 10px;
  background: #f0fff4;
  border: 1px solid #b2dfdb;
  border-radius: 8px;
  color: #1b5e20;
  text-align: center;
  font-weight: 600;
  margin-bottom: 12px;
}
.no-recording-note { text-align: center; color: var(--c-muted); font-size: 14px; padding: 16px 0; }

.panel-meta { border-top: 1px solid var(--c-border); padding-top: 16px; margin-top: 16px; }
.meta-item { display: flex; flex-direction: column; margin-bottom: 12px; }
.meta-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--c-muted); letter-spacing: 0.5px; margin-bottom: 2px; }
.meta-value { font-size: 14px; color: var(--c-text); }

.action-success {
  padding: 10px 12px;
  background: #f0fff4;
  border: 1px solid #b2dfdb;
  border-radius: 6px;
  color: #1b5e20;
  font-size: 13px;
  margin-bottom: 14px;
}
.action-error {
  padding: 10px 12px;
  background: #fff3f3;
  border: 1px solid #ffcccc;
  border-radius: 6px;
  color: #c62828;
  font-size: 13px;
  margin-bottom: 14px;
}

.back-link-wrap { padding-bottom: 40px; }
.back-link { font-size: 14px; font-weight: 600; color: var(--c-accent); text-decoration: none; }
.back-link:hover { text-decoration: underline; }

/* Responsive */
@media (max-width: 900px) {
  .detail-layout { grid-template-columns: 1fr; }
  .container { padding: 0 24px; }
  .seminar-title { font-size: 26px; }
  .registration-panel { position: static; }
}
</style>

