<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import enterpriseApi from '@/api/enterprise'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()

const interviewId = computed(() => Number(route.params.id))

// ─── Types ────────────────────────────────────────────────────────────────────
interface TrtcCredentials {
  room_id: string | number
  user_id: string
  user_sig: string
  sdk_app_id: number
}

interface InterviewDetail {
  id: number
  title?: string
  student_name?: string
  scheduled_at: string
  duration_minutes: number
  status: string
  interviewer_name?: string
}

// ─── State ────────────────────────────────────────────────────────────────────
type Phase = 'loading' | 'error-404' | 'error-403' | 'not-started' | 'waiting' | 'room'

const phase = ref<Phase>('loading')
const interview = ref<InterviewDetail | null>(null)
const credentials = ref<TrtcCredentials | null>(null)
const errorMessage = ref('')

// Camera / mic check
const videoRef = ref<HTMLVideoElement | null>(null)
const localStream = ref<MediaStream | null>(null)
const cameraOk = ref(false)
const micOk = ref(false)
const permissionDenied = ref(false)
const audioLevel = ref(0)
let audioCtx: AudioContext | null = null
let analyserNode: AnalyserNode | null = null
let audioLevelTimer: ReturnType<typeof setInterval>

// Countdown
const now = ref(Date.now())
let tickTimer: ReturnType<typeof setInterval>

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  tickTimer = setInterval(() => { now.value = Date.now() }, 1000)
  await loadInterview()
})

onUnmounted(() => {
  clearInterval(tickTimer)
  clearInterval(audioLevelTimer)
  stopLocalMedia()
  if (audioCtx) { audioCtx.close(); audioCtx = null }
})

// ─── Load interview ───────────────────────────────────────────────────────────
async function loadInterview() {
  phase.value = 'loading'
  try {
    const detailRes = await enterpriseApi.getInterview(interviewId.value)
    interview.value = (detailRes.data.data ?? detailRes.data) as InterviewDetail

    if (interview.value.status === 'cancelled') {
      errorMessage.value = 'This interview has been cancelled.'
      phase.value = 'error-404'
      return
    }

    const scheduledMs = new Date(interview.value.scheduled_at).getTime()
    const diff = scheduledMs - Date.now()
    if (interview.value.status !== 'ongoing' && diff > 15 * 60 * 1000) {
      phase.value = 'not-started'
      return
    }

    const joinRes = await enterpriseApi.joinInterview(interviewId.value)
    credentials.value = (joinRes.data.data ?? joinRes.data) as TrtcCredentials

    phase.value = 'waiting'
    await startLocalMedia()

  } catch (e: unknown) {
    const err = e as { response?: { status?: number; data?: { message?: string } } }
    const status = err.response?.status
    if (status === 404) { phase.value = 'error-404'; return }
    if (status === 403) { phase.value = 'error-403'; return }
    errorMessage.value = err.response?.data?.message ?? 'Failed to load interview.'
    phase.value = 'error-404'
  }
}

// ─── Media helpers ────────────────────────────────────────────────────────────
async function startLocalMedia() {
  permissionDenied.value = false
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true })
    localStream.value = stream
    cameraOk.value = stream.getVideoTracks().length > 0
    micOk.value = stream.getAudioTracks().length > 0

    if (videoRef.value) videoRef.value.srcObject = stream

    audioCtx = new AudioContext()
    const source = audioCtx.createMediaStreamSource(stream)
    analyserNode = audioCtx.createAnalyser()
    analyserNode.fftSize = 256
    source.connect(analyserNode)
    const data = new Uint8Array(analyserNode.frequencyBinCount)
    audioLevelTimer = setInterval(() => {
      if (!analyserNode) return
      analyserNode.getByteFrequencyData(data)
      const avg = data.reduce((a, b) => a + b, 0) / data.length
      audioLevel.value = Math.min(100, Math.round((avg / 128) * 100))
    }, 100)

  } catch {
    permissionDenied.value = true
    cameraOk.value = false
    micOk.value = false
    ElMessage.warning(t('interviews.room.permissionDenied'))
  }
}

function stopLocalMedia() {
  if (localStream.value) {
    localStream.value.getTracks().forEach((tr) => tr.stop())
    localStream.value = null
  }
}

function enterRoom() {
  stopLocalMedia()
  clearInterval(audioLevelTimer)
  if (audioCtx) { audioCtx.close(); audioCtx = null }
  phase.value = 'room'
}

// ─── Leave ────────────────────────────────────────────────────────────────────
async function leaveRoom() {
  try {
    await ElMessageBox.confirm(
      t('interviews.room.leaveConfirm'),
      t('interviews.room.leaveRoom'),
      { type: 'warning', confirmButtonText: t('interviews.room.leaveRoom'), cancelButtonText: t('common.cancel') },
    )
  } catch { return }
  stopLocalMedia()
  router.push('/enterprise/interviews')
}

// ─── Countdown ────────────────────────────────────────────────────────────────
const countdownDisplay = computed(() => {
  if (!interview.value) return ''
  const ms = new Date(interview.value.scheduled_at).getTime() - now.value
  if (ms <= 0) return t('interviews.countdownNow')
  const totalSec = Math.floor(ms / 1000)
  const h = Math.floor(totalSec / 3600)
  const m = Math.floor((totalSec % 3600) / 60)
  const s = totalSec % 60
  return h > 0 ? `${h}h ${m}m` : m > 0 ? `${m}m ${s}s` : `${s}s`
})

function formatDateTime(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleString(undefined, {
    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

watch(phase, async (p) => {
  if (p === 'waiting') {
    await nextTick()
    if (videoRef.value && localStream.value) videoRef.value.srcObject = localStream.value
  }
})
</script>

<template>
  <div class="room-page">

    <!-- Loading -->
    <div v-if="phase === 'loading'" class="center-box">
      <el-skeleton animated :rows="4" style="max-width: 480px;" />
    </div>

    <!-- Error states -->
    <div v-else-if="phase === 'error-404'" class="center-box">
      <el-result icon="error" :title="errorMessage || t('interviews.room.notFound')" />
      <el-button @click="router.push('/enterprise/interviews')">{{ t('common.back') }}</el-button>
    </div>

    <div v-else-if="phase === 'error-403'" class="center-box">
      <el-result icon="warning" :title="t('interviews.room.notAuthorized')" />
      <el-button @click="router.push('/enterprise/interviews')">{{ t('common.back') }}</el-button>
    </div>

    <!-- Not started yet -->
    <div v-else-if="phase === 'not-started'" class="center-box">
      <el-card class="countdown-card">
        <h2>{{ interview?.title ?? t('interviews.room.title') }}</h2>
        <p class="scheduled-label">{{ formatDateTime(interview?.scheduled_at ?? '') }}</p>
        <div class="countdown-timer">{{ countdownDisplay }}</div>
        <p class="countdown-hint">{{ t('interviews.room.notStarted', { time: formatDateTime(interview?.scheduled_at ?? '') }) }}</p>
        <el-button @click="router.push('/enterprise/interviews')" style="margin-top: 16px;">
          {{ t('common.back') }}
        </el-button>
      </el-card>
    </div>

    <!-- Pre-room waiting / camera check -->
    <div v-else-if="phase === 'waiting'" class="waiting-room">
      <div class="waiting-header">
        <h1>{{ t('interviews.room.waiting') }}</h1>
        <p class="interview-subtitle">{{ interview?.title ?? '' }}
          <span v-if="interview?.student_name"> — {{ interview.student_name }}</span>
        </p>
      </div>

      <div class="media-check">
        <!-- Camera preview -->
        <div class="video-preview">
          <video ref="videoRef" autoplay muted playsinline class="preview-video" />
          <div v-if="permissionDenied" class="no-camera">{{ t('interviews.room.permissionDenied') }}</div>
        </div>

        <!-- Device status -->
        <div class="device-status">
          <div class="device-row">
            <span class="device-label">{{ t('interviews.room.checkCamera') }}</span>
            <el-tag :type="cameraOk ? 'success' : 'danger'" size="small">
              {{ cameraOk ? '✓' : '✗' }}
            </el-tag>
          </div>
          <div class="device-row">
            <span class="device-label">{{ t('interviews.room.checkMic') }}</span>
            <el-tag :type="micOk ? 'success' : 'danger'" size="small">
              {{ micOk ? '✓' : '✗' }}
            </el-tag>
          </div>
          <div v-if="micOk" class="audio-meter">
            <span class="device-label">Level</span>
            <el-progress :percentage="audioLevel" :show-text="false" class="audio-bar" />
          </div>
        </div>
      </div>

      <div class="waiting-actions">
        <el-button size="large" type="primary" :disabled="permissionDenied" @click="enterRoom">
          {{ t('interviews.room.enterRoom') }}
        </el-button>
        <el-button size="large" @click="router.push('/enterprise/interviews')">{{ t('common.back') }}</el-button>
      </div>
    </div>

    <!-- Interview room -->
    <div v-else-if="phase === 'room'" class="interview-room">
      <div class="room-header">
        <h2>{{ interview?.title ?? t('interviews.room.title') }}</h2>
        <el-button type="danger" size="small" @click="leaveRoom">
          {{ t('interviews.room.leaveRoom') }}
        </el-button>
      </div>

      <!-- TRTC placeholder — replaced in TASK-039 -->
      <div class="trtc-placeholder">
        <div class="trtc-inner">
          <div class="trtc-icon">📹</div>
          <p>Video call room</p>
          <p class="trtc-hint">
            Room ID: {{ credentials?.room_id }} |
            SDK App: {{ credentials?.sdk_app_id }}
          </p>
          <p class="trtc-hint">TrtcRoom component will be integrated in TASK-039</p>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.room-page { min-height: 100vh; background: #f5f7fa; }

.center-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  gap: 16px;
  padding: 32px;
}

.countdown-card {
  max-width: 480px;
  width: 100%;
  text-align: center;
  padding: 32px;
}
.countdown-card h2 { font-size: 20px; font-weight: 600; margin-bottom: 8px; }
.scheduled-label { font-size: 14px; color: #6c757d; margin-bottom: 24px; }
.countdown-timer {
  font-size: 40px;
  font-weight: 700;
  color: #003366;
  letter-spacing: 2px;
  margin-bottom: 12px;
}
.countdown-hint { font-size: 13px; color: #6c757d; }

/* Waiting room */
.waiting-room {
  max-width: 720px;
  margin: 0 auto;
  padding: 40px 24px;
}
.waiting-header { margin-bottom: 32px; text-align: center; }
.waiting-header h1 { font-size: 24px; font-weight: 600; color: #003366; }
.interview-subtitle { font-size: 14px; color: #6c757d; margin-top: 8px; }

.media-check {
  display: flex;
  gap: 24px;
  margin-bottom: 32px;
  flex-wrap: wrap;
}

.video-preview {
  flex: 1;
  min-width: 280px;
  position: relative;
  background: #1a1a2e;
  border-radius: 12px;
  overflow: hidden;
  aspect-ratio: 4/3;
}
.preview-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.no-camera {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 13px;
  text-align: center;
  padding: 16px;
}

.device-status {
  flex: 0 0 200px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  justify-content: center;
}
.device-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.device-label { font-size: 13px; color: #6c757d; }
.audio-meter { display: flex; align-items: center; gap: 8px; }
.audio-bar { flex: 1; }

.waiting-actions { display: flex; gap: 12px; justify-content: center; }

/* Interview room */
.interview-room { padding: 24px; }
.room-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.room-header h2 { font-size: 20px; font-weight: 600; color: #003366; }

.trtc-placeholder {
  background: #1a1a2e;
  border-radius: 12px;
  min-height: 480px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.trtc-inner { text-align: center; color: #fff; }
.trtc-icon { font-size: 48px; margin-bottom: 12px; }
.trtc-hint { font-size: 12px; color: #a0aec0; margin-top: 4px; }
</style>

