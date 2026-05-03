<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'
import VideoPlayer from '@/components/seminar/VideoPlayer.vue'
import DanmuOverlay from '@/components/seminar/DanmuOverlay.vue'

const { t } = useI18n()
const route = useRoute()

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
const error = ref('')
const viewerCount = ref(0)

let viewerTimer: ReturnType<typeof setInterval> | null = null

const seminarId = computed(() => Number(route.params.id))
const isLive = computed(() => seminar.value?.status === 'live')

/* ─── Fetch ──────────────────────────────────── */
async function fetchWatch() {
  loading.value = true
  error.value = ''
  try {
    // Get stream URL
    const res = await publicApi.getSeminarWatch(seminarId.value)
    const data = res.data?.data ?? res.data ?? {}
    streamUrl.value = data.url ?? data.stream_url ?? data.hls_url ?? null
    seminar.value = data.seminar ?? null
    viewerCount.value = seminar.value?.viewer_count ?? 0

    if (!streamUrl.value) {
      error.value = t('seminar.noRecording')
    }
  } catch (err: unknown) {
    const status = (err as { response?: { status?: number } })?.response?.status
    if (status === 401 || status === 403) {
      error.value = t('seminar.loginToRegister')
    } else {
      error.value = t('common.error')
    }
  } finally {
    loading.value = false
  }
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

function getSpeakerName(): string {
  return seminar.value?.speaker?.name ?? seminar.value?.speaker_name ?? ''
}

onMounted(() => {
  fetchWatch()
  viewerTimer = setInterval(refreshViewerCount, 30_000)
})

onBeforeUnmount(() => {
  if (viewerTimer) clearInterval(viewerTimer)
})
</script>

<template>
  <div class="watch-page">

    <!-- Loading -->
    <div v-if="loading" class="loading-screen">
      <el-icon class="loading-spin" :size="48"><Loading /></el-icon>
      <p>{{ t('common.loading') }}</p>
    </div>

    <!-- Error -->
    <div v-else-if="error || !streamUrl" class="error-screen">
      <div class="error-box">
        <p class="error-text">{{ error || t('seminar.noRecording') }}</p>
        <router-link :to="seminarId ? `/seminars/${seminarId}` : '/seminars'" class="back-link">
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
          <span>{{ isLive ? t('seminar.watchLive') : t('seminar.watchRecording') }}</span>
        </nav>

        <!-- Player wrapper -->
        <div class="player-wrap">
          <VideoPlayer
            :src="streamUrl"
            :autoplay="isLive"
            :enable-speed-control="!isLive"
          />
          <!-- Danmu overlay only for live -->
          <DanmuOverlay
            v-if="isLive"
            :seminar-id="seminarId"
            :live="true"
          />
        </div>

        <!-- Live: status bar -->
        <div v-if="isLive" class="live-statusbar">
          <span class="live-badge">
            <span class="live-dot" />
            {{ t('seminar.status.live') }}
          </span>
          <span v-if="viewerCount" class="viewer-count">
            &#128064; {{ t('seminar.viewerCount', { count: viewerCount }) }}
          </span>
        </div>

        <!-- Recording speed control info -->
        <div v-if="!isLive" class="recording-note">
          &#9654; {{ t('seminar.playbackSpeed') }}: {{ t('common.filter') }} via player controls
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

/* Loading / Error */
.loading-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  gap: 16px;
  color: #fff;
}
.loading-spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.error-screen {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  padding: 48px;
}
.error-box {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  max-width: 400px;
}
.error-text { font-size: 16px; margin-bottom: 20px; color: rgba(255,255,255,0.8); }
.back-link { color: #6ab4ff; font-weight: 600; text-decoration: none; }
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

.recording-note { margin-top: 10px; font-size: 13px; color: rgba(255,255,255,0.5); }

/* Sidebar */
.watch-sidebar {
  background: rgba(255,255,255,0.04);
  border-left: 1px solid rgba(255,255,255,0.08);
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.sidebar-info { }
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

