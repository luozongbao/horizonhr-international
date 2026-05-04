<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
import { publicApi } from '@/api/public'
import { useAuthStore } from '@/stores/auth'
import VideoPlayer from '@/components/seminar/VideoPlayer.vue'
import DanmuOverlay from '@/components/seminar/DanmuOverlay.vue'

const { t } = useI18n()
const route = useRoute()
const auth = useAuthStore()

/* ─── Types ──────────────────────────────────── */
interface Speaker {
  name?: string
  title?: string
  photo_url?: string
}

interface Seminar {
  id: number
  title: string
  status: 'scheduled' | 'live' | 'ended'
  speaker?: Speaker
  speaker_name?: string
  viewer_count?: number
  has_recording?: boolean
}

/* ─── State ──────────────────────────────────── */
const seminar = ref<Seminar | null>(null)
const streamUrl = ref<string | null>(null)
const loading = ref(true)
const fatalError = ref('')          // real error (auth, etc.)
const streamNotAvailable = ref(false)  // live but no URL yet
const streamRetrying = ref(false)   // HLS error → retrying
const viewerCount = ref(0)

// Danmu sidebar input
const danmuRef = ref<InstanceType<typeof DanmuOverlay> | null>(null)
const danmuText = ref('')
const danmuSending = ref(false)
const danmuSendTimestamps = ref<number[]>([])
const DANMU_RATE_MAX = 3
const DANMU_RATE_WINDOW_MS = 10_000
const DANMU_MAX_LENGTH = 50

let viewerTimer: ReturnType<typeof setInterval> | null = null
let streamRetryTimer: ReturnType<typeof setTimeout> | null = null

const seminarId = computed(() => Number(route.params.id))
const isLive = computed(() => seminar.value?.status === 'live')

/* ─── Fetch ──────────────────────────────────── */
async function fetchWatch() {
  loading.value = true
  fatalError.value = ''
  streamNotAvailable.value = false
  try {
    const res = await publicApi.getSeminarWatch(seminarId.value)
    const data = res.data?.data ?? res.data ?? {}
    streamUrl.value = data.url ?? data.stream_url ?? data.hls_url ?? null
    seminar.value = data.seminar ?? null
    viewerCount.value = seminar.value?.viewer_count ?? 0

    if (!streamUrl.value) {
      if (seminar.value?.status === 'live') {
        // Live but stream not started yet — show waiting state, auto-retry
        streamNotAvailable.value = true
        scheduleStreamRetry()
      } else if (seminar.value?.status === 'scheduled') {
        streamNotAvailable.value = true
      } else {
        // ended with no recording
        fatalError.value = t('seminar.noRecording')
      }
    }
  } catch (err: unknown) {
    const status = (err as { response?: { status?: number } })?.response?.status
    if (status === 401 || status === 403) {
      fatalError.value = t('seminar.loginToRegister')
    } else {
      fatalError.value = t('seminarWatch.errorOccurred')
    }
  } finally {
    loading.value = false
  }
}

function scheduleStreamRetry() {
  if (streamRetryTimer) clearTimeout(streamRetryTimer)
  streamRetryTimer = setTimeout(() => {
    fetchWatch()
  }, 15_000)
}

async function refreshViewerCount() {
  if (!isLive.value) return
  try {
    const res = await publicApi.getSeminar(seminarId.value)
    viewerCount.value = res.data?.data?.viewer_count ?? viewerCount.value
  } catch {
    // silently ignore
  }
}

function onPlayerReady() {
  streamRetrying.value = false
  streamNotAvailable.value = false
}

function onPlayerError(msg: string) {
  console.warn('[VideoPlayer error]', msg)
  streamRetrying.value = true
}

function getSpeakerName(): string {
  return seminar.value?.speaker?.name ?? seminar.value?.speaker_name ?? ''
}

/* ─── Danmu sidebar ──────────────────────────── */
function checkSidebarRateLimit(): boolean {
  const now = Date.now()
  danmuSendTimestamps.value = danmuSendTimestamps.value.filter(ts => now - ts < DANMU_RATE_WINDOW_MS)
  return danmuSendTimestamps.value.length >= DANMU_RATE_MAX
}

async function sendSidebarDanmu() {
  const msg = danmuText.value.trim()
  if (!msg || danmuSending.value) return
  if (checkSidebarRateLimit()) {
    ElMessage.warning(t('seminarWatch.danmuRateLimit'))
    return
  }
  danmuSending.value = true
  try {
    await danmuRef.value?.externalSend(msg)
    danmuSendTimestamps.value.push(Date.now())
    danmuText.value = ''
  } finally {
    danmuSending.value = false
  }
}

function handleDanmuKey(e: KeyboardEvent) {
  if (e.key === 'Enter') sendSidebarDanmu()
}

onMounted(() => {
  fetchWatch()
  viewerTimer = setInterval(refreshViewerCount, 30_000)
})

onBeforeUnmount(() => {
  if (viewerTimer) clearInterval(viewerTimer)
  if (streamRetryTimer) clearTimeout(streamRetryTimer)
})
</script>

<template>
  <div class="watch-page">

    <!-- Loading -->
    <div v-if="loading" class="status-screen">
      <el-icon class="loading-spin" :size="48"><Loading /></el-icon>
      <p>{{ t('common.loading') }}</p>
    </div>

    <!-- Stream not available yet (scheduled or live-but-not-started) -->
    <div v-else-if="streamNotAvailable" class="status-screen">
      <div class="status-box">
        <div v-if="isLive" class="waiting-live-badge">
          <span class="live-dot" />
          {{ t('seminarWatch.liveNow') }}
        </div>
        <p class="status-text">{{ t('seminarWatch.streamNotAvailable') }}</p>
        <p class="status-sub">{{ t('seminarWatch.retrying') }}</p>
        <button class="retry-btn-lg" @click="fetchWatch">{{ t('seminarWatch.retry') }}</button>
        <router-link :to="`/seminars/${seminarId}`" class="back-link">
          ← {{ t('seminar.backToSeminars') }}
        </router-link>
      </div>
    </div>

    <!-- Fatal error -->
    <div v-else-if="fatalError" class="status-screen">
      <div class="status-box">
        <p class="status-text">{{ fatalError }}</p>
        <button class="retry-btn-lg" @click="fetchWatch">{{ t('seminarWatch.retry') }}</button>
        <router-link :to="`/seminars/${seminarId}`" class="back-link">
          ← {{ t('seminar.backToSeminars') }}
        </router-link>
      </div>
    </div>

    <!-- Player -->
    <div v-else class="watch-layout">

      <!-- Video column -->
      <div class="video-column">
        <!-- Breadcrumb -->
        <nav class="watch-breadcrumb">
          <router-link to="/seminars">{{ t('seminar.pageTitle') }}</router-link>
          <span class="sep">/</span>
          <router-link v-if="seminar" :to="`/seminars/${seminarId}`">{{ seminar.title }}</router-link>
          <span class="sep">/</span>
          <span>{{ isLive ? t('seminar.watchLive') : t('seminarWatch.watchRecording') }}</span>
        </nav>

        <!-- Player wrapper -->
        <div class="player-wrap">
          <VideoPlayer
            :src="streamUrl!"
            :autoplay="isLive"
            :is-live="isLive"
            :enable-speed-control="!isLive"
            @ready="onPlayerReady"
            @error="onPlayerError"
            @retry="fetchWatch"
          />
          <!-- Danmu overlay only for live — no built-in input (sidebar handles it) -->
          <DanmuOverlay
            v-if="isLive"
            ref="danmuRef"
            :seminar-id="seminarId"
            :live="true"
            :show-input="false"
          />
        </div>

        <!-- Live: retrying banner -->
        <div v-if="streamRetrying" class="retrying-banner">
          {{ t('seminarWatch.retrying') }}
        </div>

        <!-- Live: status bar -->
        <div v-if="isLive" class="live-statusbar">
          <span class="live-badge">
            <span class="live-dot" />
            {{ t('seminarWatch.liveNow') }}
          </span>
          <span v-if="viewerCount" class="viewer-count">
            &#128064; {{ t('seminarWatch.viewerCount', { count: viewerCount }) }}
          </span>
        </div>
      </div>

      <!-- Sidebar -->
      <aside class="watch-sidebar">
        <div v-if="seminar" class="sidebar-info">
          <h2 class="sidebar-title">{{ seminar.title }}</h2>
          <div v-if="getSpeakerName()" class="sidebar-speaker">
            <img
              v-if="seminar.speaker?.photo_url"
              :src="seminar.speaker.photo_url"
              :alt="getSpeakerName()"
              class="sidebar-avatar"
            />
            <div class="sidebar-speaker-info">
              <p class="sidebar-speaker-name">{{ getSpeakerName() }}</p>
              <p v-if="seminar.speaker?.title" class="sidebar-speaker-role">{{ seminar.speaker.title }}</p>
            </div>
          </div>
        </div>

        <!-- Danmu input (live only) -->
        <div v-if="isLive" class="sidebar-danmu">
          <template v-if="auth.isLoggedIn">
            <div class="danmu-field">
              <input
                v-model="danmuText"
                class="danmu-input"
                :placeholder="t('seminarWatch.danmuPlaceholder')"
                :maxlength="DANMU_MAX_LENGTH"
                @keydown="handleDanmuKey"
              />
              <button
                class="danmu-send-btn"
                :disabled="danmuSending || !danmuText.trim()"
                @click="sendSidebarDanmu"
              >
                {{ t('seminarWatch.danmuSend') }}
              </button>
            </div>
            <p class="danmu-char-count">{{ danmuText.length }}/{{ DANMU_MAX_LENGTH }}</p>
          </template>
          <template v-else>
            <router-link to="/login" class="danmu-login-hint">
              {{ t('seminarWatch.danmuLoginRequired') }}
            </router-link>
          </template>
        </div>

        <router-link :to="`/seminars/${seminarId}`" class="sidebar-back-link">
          ← {{ t('seminar.backToSeminars') }}
        </router-link>
      </aside>

    </div>
  </div>
</template>

<style scoped>
.watch-page {
  --c-primary: #003366;
  --c-secondary: #E6F0FF;
  --c-accent: #0066CC;
  --c-live: #e53e3e;
  --c-text: #1A1A2E;
  --c-muted: #6C757D;
  --c-border: #DEE2E6;
  min-height: 80vh;
  background: #0a0a0a;
  color: #fff;
}

/* Loading / Error / Waiting */
.status-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  gap: 16px;
  color: #fff;
  padding: 48px;
}
.loading-spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.status-box {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  max-width: 420px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.waiting-live-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--c-live);
  color: #fff;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.status-text { font-size: 16px; color: rgba(255,255,255,0.85); margin: 0; }
.status-sub { font-size: 13px; color: rgba(255,255,255,0.5); margin: 0; }

.retry-btn-lg {
  background: #0066cc;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 24px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.15s;
  margin-top: 4px;
}
.retry-btn-lg:hover { background: #0055aa; }

.back-link { color: #6ab4ff; font-weight: 600; text-decoration: none; font-size: 14px; }
.back-link:hover { text-decoration: underline; }

/* Layout */
.watch-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  min-height: 100vh;
}

/* Video column */
.video-column { padding: 24px; }
.watch-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: rgba(255,255,255,0.6);
  margin-bottom: 16px;
  flex-wrap: wrap;
}
.watch-breadcrumb a { color: rgba(255,255,255,0.6); text-decoration: none; }
.watch-breadcrumb a:hover { color: #fff; }
.sep { opacity: 0.4; }

.player-wrap {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
}

/* Retrying banner */
.retrying-banner {
  margin-top: 8px;
  padding: 8px 14px;
  background: rgba(255, 200, 0, 0.12);
  border: 1px solid rgba(255, 200, 0, 0.3);
  border-radius: 6px;
  color: #ffd700;
  font-size: 13px;
  text-align: center;
}

/* Live status */
.live-statusbar {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: 12px;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}
.live-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--c-live);
  color: #fff;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.live-dot { width: 7px; height: 7px; border-radius: 50%; background: #fff; animation: blink 1s infinite; }
@keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0; } }
.viewer-count { font-size: 14px; color: rgba(255,255,255,0.75); }

/* Sidebar */
.watch-sidebar {
  background: rgba(255,255,255,0.04);
  border-left: 1px solid rgba(255,255,255,0.08);
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.sidebar-title { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 16px; line-height: 1.4; }
.sidebar-speaker {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(255,255,255,0.06);
  border-radius: 8px;
}
.sidebar-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.sidebar-speaker-name { font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 2px; }
.sidebar-speaker-role { font-size: 12px; color: rgba(255,255,255,0.6); }

/* Danmu input area */
.sidebar-danmu {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.danmu-field {
  display: flex;
  gap: 8px;
}
.danmu-input {
  flex: 1;
  padding: 9px 12px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 6px;
  color: #fff;
  font-size: 13px;
  outline: none;
  min-width: 0;
}
.danmu-input::placeholder { color: rgba(255,255,255,0.4); }
.danmu-input:focus { border-color: rgba(255,255,255,0.5); }
.danmu-send-btn {
  padding: 9px 14px;
  background: #003366;
  border: none;
  border-radius: 6px;
  color: #fff;
  font-size: 13px;
  cursor: pointer;
  transition: background 0.15s;
  white-space: nowrap;
  flex-shrink: 0;
}
.danmu-send-btn:hover:not(:disabled) { background: #0055aa; }
.danmu-send-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.danmu-char-count {
  font-size: 11px;
  color: rgba(255,255,255,0.35);
  text-align: right;
  margin: 0;
}
.danmu-login-hint {
  color: rgba(255,255,255,0.5);
  font-size: 13px;
  text-decoration: underline;
}

.sidebar-back-link {
  margin-top: auto;
  display: inline-block;
  color: rgba(255,255,255,0.5);
  font-size: 13px;
  text-decoration: none;
}
.sidebar-back-link:hover { color: #fff; }

/* Responsive */
@media (max-width: 900px) {
  .watch-layout { grid-template-columns: 1fr; }
  .watch-sidebar {
    border-left: none;
    border-top: 1px solid rgba(255,255,255,0.08);
  }
}
</style>
