<script setup lang="ts">
import { ref, watch, nextTick, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps<{
  interviewId: number
  fetchMessages: (id: number) => Promise<any>
  sendMessage: (id: number, content: string) => Promise<any>
  currentUserId?: string
}>()

// ─── Types ────────────────────────────────────────────────────────────────────
interface ChatMessage {
  id: number
  sender_id: string | number
  sender_name: string
  content: string
  created_at: string
}

// ─── State ────────────────────────────────────────────────────────────────────
const messages = ref<ChatMessage[]>([])
const newMessage = ref('')
const sending = ref(false)
const listRef = ref<HTMLDivElement | null>(null)

// ─── Polling ──────────────────────────────────────────────────────────────────
let pollTimer: ReturnType<typeof setInterval> | null = null

async function loadMessages() {
  try {
    const { data } = await props.fetchMessages(props.interviewId)
    messages.value = data.data ?? data
    scrollToBottom()
  } catch { /* silent fail */ }
}

function startPolling() {
  loadMessages()
  pollTimer = setInterval(loadMessages, 2000)
}

function stopPolling() {
  if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
}

watch(() => props.interviewId, (id) => {
  if (id) startPolling()
}, { immediate: true })

onUnmounted(stopPolling)

// ─── Send ─────────────────────────────────────────────────────────────────────
async function send() {
  const text = newMessage.value.trim()
  if (!text || sending.value) return
  sending.value = true
  try {
    await props.sendMessage(props.interviewId, text)
    newMessage.value = ''
    await loadMessages()
  } finally {
    sending.value = false
  }
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault()
    send()
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
async function scrollToBottom() {
  await nextTick()
  if (listRef.value) {
    listRef.value.scrollTop = listRef.value.scrollHeight
  }
}

function formatTime(dt: string): string {
  if (!dt) return ''
  return new Date(dt).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })
}

function isOwn(msg: ChatMessage): boolean {
  return String(msg.sender_id) === String(props.currentUserId)
}
</script>

<template>
  <div class="text-chat">
    <!-- Header -->
    <div class="chat-header">
      <span class="chat-title">{{ t('interviews.room.chat') }}</span>
    </div>

    <!-- Message list -->
    <div ref="listRef" class="message-list">
      <div v-if="messages.length === 0" class="no-messages">
        {{ t('interviews.room.noMessages') }}
      </div>
      <div
        v-for="msg in messages"
        :key="msg.id"
        class="message-row"
        :class="{ own: isOwn(msg) }"
      >
        <div class="message-bubble">
          <div class="message-meta">
            <span class="sender-name">{{ msg.sender_name }}</span>
            <span class="message-time">{{ formatTime(msg.created_at) }}</span>
          </div>
          <div class="message-content">{{ msg.content }}</div>
        </div>
      </div>
    </div>

    <!-- Input bar -->
    <div class="chat-input-bar">
      <textarea
        v-model="newMessage"
        class="chat-input"
        :placeholder="t('interviews.room.messagePlaceholder')"
        rows="2"
        :disabled="sending"
        @keydown="onKeydown"
      />
      <button
        class="send-btn"
        :disabled="!newMessage.trim() || sending"
        @click="send"
      >
        {{ t('interviews.room.sendMessage') }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.text-chat {
  display: flex;
  flex-direction: column;
  height: 100%;
  color: #fff;
}

.chat-header {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  flex-shrink: 0;
}
.chat-title {
  font-size: 13px;
  font-weight: 600;
  color: rgba(255,255,255,0.85);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.message-list {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.no-messages {
  text-align: center;
  font-size: 12px;
  color: rgba(255,255,255,0.35);
  margin-top: 32px;
}

.message-row { display: flex; }
.message-row.own { justify-content: flex-end; }

.message-bubble {
  max-width: 75%;
  background: rgba(255,255,255,0.07);
  border-radius: 8px;
  padding: 8px 10px;
}
.message-row.own .message-bubble {
  background: rgba(100,160,255,0.2);
}

.message-meta {
  display: flex;
  align-items: baseline;
  gap: 6px;
  margin-bottom: 3px;
}
.sender-name { font-size: 11px; font-weight: 600; color: rgba(255,255,255,0.6); }
.message-time { font-size: 10px; color: rgba(255,255,255,0.35); }
.message-content { font-size: 13px; color: rgba(255,255,255,0.9); line-height: 1.5; word-break: break-word; }

/* Input bar */
.chat-input-bar {
  display: flex;
  gap: 8px;
  padding: 10px 12px;
  border-top: 1px solid rgba(255,255,255,0.08);
  flex-shrink: 0;
}

.chat-input {
  flex: 1;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 6px;
  color: #fff;
  font-size: 13px;
  padding: 8px 10px;
  resize: none;
  outline: none;
  transition: border-color 0.15s;
  font-family: inherit;
}
.chat-input::placeholder { color: rgba(255,255,255,0.3); }
.chat-input:focus { border-color: rgba(100,160,255,0.5); }

.send-btn {
  background: rgba(100,160,255,0.25);
  border: none;
  border-radius: 6px;
  color: #7dd3fc;
  font-size: 12px;
  font-weight: 600;
  padding: 0 12px;
  cursor: pointer;
  transition: background 0.15s;
  white-space: nowrap;
}
.send-btn:hover:not(:disabled) { background: rgba(100,160,255,0.4); }
.send-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Scrollbar */
.message-list::-webkit-scrollbar { width: 4px; }
.message-list::-webkit-scrollbar-track { background: transparent; }
.message-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }
</style>
