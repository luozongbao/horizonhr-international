<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
// @ts-ignore — trtc-js-sdk ships its own type defs but may not have a default export declaration
import TRTC from 'trtc-js-sdk'

const { t } = useI18n()

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps<{
  sdkAppId: number
  roomId: number
  userId: string
  userSig: string
  displayName: string
  role: 'student' | 'enterprise'
}>()

const emit = defineEmits<{
  (e: 'leave'): void
}>()

// ─── Refs ─────────────────────────────────────────────────────────────────────
const localVideoRef = ref<HTMLDivElement | null>(null)
const remoteVideoRef = ref<HTMLDivElement | null>(null)

// ─── State ────────────────────────────────────────────────────────────────────
const joined = ref(false)
const remoteJoined = ref(false)
const micMuted = ref(false)
const cameraOff = ref(false)
const screenSharing = ref(false)
const chatVisible = ref(false)
const error = ref<string | null>(null)
const browserSupported = ref(true)

// ─── TRTC instances ───────────────────────────────────────────────────────────
let client: any = null
let localStream: any = null
let screenStream: any = null

// ─── Init ─────────────────────────────────────────────────────────────────────
onMounted(async () => {
  // Check browser support
  const support = await TRTC.checkSystemRequirements()
  if (!support.result) {
    browserSupported.value = false
    error.value = t('interviews.room.browserNotSupported')
    return
  }

  await joinRoom()
})

onUnmounted(() => {
  cleanup()
})

async function joinRoom() {
  error.value = null
  try {
    // 1. Create client
    client = TRTC.createClient({
      mode: 'rtc',
      sdkAppId: props.sdkAppId,
      userId: props.userId,
      userSig: props.userSig,
    })

    // 2. Bind events
    client.on('stream-added', (event: any) => {
      client.subscribe(event.stream)
    })

    client.on('stream-subscribed', (event: any) => {
      remoteJoined.value = true
      event.stream.play(remoteVideoRef.value!, { objectFit: 'contain' })
    })

    client.on('stream-removed', () => {
      remoteJoined.value = false
    })

    client.on('peer-leave', () => {
      remoteJoined.value = false
      ElMessage.info(t('interviews.room.participantLeft'))
    })

    client.on('error', (err: any) => {
      console.error('TRTC error:', err)
      error.value = t('interviews.room.connectionError')
    })

    // 3. Join room
    await client.join({ roomId: props.roomId })
    joined.value = true

    // 4. Create and publish local stream
    localStream = TRTC.createStream({
      userId: props.userId,
      audio: true,
      video: true,
    })

    try {
      await localStream.initialize()
    } catch (err: any) {
      if (err.name === 'NotAllowedError') {
        error.value = t('interviews.room.permissionDenied')
      } else if (err.name === 'NotFoundError') {
        error.value = t('interviews.room.noDeviceFound')
      } else {
        error.value = t('interviews.room.connectionError')
      }
      return
    }

    if (localVideoRef.value) {
      localStream.play(localVideoRef.value, { objectFit: 'cover', mirror: true })
    }

    await client.publish(localStream)

  } catch (err: any) {
    console.error('TRTC join error:', err)
    error.value = t('interviews.room.connectionError')
  }
}

// ─── Controls ─────────────────────────────────────────────────────────────────
async function toggleMic() {
  if (!localStream) return
  if (micMuted.value) {
    await localStream.unmuteAudio()
  } else {
    await localStream.muteAudio()
  }
  micMuted.value = !micMuted.value
}

async function toggleCamera() {
  if (!localStream) return
  if (cameraOff.value) {
    await localStream.unmuteVideo()
  } else {
    await localStream.muteVideo()
  }
  cameraOff.value = !cameraOff.value
}

async function toggleScreenShare() {
  if (screenSharing.value) {
    await stopScreenShare()
  } else {
    await startScreenShare()
  }
}

async function startScreenShare() {
  try {
    screenStream = TRTC.createStream({
      userId: props.userId,
      audio: false,
      screen: true,
    })
    await screenStream.initialize()
    await client.publish(screenStream)
    screenSharing.value = true

    // Auto-stop when user ends via browser UI
    screenStream.getVideoTrack().onended = () => {
      stopScreenShare()
    }
  } catch (err: any) {
    if (err.name === 'NotAllowedError') return // user cancelled
    ElMessage.error(t('interviews.room.connectionError'))
  }
}

async function stopScreenShare() {
  if (!screenStream) return
  try {
    await client.unpublish(screenStream)
    screenStream.stop()
    screenStream = null
  } finally {
    screenSharing.value = false
  }
}

async function leaveRoom() {
  cleanup()
  emit('leave')
}

function cleanup() {
  if (screenStream) {
    try { client?.unpublish(screenStream); screenStream.stop() } catch { /* ignore */ }
    screenStream = null
  }
  if (localStream) {
    try { client?.unpublish(localStream); localStream.stop() } catch { /* ignore */ }
    localStream = null
  }
  if (client) {
    try { client.leave() } catch { /* ignore */ }
    client = null
  }
  joined.value = false
}
</script>

<template>
  <div class="trtc-room">
    <!-- Browser not supported -->
    <div v-if="!browserSupported" class="room-error">
      <div class="error-icon">⚠️</div>
      <p class="error-msg">{{ error }}</p>
    </div>

    <!-- Connection / permission error -->
    <div v-else-if="error && !joined" class="room-error">
      <div class="error-icon">⚠️</div>
      <p class="error-msg">{{ error }}</p>
      <button class="ctrl-btn retry-btn" @click="joinRoom">Retry</button>
    </div>

    <!-- Room UI -->
    <template v-else>
      <!-- Remote video (main) -->
      <div class="remote-area">
        <div ref="remoteVideoRef" class="remote-video" />
        <div v-if="!remoteJoined" class="waiting-overlay">
          <div class="waiting-spinner" />
          <p>{{ t('interviews.room.waitingForParticipant') }}</p>
        </div>
      </div>

      <!-- Local video (PiP) -->
      <div class="local-pip">
        <div ref="localVideoRef" class="local-video" />
        <span v-if="cameraOff" class="camera-off-badge">📷</span>
      </div>

      <!-- Error banner (non-fatal) -->
      <div v-if="error && joined" class="error-banner">{{ error }}</div>

      <!-- Controls bar -->
      <div class="controls-bar">
        <!-- Mic toggle -->
        <button
          class="ctrl-btn"
          :class="{ active: !micMuted, muted: micMuted }"
          :title="micMuted ? t('interviews.room.unmute') : t('interviews.room.mute')"
          @click="toggleMic"
        >
          <span class="ctrl-icon">{{ micMuted ? '🔇' : '🎙' }}</span>
          <span class="ctrl-label">{{ micMuted ? t('interviews.room.unmute') : t('interviews.room.mute') }}</span>
        </button>

        <!-- Camera toggle -->
        <button
          class="ctrl-btn"
          :class="{ active: !cameraOff, muted: cameraOff }"
          :title="cameraOff ? t('interviews.room.cameraOn') : t('interviews.room.cameraOff')"
          @click="toggleCamera"
        >
          <span class="ctrl-icon">{{ cameraOff ? '📷' : '📹' }}</span>
          <span class="ctrl-label">{{ cameraOff ? t('interviews.room.cameraOn') : t('interviews.room.cameraOff') }}</span>
        </button>

        <!-- Screen share -->
        <button
          class="ctrl-btn"
          :class="{ active: screenSharing }"
          :title="screenSharing ? t('interviews.room.stopSharing') : t('interviews.room.shareScreen')"
          @click="toggleScreenShare"
        >
          <span class="ctrl-icon">🖥</span>
          <span class="ctrl-label">{{ screenSharing ? t('interviews.room.stopSharing') : t('interviews.room.shareScreen') }}</span>
        </button>

        <!-- Chat toggle -->
        <button
          class="ctrl-btn"
          :class="{ active: chatVisible }"
          :title="t('interviews.room.chat')"
          @click="chatVisible = !chatVisible"
        >
          <span class="ctrl-icon">💬</span>
          <span class="ctrl-label">{{ t('interviews.room.chat') }}</span>
        </button>

        <!-- Leave -->
        <button class="ctrl-btn leave-btn" :title="t('interviews.room.leaveRoom')" @click="leaveRoom">
          <span class="ctrl-icon">📵</span>
          <span class="ctrl-label">{{ t('interviews.room.leaveRoom') }}</span>
        </button>
      </div>

      <!-- Chat panel slot -->
      <div v-if="chatVisible" class="chat-panel">
        <slot name="chat" />
      </div>
    </template>
  </div>
</template>

<style scoped>
.trtc-room {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 480px;
  background: #0d1117;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Remote video area */
.remote-area {
  position: relative;
  flex: 1;
  background: #1a1a2e;
  overflow: hidden;
}
.remote-video {
  width: 100%;
  height: 100%;
}
:deep(.remote-video video) {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.waiting-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  color: rgba(255,255,255,0.7);
  font-size: 14px;
}
.waiting-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255,255,255,0.15);
  border-top-color: #7dd3fc;
  border-radius: 50%;
  animation: spin 0.9s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Local PiP */
.local-pip {
  position: absolute;
  bottom: 80px;
  right: 16px;
  width: 160px;
  height: 100px;
  border-radius: 10px;
  overflow: hidden;
  border: 2px solid rgba(255,255,255,0.2);
  background: #1a1a2e;
  z-index: 10;
  cursor: move;
}
.local-video {
  width: 100%;
  height: 100%;
}
:deep(.local-video video) {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.camera-off-badge {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  background: rgba(0,0,0,0.6);
}

/* Error states */
.room-error {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  color: #fff;
  text-align: center;
  padding: 40px;
}
.error-icon { font-size: 48px; }
.error-msg { font-size: 14px; color: rgba(255,255,255,0.7); max-width: 360px; line-height: 1.6; }

.error-banner {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  background: rgba(220,53,69,0.85);
  color: #fff;
  padding: 8px 16px;
  font-size: 13px;
  text-align: center;
  z-index: 20;
}

/* Controls bar */
.controls-bar {
  height: 72px;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 16px;
  flex-shrink: 0;
}

.ctrl-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 8px 12px;
  border: none;
  border-radius: 8px;
  background: rgba(255,255,255,0.08);
  color: rgba(255,255,255,0.85);
  cursor: pointer;
  transition: background 0.15s;
  min-width: 64px;
}
.ctrl-btn:hover { background: rgba(255,255,255,0.14); }
.ctrl-btn.active { background: rgba(100,160,255,0.18); }
.ctrl-btn.muted { background: rgba(220,53,69,0.25); }
.ctrl-icon { font-size: 20px; line-height: 1; }
.ctrl-label { font-size: 10px; white-space: nowrap; }

.leave-btn { background: rgba(220,53,69,0.3); }
.leave-btn:hover { background: rgba(220,53,69,0.5); }
.retry-btn { background: rgba(100,160,255,0.25); color: #fff; font-size: 13px; padding: 8px 20px; border-radius: 8px; cursor: pointer; border: none; }

/* Chat panel */
.chat-panel {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 72px;
  width: 300px;
  background: rgba(15, 20, 30, 0.95);
  border-left: 1px solid rgba(255,255,255,0.08);
  z-index: 15;
  display: flex;
  flex-direction: column;
}
</style>
