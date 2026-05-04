<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import Hls from 'hls.js'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = withDefaults(defineProps<{
  src: string
  autoplay?: boolean
  isLive?: boolean
  poster?: string
  enableSpeedControl?: boolean
}>(), {
  autoplay: false,
  isLive: false,
  enableSpeedControl: false,
})

const emit = defineEmits<{
  (e: 'ready'): void
  (e: 'error', msg: string): void
  (e: 'retry'): void
}>()

const videoRef = ref<HTMLVideoElement | null>(null)
let hls: Hls | null = null

const currentSpeed = ref(1)
const speeds = [0.5, 1, 1.25, 1.5, 2]
const volume = ref(1)
const isMuted = ref(false)
const isFullscreen = ref(false)
const hasError = ref(false)
const errorMsg = ref('')

const volumeIcon = computed(() => {
  if (isMuted.value || volume.value === 0) return '🔇'
  if (volume.value < 0.4) return '🔉'
  return '🔊'
})

function loadSource(src: string) {
  const video = videoRef.value
  if (!video) return

  hasError.value = false
  errorMsg.value = ''

  if (hls) {
    hls.destroy()
    hls = null
  }

  const hlsConfig: Partial<Hls['config']> = { enableWorker: true }
  if (props.isLive) {
    Object.assign(hlsConfig, {
      lowLatencyMode: true,
      liveSyncDurationCount: 3,
      maxLoadingDelay: 4,
      liveMaxLatencyDurationCount: 6,
    })
  }

  if (Hls.isSupported()) {
    hls = new Hls(hlsConfig)
    hls.loadSource(src)
    hls.attachMedia(video)
    hls.on(Hls.Events.MANIFEST_PARSED, () => {
      video.volume = volume.value
      video.muted = isMuted.value
      if (props.autoplay) video.play().catch(() => {})
      emit('ready')
    })
    hls.on(Hls.Events.ERROR, (_event, data) => {
      if (data.fatal) {
        hasError.value = true
        errorMsg.value = data.details ?? 'HLS error'
        emit('error', errorMsg.value)
      }
    })
  } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    // Safari native HLS
    video.src = src
    video.addEventListener('loadedmetadata', () => {
      video.volume = volume.value
      video.muted = isMuted.value
      if (props.autoplay) video.play().catch(() => {})
      emit('ready')
    }, { once: true })
    video.addEventListener('error', () => {
      hasError.value = true
      errorMsg.value = 'Video load error'
      emit('error', errorMsg.value)
    }, { once: true })
  } else {
    hasError.value = true
    errorMsg.value = 'HLS not supported'
    emit('error', errorMsg.value)
  }
}

function setSpeed(speed: number) {
  currentSpeed.value = speed
  if (videoRef.value) videoRef.value.playbackRate = speed
}

function setVolume(val: number) {
  volume.value = val
  if (videoRef.value) {
    videoRef.value.volume = val
    if (val === 0) {
      isMuted.value = true
      videoRef.value.muted = true
    } else {
      isMuted.value = false
      videoRef.value.muted = false
    }
  }
}

function toggleMute() {
  isMuted.value = !isMuted.value
  if (videoRef.value) videoRef.value.muted = isMuted.value
}

function toggleFullscreen() {
  const container = videoRef.value?.closest('.video-player-wrap') as HTMLElement | null
  if (!container) return
  if (!document.fullscreenElement) {
    container.requestFullscreen().catch(() => {})
  } else {
    document.exitFullscreen().catch(() => {})
  }
}

function onRetry() {
  emit('retry')
  if (props.src) loadSource(props.src)
}

function onFullscreenChange() {
  isFullscreen.value = !!document.fullscreenElement
}

watch(() => props.src, (newSrc) => {
  if (newSrc) loadSource(newSrc)
})

onMounted(() => {
  document.addEventListener('fullscreenchange', onFullscreenChange)
  if (props.src) loadSource(props.src)
})

onBeforeUnmount(() => {
  document.removeEventListener('fullscreenchange', onFullscreenChange)
  if (hls) {
    hls.destroy()
    hls = null
  }
})

defineExpose({ videoRef })
</script>

<template>
  <div class="video-player-wrap">
    <!-- LIVE badge -->
    <div v-if="isLive" class="live-badge">
      <span class="live-dot" />
      {{ t('seminar.liveNow') }}
    </div>

    <!-- Poster / waiting state when no src yet -->
    <video
      ref="videoRef"
      class="video-el"
      :poster="poster"
      playsinline
    />

    <!-- Error overlay -->
    <div v-if="hasError" class="error-overlay">
      <p class="error-msg">{{ t('seminarWatch.errorOccurred') }}</p>
      <button class="retry-btn" @click="onRetry">{{ t('seminarWatch.retry') }}</button>
    </div>

    <!-- Custom controls bar -->
    <div class="controls-bar">
      <!-- Volume -->
      <div class="ctrl-group">
        <button class="ctrl-btn" :title="isMuted ? t('seminarWatch.unmute') : t('seminarWatch.mute')" @click="toggleMute">
          {{ volumeIcon }}
        </button>
        <input
          type="range"
          class="volume-slider"
          min="0"
          max="1"
          step="0.05"
          :value="isMuted ? 0 : volume"
          @input="setVolume(+($event.target as HTMLInputElement).value)"
        />
      </div>

      <!-- Speed selector (recording only) -->
      <div v-if="!isLive && enableSpeedControl" class="ctrl-group speed-group">
        <label class="ctrl-label">{{ t('seminarWatch.speed') }}</label>
        <select class="speed-select" :value="currentSpeed" @change="setSpeed(+($event.target as HTMLSelectElement).value)">
          <option v-for="s in speeds" :key="s" :value="s">{{ s }}x</option>
        </select>
      </div>

      <div class="ctrl-spacer" />

      <!-- Fullscreen -->
      <button
        class="ctrl-btn"
        :title="isFullscreen ? t('seminarWatch.exitFullscreen') : t('seminarWatch.fullscreen')"
        @click="toggleFullscreen"
      >
        {{ isFullscreen ? '⛶' : '⛶' }}
        <svg v-if="!isFullscreen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/></svg>
      </button>
    </div>
  </div>
</template>

<style scoped>
.video-player-wrap {
  position: relative;
  width: 100%;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.video-el {
  width: 100%;
  display: block;
  max-height: 70vh;
  background: #000;
}
/* LIVE badge */
.live-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 10;
  display: flex;
  align-items: center;
  gap: 5px;
  background: #e53e3e;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
  padding: 3px 8px;
  border-radius: 4px;
  pointer-events: none;
}
.live-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #fff;
  animation: live-pulse 1.2s ease-in-out infinite;
}
@keyframes live-pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}
/* Error overlay */
.error-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.75);
  z-index: 20;
  gap: 12px;
}
.error-msg {
  color: #fff;
  font-size: 14px;
  text-align: center;
  margin: 0;
}
.retry-btn {
  background: #3e8ed0;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 8px 20px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.15s;
}
.retry-btn:hover {
  background: #2d6faa;
}
/* Custom controls bar */
.controls-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(0, 0, 0, 0.7);
  padding: 6px 12px;
  flex-shrink: 0;
}
.ctrl-group {
  display: flex;
  align-items: center;
  gap: 6px;
}
.ctrl-btn {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.85);
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 2px 4px;
  border-radius: 4px;
  font-size: 16px;
  line-height: 1;
  transition: color 0.15s;
}
.ctrl-btn:hover {
  color: #fff;
}
.volume-slider {
  width: 72px;
  accent-color: #3e8ed0;
  cursor: pointer;
}
.ctrl-label {
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
}
.speed-select {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 4px;
  padding: 2px 6px;
  font-size: 12px;
  cursor: pointer;
}
.speed-select option {
  background: #222;
}
.ctrl-spacer {
  flex: 1;
}
</style>

