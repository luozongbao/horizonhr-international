<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import studentApi from '@/api/student'

const { t } = useI18n()
const router = useRouter()

// ─── Types ────────────────────────────────────────────────────────────────────
interface Interview {
  id: number
  job_title: string
  company_name: string
  company_logo?: string
  scheduled_at: string
  duration_minutes: number
  status: 'scheduled' | 'ongoing' | 'completed' | 'cancelled'
  interviewer_name?: string
  preparation_notes?: string
}

// ─── State ────────────────────────────────────────────────────────────────────
const loading = ref(true)
const interviews = ref<Interview[]>([])
const activeTab = ref('all')
const detailVisible = ref(false)
const selectedInterview = ref<Interview | null>(null)
const now = ref(Date.now())

const tabs = ['all', 'scheduled', 'ongoing', 'completed', 'cancelled']

// ─── Polling ──────────────────────────────────────────────────────────────────
let tickTimer: ReturnType<typeof setInterval>
let pollTimer: ReturnType<typeof setInterval>

onMounted(() => {
  fetchInterviews()
  // Update countdown every second
  tickTimer = setInterval(() => { now.value = Date.now() }, 1000)
  // Re-fetch every 30s to catch status changes
  pollTimer = setInterval(fetchInterviews, 30_000)
})

onUnmounted(() => {
  clearInterval(tickTimer)
  clearInterval(pollTimer)
})

// ─── Fetch ────────────────────────────────────────────────────────────────────
async function fetchInterviews() {
  if (!loading.value) loading.value = false // silent refresh after first load
  try {
    const params = activeTab.value !== 'all' ? { status: activeTab.value } : {}
    const { data } = await studentApi.getInterviews(params)
    interviews.value = (data.data ?? data) as Interview[]
  } finally {
    loading.value = false
  }
}

// Initial load with spinner
loading.value = true

function onTabChange(tab: string) {
  activeTab.value = tab
  loading.value = true
  fetchInterviews()
}

// ─── Tab counts ───────────────────────────────────────────────────────────────
const counts = computed(() => {
  const all = interviews.value
  return {
    all:       all.length,
    scheduled: all.filter((i) => i.status === 'scheduled').length,
    ongoing:   all.filter((i) => i.status === 'ongoing').length,
    completed: all.filter((i) => i.status === 'completed').length,
    cancelled: all.filter((i) => i.status === 'cancelled').length,
  }
})

// ─── Join button logic ────────────────────────────────────────────────────────
function canJoin(iv: Interview): boolean {
  if (iv.status === 'ongoing') return true
  if (iv.status !== 'scheduled') return false
  const scheduledMs = new Date(iv.scheduled_at).getTime()
  const diffMs = scheduledMs - now.value
  return diffMs <= 15 * 60 * 1000 // within 15 minutes
}

// ─── Countdown ────────────────────────────────────────────────────────────────
function countdown(iv: Interview): string {
  if (iv.status === 'ongoing') return t('interviews.countdownNow')
  const scheduledMs = new Date(iv.scheduled_at).getTime()
  const diffMs = scheduledMs - now.value
  if (diffMs <= 0) return t('interviews.countdownNow')
  const totalSec = Math.floor(diffMs / 1000)
  const days  = Math.floor(totalSec / 86400)
  const hours = Math.floor((totalSec % 86400) / 3600)
  const mins  = Math.floor((totalSec % 3600) / 60)
  const secs  = totalSec % 60

  let parts = ''
  if (days > 0)        parts = `${days}d ${hours}h`
  else if (hours > 0)  parts = `${hours}h ${mins}m`
  else if (mins > 0)   parts = `${mins}m ${secs}s`
  else                 parts = `${secs}s`
  return t('interviews.countdownIn', { time: parts })
}

// ─── Status helpers ───────────────────────────────────────────────────────────
const statusTypeMap: Record<string, string> = {
  scheduled: 'primary',
  ongoing:   'success',
  completed: 'info',
  cancelled: 'danger',
}
function statusTagType(status: string) {
  return statusTypeMap[status] ?? 'info'
}

// ─── Detail modal ─────────────────────────────────────────────────────────────
function openDetail(iv: Interview) {
  selectedInterview.value = iv
  detailVisible.value = true
}

// ─── Format helpers ───────────────────────────────────────────────────────────
function formatDate(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}
function formatDateTime(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleString(undefined, {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}
</script>

<template>
  <div class="interviews-page">
    <h1 class="page-heading">{{ t('interviews.pageTitle') }}</h1>

    <!-- Status tabs -->
    <div class="status-tabs">
      <button
        v-for="tab in tabs"
        :key="tab"
        class="tab-btn"
        :class="{ active: activeTab === tab }"
        @click="onTabChange(tab)"
      >
        {{ tab === 'all' ? t('interviews.allStatuses') : t(`interviews.status.${tab}`) }}
        <span v-if="counts[tab as keyof typeof counts] > 0" class="tab-count">
          {{ counts[tab as keyof typeof counts] }}
        </span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="interview-list">
      <div v-for="i in 3" :key="i">
        <el-skeleton animated>
          <template #template>
            <el-skeleton-item variant="rect" style="height: 140px; border-radius: 12px;" />
          </template>
        </el-skeleton>
      </div>
    </div>

    <!-- Interview cards -->
    <div v-else-if="interviews.length" class="interview-list">
      <div
        v-for="iv in interviews"
        :key="iv.id"
        class="interview-card"
        :class="{ 'is-ongoing': iv.status === 'ongoing' }"
      >
        <!-- Card header -->
        <div class="card-header">
          <div class="company-logo">
            <img v-if="iv.company_logo" :src="iv.company_logo" :alt="iv.company_name" />
            <span v-else>{{ iv.company_name.charAt(0) }}</span>
          </div>
          <div class="card-info">
            <p class="card-title">{{ iv.job_title }}</p>
            <p class="card-company">{{ iv.company_name }}</p>
          </div>
          <el-tag
            :type="statusTagType(iv.status)"
            :class="{ 'pulse-tag': iv.status === 'ongoing' }"
          >
            {{ t(`interviews.status.${iv.status}`) }}
          </el-tag>
        </div>

        <!-- Details grid -->
        <div class="card-details">
          <div class="detail-item">
            <span class="detail-label">{{ t('interviews.scheduledAt') }}</span>
            <span class="detail-value">{{ formatDateTime(iv.scheduled_at) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">{{ t('interviews.duration') }}</span>
            <span class="detail-value">{{ iv.duration_minutes ?? '—' }} min</span>
          </div>
          <div v-if="iv.interviewer_name" class="detail-item">
            <span class="detail-label">{{ t('interviews.interviewer') }}</span>
            <span class="detail-value">{{ iv.interviewer_name }}</span>
          </div>
          <!-- Countdown for scheduled -->
          <div v-if="iv.status === 'scheduled' || iv.status === 'ongoing'" class="detail-item">
            <span class="detail-label">Countdown</span>
            <span
              class="detail-value countdown"
              :class="{ 'soon': canJoin(iv) }"
            >{{ countdown(iv) }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="card-actions">
          <el-button
            type="primary"
            :disabled="!canJoin(iv)"
            @click="router.push(`/student/interviews/${iv.id}`)"
          >
            <el-icon class="el-icon--left"><VideoCamera /></el-icon>
            {{ t('interviews.joinInterview') }}
          </el-button>
          <el-button plain @click="openDetail(iv)">
            <el-icon class="el-icon--left"><InfoFilled /></el-icon>
            {{ t('interviews.viewDetails') }}
          </el-button>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="empty-state">
      <el-icon class="empty-icon"><VideoCamera /></el-icon>
      <p class="empty-title">{{ t('interviews.noInterviews') }}</p>
    </div>

    <!-- Detail modal -->
    <el-dialog
      v-model="detailVisible"
      :title="t('interviews.viewDetails')"
      width="520px"
      align-center
    >
      <div v-if="selectedInterview" class="detail-modal">
        <!-- Header -->
        <div class="modal-header">
          <div class="modal-logo">
            <img v-if="selectedInterview.company_logo" :src="selectedInterview.company_logo" :alt="selectedInterview.company_name" />
            <span v-else>{{ selectedInterview.company_name.charAt(0) }}</span>
          </div>
          <div>
            <p class="modal-title">{{ selectedInterview.job_title }}</p>
            <p class="modal-company">{{ selectedInterview.company_name }}</p>
          </div>
          <el-tag :type="statusTagType(selectedInterview.status)">
            {{ t(`interviews.status.${selectedInterview.status}`) }}
          </el-tag>
        </div>

        <!-- Info rows -->
        <div class="modal-info-grid">
          <div class="modal-info-item">
            <span class="info-label">{{ t('interviews.scheduledAt') }}</span>
            <span class="info-value">{{ formatDateTime(selectedInterview.scheduled_at) }}</span>
          </div>
          <div class="modal-info-item">
            <span class="info-label">{{ t('interviews.duration') }}</span>
            <span class="info-value">{{ selectedInterview.duration_minutes ?? '—' }} min</span>
          </div>
          <div v-if="selectedInterview.interviewer_name" class="modal-info-item">
            <span class="info-label">{{ t('interviews.interviewer') }}</span>
            <span class="info-value">{{ selectedInterview.interviewer_name }}</span>
          </div>
        </div>

        <!-- Preparation notes -->
        <div v-if="selectedInterview.preparation_notes" class="modal-notes">
          <p class="notes-label">{{ t('interviews.preparation') }}</p>
          <p class="notes-text">{{ selectedInterview.preparation_notes }}</p>
        </div>
      </div>

      <template #footer>
        <el-button @click="detailVisible = false">Close</el-button>
        <el-button
          v-if="selectedInterview && canJoin(selectedInterview)"
          type="primary"
          @click="detailVisible = false; router.push(`/student/interviews/${selectedInterview?.id}`)"
        >
          {{ t('interviews.joinInterview') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.interviews-page {
  padding: 8px 0;
}
.page-heading {
  font-size: 22px;
  font-weight: 700;
  color: #003366;
  margin: 0 0 20px;
}

/* Tabs */
.status-tabs {
  display: flex;
  gap: 0;
  border-bottom: 2px solid #dee2e6;
  margin-bottom: 20px;
  overflow-x: auto;
}
.tab-btn {
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 500;
  color: #6c757d;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  cursor: pointer;
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: color 0.2s, border-color 0.2s;
}
.tab-btn:hover { color: #003366; }
.tab-btn.active { color: #003366; border-bottom-color: #003366; }
.tab-count {
  background: #e6f0ff;
  color: #003366;
  font-size: 11px;
  padding: 1px 7px;
  border-radius: 10px;
  font-weight: 600;
}

/* Interview list */
.interview-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.interview-card {
  background: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 12px;
  overflow: hidden;
  transition: border-color 0.2s;
}
.interview-card:hover { border-color: #0066cc; }
.interview-card.is-ongoing { border-color: #52c41a; }

.card-header {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 20px;
  border-bottom: 1px solid #f0f0f0;
}
.interview-card.is-ongoing .card-header {
  background: linear-gradient(135deg, #003366 0%, #0055a4 100%);
  color: #fff;
}
.company-logo {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  background: #e6f0ff;
  color: #003366;
  font-size: 18px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.is-ongoing .company-logo { background: rgba(255,255,255,0.2); color: #fff; }
.company-logo img { width: 100%; height: 100%; object-fit: cover; }
.card-info { flex: 1; min-width: 0; }
.card-title { margin: 0 0 2px; font-size: 15px; font-weight: 600; }
.card-company { margin: 0; font-size: 13px; color: #6c757d; }
.is-ongoing .card-company { color: rgba(255,255,255,0.75); }

.pulse-tag { animation: pulse 2s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.65; } }

.card-details {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 16px;
  padding: 16px 20px;
}
.detail-item { display: flex; flex-direction: column; gap: 3px; }
.detail-label { font-size: 11px; text-transform: uppercase; color: #6c757d; letter-spacing: 0.05em; }
.detail-value { font-size: 14px; font-weight: 500; color: #1a1a2e; }
.countdown { color: #6c757d; font-size: 13px; font-weight: 400; }
.countdown.soon { color: #e6a817; font-weight: 600; }

.card-actions {
  display: flex;
  gap: 10px;
  padding: 12px 20px;
  background: #fafbfc;
  border-top: 1px solid #f0f0f0;
  flex-wrap: wrap;
}

/* Empty state */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 64px 32px;
  gap: 16px;
  background: #fff;
  border-radius: 12px;
  border: 1px solid #dee2e6;
}
.empty-icon { font-size: 56px; color: #dee2e6; }
.empty-title { font-size: 16px; color: #6c757d; margin: 0; }

/* Detail modal */
.detail-modal { display: flex; flex-direction: column; gap: 20px; }
.modal-header {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}
.modal-logo {
  width: 52px;
  height: 52px;
  border-radius: 8px;
  background: #e6f0ff;
  color: #003366;
  font-size: 20px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.modal-logo img { width: 100%; height: 100%; object-fit: cover; }
.modal-title { margin: 0 0 3px; font-size: 16px; font-weight: 600; color: #1a1a2e; }
.modal-company { margin: 0; font-size: 13px; color: #6c757d; }

.modal-info-grid { display: flex; flex-direction: column; gap: 12px; }
.modal-info-item { display: flex; justify-content: space-between; padding: 10px 14px; background: #f8f9fa; border-radius: 8px; }
.info-label { font-size: 13px; color: #6c757d; }
.info-value { font-size: 13px; font-weight: 500; color: #1a1a2e; }

.modal-notes { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 16px; }
.notes-label { font-size: 12px; font-weight: 600; color: #92400e; margin: 0 0 6px; text-transform: uppercase; }
.notes-text { font-size: 14px; color: #1a1a2e; margin: 0; line-height: 1.6; }
</style>

