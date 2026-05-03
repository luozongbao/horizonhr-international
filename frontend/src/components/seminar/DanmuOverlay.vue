<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'
import { useAuthStore } from '@/stores/auth'

const props = defineProps<{
  seminarId: number
  live?: boolean
}>()

const { t } = useI18n()
const auth = useAuthStore()

/* ─── Types ──────────────────────────────────── */
interface DanmuItem {
  id: number
  message: string
  user_name?: string
  created_at?: string
}

interface FloatingDanmu extends DanmuItem {
  uid: number
  top: number   // percentage
  color: string
  duration: number // seconds
}

/* ─── State ──────────────────────────────────── */
const danmuItems = ref<FloatingDanmu[]>([])
const inputText = ref('')
const sending = ref(false)
const lastId = ref(0)
let pollTimer: ReturnType<typeof setInterval> | null = null
let uidCounter = 0

const colors = [
  '#ffffff', '#ffdd57', '#48c78e', '#3e8ed0',
  '#ff6b6b', '#f5a623', '#bd93f9', '#50fa7b',
]

/* ─── Danmu lifecycle ────────────────────────── */
function spawnDanmu(item: DanmuItem) {
  const floating: FloatingDanmu = {
    ...item,
    uid: ++uidCounter,
    top: 10 + Math.random() * 70,
    color: colors[Math.floor(Math.random() * colors.length)],
    duration: 7 + Math.random() * 5,
  }
  danmuItems.value.push(floating)

  // Remove after animation ends
  setTimeout(() => {
    danmuItems.value = danmuItems.value.filter(d => d.uid !== floating.uid)
  }, (floating.duration + 0.5) * 1000)
}

async function poll() {
  try {
    const res = await publicApi.getSeminarDanmu(props.seminarId, lastId.value || undefined)
    const items: DanmuItem[] = res.data?.data ?? []
    if (items.length > 0) {
      lastId.value = items[items.length - 1].id
      items.forEach(spawnDanmu)
    }
  } catch {
    // silently ignore poll errors
  }
}

async function sendDanmu() {
  const msg = inputText.value.trim()
  if (!msg || sending.value) return

  sending.value = true
  try {
    await publicApi.sendSeminarDanmu(props.seminarId, msg)
    // Immediately show own danmu
    spawnDanmu({ id: ++uidCounter, message: msg, user_name: auth.user?.name })
    inputText.value = ''
  } catch {
    // ignore
  } finally {
    sending.value = false
  }
}

function handleKey(e: KeyboardEvent) {
  if (e.key === 'Enter') sendDanmu()
}

onMounted(() => {
  // Initial load
  poll()
  if (props.live) {
    pollTimer = setInterval(poll, 3000)
  }
})

onBeforeUnmount(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>

<template>
  <div class="danmu-overlay">
    <!-- Floating comments -->
    <div
      v-for="d in danmuItems"
      :key="d.uid"
      class="danmu-item"
      :style="{
        top: d.top + '%',
        color: d.color,
        animationDuration: d.duration + 's',
      }"
    >
      <span v-if="d.user_name" class="danmu-user">{{ d.user_name }}: </span>{{ d.message }}
    </div>

    <!-- Input bar -->
    <div v-if="live" class="danmu-input-bar">
      <template v-if="auth.isLoggedIn">
        <input
          v-model="inputText"
          class="danmu-input"
          :placeholder="t('seminar.danmuPlaceholder')"
          maxlength="100"
          @keydown="handleKey"
        />
        <button class="danmu-send-btn" :disabled="sending || !inputText.trim()" @click="sendDanmu">
          {{ t('seminar.danmuSend') }}
        </button>
      </template>
      <template v-else>
        <router-link to="/login" class="danmu-login-hint">
          {{ t('seminar.loginToComment') }}
        </router-link>
      </template>
    </div>
  </div>
</template>

<style scoped>
.danmu-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
}

.danmu-item {
  position: absolute;
  right: -100%;
  white-space: nowrap;
  font-size: 18px;
  font-weight: 600;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.8), -1px -1px 2px rgba(0,0,0,0.8);
  animation: danmu-fly linear forwards;
  pointer-events: none;
}

@keyframes danmu-fly {
  from { transform: translateX(0); }
  to   { transform: translateX(calc(-100vw - 200%)); }
}

.danmu-user {
  font-size: 13px;
  opacity: 0.8;
  margin-right: 2px;
}

/* Input bar — pointer-events on, positioned at bottom */
.danmu-input-bar {
  position: absolute;
  bottom: 60px;
  left: 0;
  right: 0;
  padding: 0 16px;
  display: flex;
  gap: 8px;
  pointer-events: all;
}
.danmu-input {
  flex: 1;
  padding: 8px 12px;
  background: rgba(0,0,0,0.55);
  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 6px;
  color: #fff;
  font-size: 14px;
  outline: none;
}
.danmu-input::placeholder { color: rgba(255,255,255,0.5); }
.danmu-input:focus { border-color: rgba(255,255,255,0.7); }
.danmu-send-btn {
  padding: 8px 18px;
  background: #003366;
  border: none;
  border-radius: 6px;
  color: #fff;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.15s;
}
.danmu-send-btn:hover:not(:disabled) { background: #0055aa; }
.danmu-send-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.danmu-login-hint {
  color: rgba(255,255,255,0.7);
  font-size: 13px;
  pointer-events: all;
  text-decoration: underline;
}
</style>
