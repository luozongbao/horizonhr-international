<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import studentApi from '@/api/student'

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
  job_title: string
  company_name: string
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

// Countdown (for not-started state)
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

// ─── Load interview + join ────────────────────────────────────────────────────
async function loadInterview() {
  phase.value = 'loading'
  try {
    const detailRes = await studentApi.getInterview(interviewId.value)
    interview.value = (detailRes.data.data ?? detailRes.data) as InterviewDetail

    // Check if started yet (allow 15 min early)
    const scheduledMs = new Date(interview.value.scheduled_at).getTime()
    const diff = scheduledMs - Date.now()
    if (interview.value.status === 'cancelled') {
      errorMessage.value = 'This interview has been cancelled.'
      phase.value = 'error-404'
      return
    }
    if (interview.value.status !== 'ongoing' && diff > 15 * 60 * 1000) {
      phase.value = 'not-started'
      return
    }

    // Get TRTC credentials
    const joinRes = await studentApi.joinInterview(interviewId.value)
    credentials.value = (joinRes.data.data ?? joinRes.data) as TrtcCredentials

    // Enter waiting room
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

// ─── Media device helpers ─────────────────────────────────────────────────────
async function startLocalMedia() {
  permissionDenied.value = false
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true })
    localStream.value = stream
    cameraOk.value = stream.getVideoTracks().length > 0
    micOk.value = stream.getAudioTracks().length > 0

    // Attach to video element
    if (videoRef.value) {
      videoRef.value.srcObject = stream
    }

    // Audio level meter
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
    localStream.value.getTracks().forEach((t) => t.stop())
    localStream.value = null
  }
}

function enterRoom() {
  // Stop preview (real TRTC will reacquire media in TASK-039)
  stopLocalMedia()
  clearInterval(audioLevelTimer)
  if (audioCtx) { audioCtx.close(); audioCtx = null }
  phase.value = 'room'
}

// ─── Leave room ───────────────────────────────────────────────────────────────
async function leaveRoom() {
  try {
    await ElMessageBox.confirm(
      t('interviews.room.leaveConfirm'),
      t('interviews.room.leaveRoom'),
      { type: 'warning', confirmButtonText: t('interviews.room.leaveRoom'), cancelButtonText: 'Cancel' },
    )
  } catch {
    return
  }
  stopLocalMedia()
  router.push('/student/interviews')
}

// ─── Countdown for not-started ────────────────────────────────────────────────
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

// Watch videoRef binding after phase change
import { watch, nextTick } from 'vue'
watch(phase, async (p) => {
  if (p === 'waiting') {
    await nextTick()
    if (videoRef.value && localStream.value) {
      videoRef.value.srcObject = localStream.value
    }
  }
})
</script>

<template>
  <div class="interview-room-page">

    <!-- ── Loading ─────────────────────────────────────────────── -->
    <div v-if="phase === 'loading'" class="center-state">
      <el-icon class="spin-icon"><Loading /></el-icon>
      <p>Loading interview...</p>
    </div>

    <!-- ── Error 404 / generic ────────────────────────────────── -->
    <div v-else-if="phase === 'error-404'" class="center-state error-state">
      <el-icon class="state-icon"><Warning /></el-icon>
      <h2>{{ errorMessage || t('interviews.room.notFound') }}</h2>
      <el-button type="primary" @click="router.push('/student/interviews')">
        Back to Interviews
      </el-button>
    </div>

    <!-- ── Error 403 ──────────────────────────────────────────── -->
    <div v-else-if="phase === 'error-403'" class="center-state error-state">
      <el-icon class="state-icon"><Lock /></el-icon>
      <h2>{{ t('interviews.room.notAuthorized') }}</h2>
      <el-button type="primary" @click="router.push('/student/interviews')">
        Back to Interviews
      </el-button>
    </div>

    <!-- ── Not started yet ────────────────────────────────────── -->
    <div v-else-if="phase === 'not-started'" class="center-state not-started-state">
      <div class="clock-card">
        <el-icon class="clock-icon"><Clock /></el-icon>
        <h2 class="interview-title">{{ interview?.job_title }}</h2>
        <p class="interview-company">{{ interview?.company_name }}</p>
        <div class="countdown-display">{{ countdownDisplay }}</div>
        <p class="scheduled-label">
          {{ t('interviews.room.notStarted', { time: formatDateTime(interview?.scheduled_at ?? '') }) }}
        </p>
        <el-button plain @click="router.push('/student/interviews')">
          Back to Interviews
        </el-button>
      </div>
    </div>

    <!-- ── Waiting room (camera/mic check) ───────────────────── -->
    <div v-else-if="phase === 'waiting'" class="waiting-room">
      <div class="waiting-inner">
        <h1 class="room-title">{{ t('interviews.room.waiting') }}</h1>
        <p class="room-subtitle">{{ interview?.job_title }} · {{ interview?.company_name }}</p>

        <div class="check-grid">
          <!-- Camera preview -->
          <div class="check-card">
            <h3 class="check-title">
              <el-icon><VideoCamera /></el-icon>
              {{ t('interviews.room.checkCamera') }}
            </h3>
            <div class="video-preview-wrap">
              <video
                ref="videoRef"
                class="local-video"
                autoplay
                muted
                playsinline
              />
              <div v-if="!cameraOk" class="video-placeholder">
                <el-icon><VideoCamera /></el-icon>
                <span v-if="permissionDenied" class="permission-msg">{{ t('interviews.room.permissionDenied') }}</span>
                <span v-else>No camera detected</span>
              </div>
            </div>
            <el-tag :type="cameraOk ? 'success' : 'danger'" size="small">
              {{ cameraOk ? 'Camera OK' : 'No camera' }}
            </el-tag>
          </div>

          <!-- Microphone check -->
          <div class="check-card">
            <h3 class="check-title">
              <el-icon><Microphone /></el-icon>
              {{ t('interviews.room.checkMic') }}
            </h3>
            <div class="mic-meter-wrap">
              <div class="mic-icon-wrap">
                <el-icon class="mic-big" :class="{ active: audioLevel > 10 }"><Microphone /></el-icon>
              </div>
              <div class="audio-bar">
                <div class="audio-fill" :style="{ width: `${audioLevel}%` }" />
              </div>
              <p class="level-label">{{ audioLevel > 0 ? `${audioLevel}%` : '—' }}</p>
            </div>
            <el-tag :type="micOk ? 'success' : 'danger'" size="small">
              {{ micOk ? 'Microphone OK' : 'No microphone' }}
            </el-tag>
          </div>
        </div>

        <!-- Permission denied retry -->
        <div v-if="permissionDenied" class="permission-alert">
          <el-alert type="warning" :closable="false" show-icon>
            {{ t('interviews.room.permissionDenied') }}
          </el-alert>
          <el-button @click="startLocalMedia">Retry Permissions</el-button>
        </div>

        <!-- Dev: TRTC credentials info -->
        <div v-if="credentials" class="dev-info">
          <p><strong>Room ID:</strong> {{ credentials.room_id }}</p>
          <p><strong>SDK App ID:</strong> {{ credentials.sdk_app_id }}</p>
        </div>

        <div class="waiting-actions">
          <el-button plain @click="router.push('/student/interviews')">Cancel</el-button>
          <el-button type="primary" size="large" @click="enterRoom">
            <el-icon class="el-icon--left"><VideoCamera /></el-icon>
            {{ t('interviews.room.readyToJoin') }}
          </el-button>
        </div>
      </div>
    </div>

    <!-- ── Interview room (placeholder for TASK-039) ──────────── -->
    <div v-else-if="phase === 'room'" class="room-active">
      <div class="room-topbar">
        <div class="room-info">
          <el-icon><VideoCamera /></el-icon>
          <span class="room-label">{{ t('interviews.room.title') }}</span>
          <span class="room-job">{{ interview?.job_title }} · {{ interview?.company_name }}</span>
        </div>
        <el-button type="danger" size="small" @click="leaveRoom">
          <el-icon class="el-icon--left"><SwitchButton /></el-icon>
          {{ t('interviews.room.leaveRoom') }}
        </el-button>
      </div>

      <!-- TRTC placeholder -->
      <div class="trtc-placeholder">
        <el-icon class="placeholder-icon"><VideoCamera /></el-icon>
        <p class="placeholder-title">TRTC Video Room</p>
        <p class="placeholder-sub">
          Full video call implementation coming in TASK-039.<br />
          Room ID: <strong>{{ credentials?.room_id }}</strong>
        </p>
        <div v-if="credentials" class="creds-grid">
          <div class="cred-item"><span class="cred-label">SDK App ID</span><code>{{ credentials.sdk_app_id }}</code></div>
          <div class="cred-item"><span class="cred-label">Room ID</span><code>{{ credentials.room_id }}</code></div>
          <div class="cred-item"><span class="cred-label">User ID</span><code>{{ credentials.user_id }}</code></div>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.interview-room-page {
  min-height: calc(100vh - 64px);
  display: flex;
  flex-direction: column;
}

/* Centre states */
.center-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 48px;
  text-align: center;
}
.spin-icon {
  font-size: 48px;
  color: #0066cc;
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.error-state .state-icon { font-size: 56px; color: #dc3545; }
.error-state h2 { font-size: 18px; color: #1a1a2e; }

/* Not started */
.not-started-state .clock-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 16px;
  padding: 40px 56px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  max-width: 440px;
}
.clock-icon { font-size: 56px; color: #0066cc; }
.interview-title { font-size: 20px; font-weight: 700; color: #003366; margin: 0; }
.interview-company { font-size: 14px; color: #6c757d; margin: 0; }
.countdown-display {
  font-size: 36px;
  font-weight: 700;
  color: #003366;
  letter-spacing: 0.04em;
}
.scheduled-label { font-size: 13px; color: #6c757d; margin: 0; }

/* Waiting room */
.waiting-room {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
  background: #f5f7fa;
}
.waiting-inner {
  background: #fff;
  border-radius: 16px;
  border: 1px solid #dee2e6;
  padding: 32px 36px;
  width: 100%;
  max-width: 720px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.room-title { font-size: 22px; font-weight: 700; color: #003366; margin: 0; }
.room-subtitle { font-size: 14px; color: #6c757d; margin: 0; }

.check-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.check-card {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 10px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.check-title {
  font-size: 14px;
  font-weight: 600;
  color: #003366;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

/* Video preview */
.video-preview-wrap {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  background: #1a1a2e;
  aspect-ratio: 4/3;
}
.local-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scaleX(-1); /* mirror */
}
.video-placeholder {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: rgba(255,255,255,0.5);
  font-size: 12px;
}
.video-placeholder .el-icon { font-size: 32px; }

/* Mic meter */
.mic-meter-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  padding: 16px 0;
}
.mic-big { font-size: 40px; color: #6c757d; transition: color 0.1s; }
.mic-big.active { color: #0066cc; }
.audio-bar {
  width: 100%;
  height: 8px;
  background: #dee2e6;
  border-radius: 4px;
  overflow: hidden;
}
.audio-fill {
  height: 100%;
  background: linear-gradient(90deg, #52c41a, #0066cc);
  border-radius: 4px;
  transition: width 0.1s;
}
.level-label { font-size: 12px; color: #6c757d; margin: 0; }

.permission-alert { display: flex; flex-direction: column; gap: 10px; }
.permission-msg { font-size: 12px; color: rgba(255,255,255,0.7); }

/* Dev info */
.dev-info {
  background: #f0f4f8;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 12px;
  color: #6c757d;
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}
.dev-info strong { color: #1a1a2e; }

.waiting-actions { display: flex; justify-content: flex-end; gap: 12px; }

/* Active room */
.room-active {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #1a1a2e;
}
.room-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  background: #0d1117;
  color: #fff;
}
.room-info { display: flex; align-items: center; gap: 10px; font-size: 14px; }
.room-label { font-weight: 600; color: #fff; }
.room-job { color: rgba(255,255,255,0.6); }

.trtc-placeholder {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 48px;
  text-align: center;
  color: #fff;
}
.placeholder-icon { font-size: 64px; color: rgba(255,255,255,0.2); }
.placeholder-title { font-size: 22px; font-weight: 600; margin: 0; }
.placeholder-sub { font-size: 14px; color: rgba(255,255,255,0.5); margin: 0; line-height: 1.7; }

.creds-grid {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  justify-content: center;
  margin-top: 8px;
}
.cred-item { display: flex; flex-direction: column; gap: 4px; text-align: center; }
.cred-label { font-size: 11px; text-transform: uppercase; color: rgba(255,255,255,0.4); letter-spacing: 0.06em; }
.cred-item code { background: rgba(255,255,255,0.08); padding: 4px 10px; border-radius: 4px; font-size: 13px; color: #7dd3fc; }

@media (max-width: 640px) {
  .check-grid { grid-template-columns: 1fr; }
  .waiting-inner { padding: 20px; }
}
</style>

