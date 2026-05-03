<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import Hls from 'hls.js'

const props = withDefaults(defineProps<{
  src: string
  autoplay?: boolean
  controls?: boolean
  enableSpeedControl?: boolean
}>(), {
  autoplay: false,
  controls: true,
  enableSpeedControl: false,
})

const emit = defineEmits<{
  (e: 'ready'): void
  (e: 'error', msg: string): void
}>()

const videoRef = ref<HTMLVideoElement | null>(null)
let hls: Hls | null = null

const currentSpeed = ref(1)
const speeds = [0.5, 1, 1.25, 1.5, 2]

function loadSource(src: string) {
  const video = videoRef.value
  if (!video) return

  // Destroy existing hls instance
  if (hls) {
    hls.destroy()
    hls = null
  }

  if (Hls.isSupported()) {
    hls = new Hls({ enableWorker: true })
    hls.loadSource(src)
    hls.attachMedia(video)
    hls.on(Hls.Events.MANIFEST_PARSED, () => {
      if (props.autoplay) video.play().catch(() => {/* autoplay blocked */})
      emit('ready')
    })
    hls.on(Hls.Events.ERROR, (_event, data) => {
      if (data.fatal) {
        emit('error', data.details ?? 'HLS error')
      }
    })
  } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    // Safari native HLS
    video.src = src
    video.addEventListener('loadedmetadata', () => {
      if (props.autoplay) video.play().catch(() => {/* autoplay blocked */})
      emit('ready')
    }, { once: true })
  } else {
    emit('error', 'HLS is not supported in this browser.')
  }
}

function setSpeed(speed: number) {
  currentSpeed.value = speed
  if (videoRef.value) {
    videoRef.value.playbackRate = speed
  }
}

watch(() => props.src, (newSrc) => {
  if (newSrc) loadSource(newSrc)
})

onMounted(() => {
  if (props.src) loadSource(props.src)
})

onBeforeUnmount(() => {
  if (hls) {
    hls.destroy()
    hls = null
  }
})

defineExpose({ videoRef })
</script>

<template>
  <div class="video-player-wrap">
    <video
      ref="videoRef"
      class="video-el"
      :controls="controls"
      playsinline
    />
    <!-- Speed control overlay -->
    <div v-if="enableSpeedControl" class="speed-control">
      <button
        v-for="s in speeds"
        :key="s"
        class="speed-btn"
        :class="{ active: currentSpeed === s }"
        @click="setSpeed(s)"
      >
        {{ s }}x
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
}
.video-el {
  width: 100%;
  display: block;
  max-height: 70vh;
  background: #000;
}
.speed-control {
  position: absolute;
  bottom: 56px;
  right: 12px;
  display: flex;
  gap: 4px;
  background: rgba(0,0,0,0.6);
  border-radius: 6px;
  padding: 4px 6px;
}
.speed-btn {
  background: transparent;
  border: 1px solid rgba(255,255,255,0.3);
  color: rgba(255,255,255,0.75);
  border-radius: 4px;
  padding: 2px 8px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.15s;
}
.speed-btn:hover, .speed-btn.active {
  background: rgba(255,255,255,0.2);
  color: #fff;
  border-color: #fff;
}
</style>
