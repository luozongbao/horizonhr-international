<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import enterpriseApi from '@/api/enterprise'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

// ─── Types ────────────────────────────────────────────────────────────────────
interface Interview {
  id: number
  title?: string
  student_name?: string
  student_id?: number
  job_title?: string
  scheduled_at: string
  duration_minutes: number
  status: 'scheduled' | 'ongoing' | 'completed' | 'cancelled'
  interviewer_name?: string
  notes?: string
  result?: 'pass' | 'fail' | 'pending'
  result_notes?: string
}

// ─── State ────────────────────────────────────────────────────────────────────
const loading = ref(true)
const interviews = ref<Interview[]>([])
const activeTab = ref('all')
const now = ref(Date.now())
const tabs = ['all', 'scheduled', 'ongoing', 'completed', 'cancelled']

// Schedule / edit dialog
const scheduleDialogVisible = ref(false)
const scheduleLoading = ref(false)
const editingId = ref<number | null>(null)
const scheduleForm = ref({
  title: '',
  student_id: undefined as number | undefined,
  scheduled_at: '',
  duration_minutes: 60,
  interviewer_name: '',
  notes: '',
})

// Complete dialog
const completeDialogVisible = ref(false)
const completeLoading = ref(false)
const completingId = ref<number | null>(null)
const completeForm = ref({
  result: 'pending' as 'pass' | 'fail' | 'pending',
  result_notes: '',
})

// ─── Polling ──────────────────────────────────────────────────────────────────
let tickTimer: ReturnType<typeof setInterval>
let pollTimer: ReturnType<typeof setInterval>

onMounted(() => {
  fetchInterviews()
  tickTimer = setInterval(() => { now.value = Date.now() }, 1000)
  pollTimer = setInterval(fetchInterviews, 30_000)

  // pre-fill student from query param (invoked from talent page)
  const sid = route.query.student_id
  if (sid) {
    scheduleForm.value.student_id = Number(sid)
    scheduleDialogVisible.value = true
  }
})

onUnmounted(() => {
  clearInterval(tickTimer)
  clearInterval(pollTimer)
})

// ─── Fetch ────────────────────────────────────────────────────────────────────
async function fetchInterviews() {
  try {
    const params = activeTab.value !== 'all' ? { status: activeTab.value } : {}
    const { data } = await enterpriseApi.getInterviews(params)
    interviews.value = (data.data ?? data) as Interview[]
  } finally {
    loading.value = false
  }
}

function onTabChange(tab: string) {
  activeTab.value = tab
  loading.value = true
  fetchInterviews()
}

// ─── Counts ───────────────────────────────────────────────────────────────────
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

// ─── Can Join ─────────────────────────────────────────────────────────────────
function canJoin(iv: Interview): boolean {
  if (iv.status === 'ongoing') return true
  if (iv.status !== 'scheduled') return false
  const scheduledMs = new Date(iv.scheduled_at).getTime()
  return scheduledMs - now.value <= 15 * 60 * 1000
}

// ─── Actions ──────────────────────────────────────────────────────────────────
function joinInterview(id: number) {
  router.push(`/enterprise/interviews/${id}`)
}

function openScheduleDialog() {
  editingId.value = null
  scheduleForm.value = {
    title: '',
    student_id: undefined,
    scheduled_at: '',
    duration_minutes: 60,
    interviewer_name: '',
    notes: '',
  }
  scheduleDialogVisible.value = true
}

function openEditDialog(iv: Interview) {
  editingId.value = iv.id
  scheduleForm.value = {
    title: iv.title ?? '',
    student_id: iv.student_id,
    scheduled_at: iv.scheduled_at ?? '',
    duration_minutes: iv.duration_minutes ?? 60,
    interviewer_name: iv.interviewer_name ?? '',
    notes: iv.notes ?? '',
  }
  scheduleDialogVisible.value = true
}

function disablePastDate(date: Date): boolean {
  return date < new Date(new Date().setHours(0, 0, 0, 0))
}

async function submitSchedule() {
  if (!scheduleForm.value.scheduled_at) {
    ElMessage.warning(t('enterprise.interviews.scheduledAt') + ' is required')
    return
  }
  if (new Date(scheduleForm.value.scheduled_at) <= new Date()) {
    ElMessage.warning('Please select a future date and time.')
    return
  }
  scheduleLoading.value = true
  try {
    if (editingId.value !== null) {
      await enterpriseApi.updateInterview(editingId.value, {
        title: scheduleForm.value.title || undefined,
        scheduled_at: scheduleForm.value.scheduled_at,
        duration_minutes: scheduleForm.value.duration_minutes,
        interviewer_name: scheduleForm.value.interviewer_name || undefined,
        notes: scheduleForm.value.notes || undefined,
      })
      ElMessage.success(t('enterprise.interviews.updateSuccess'))
    } else {
      await enterpriseApi.scheduleInterview({
        title: scheduleForm.value.title || undefined,
        student_id: scheduleForm.value.student_id,
        scheduled_at: scheduleForm.value.scheduled_at,
        duration_minutes: scheduleForm.value.duration_minutes,
        interviewer_name: scheduleForm.value.interviewer_name || undefined,
        notes: scheduleForm.value.notes || undefined,
      })
      ElMessage.success(t('enterprise.interviews.scheduleSuccess'))
    }
    scheduleDialogVisible.value = false
    await fetchInterviews()
  } catch {
    ElMessage.error(t('common.error'))
  } finally {
    scheduleLoading.value = false
  }
}

async function cancelInterview(id: number) {
  try {
    await ElMessageBox.confirm(
      t('enterprise.interviews.cancelConfirm'),
      t('enterprise.interviews.cancel'),
      { type: 'warning', confirmButtonText: t('enterprise.interviews.cancel'), cancelButtonText: t('common.cancel') },
    )
  } catch {
    return
  }
  try {
    await enterpriseApi.cancelInterview(id)
    ElMessage.success(t('enterprise.interviews.cancelSuccess'))
    await fetchInterviews()
  } catch {
    ElMessage.error(t('common.error'))
  }
}

function openCompleteDialog(iv: Interview) {
  completingId.value = iv.id
  completeForm.value = {
    result: iv.result ?? 'pending',
    result_notes: iv.result_notes ?? '',
  }
  completeDialogVisible.value = true
}

async function submitComplete() {
  if (!completingId.value) return
  completeLoading.value = true
  try {
    await enterpriseApi.completeInterview(completingId.value, {
      result: completeForm.value.result,
      result_notes: completeForm.value.result_notes || undefined,
    })
    ElMessage.success(t('enterprise.interviews.completeSuccess'))
    completeDialogVisible.value = false
    await fetchInterviews()
  } catch {
    ElMessage.error(t('common.error'))
  } finally {
    completeLoading.value = false
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
const statusTagType: Record<string, string> = {
  scheduled: 'primary',
  ongoing: 'success',
  completed: 'info',
  cancelled: 'danger',
}

function avatarLetter(name: string | undefined): string {
  return (name ?? '?').charAt(0).toUpperCase()
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
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">{{ t('enterprise.interviews.pageTitle') }}</h1>
      <el-button type="primary" @click="openScheduleDialog">
        + {{ t('enterprise.interviews.scheduleNew') }}
      </el-button>
    </div>

    <!-- Tabs -->
    <div class="status-tabs">
      <button
        v-for="tab in tabs"
        :key="tab"
        class="tab-btn"
        :class="{ active: activeTab === tab }"
        @click="onTabChange(tab)"
      >
        {{ tab === 'all' ? t('enterprise.interviews.allStatuses') : t(`enterprise.interviews.status.${tab}`) }}
        <span v-if="counts[tab as keyof typeof counts] > 0" class="tab-count">
          {{ counts[tab as keyof typeof counts] }}
        </span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="interview-list">
      <el-skeleton v-for="i in 3" :key="i" animated>
        <template #template>
          <el-skeleton-item variant="rect" style="height: 140px; border-radius: 8px;" />
        </template>
      </el-skeleton>
    </div>

    <!-- Interview cards -->
    <div v-else-if="interviews.length" class="interview-list">
      <div
        v-for="iv in interviews"
        :key="iv.id"
        class="interview-card"
        :class="{ 'is-ongoing': iv.status === 'ongoing' }"
      >
        <!-- Avatar + info -->
        <div class="student-avatar">{{ avatarLetter(iv.student_name) }}</div>

        <div class="interview-info">
          <div class="interview-header">
            <div>
              <div class="interview-title">{{ iv.title ?? iv.job_title ?? '—' }}</div>
              <div v-if="iv.student_name" class="interview-student">
                {{ t('enterprise.interviews.studentName') }}: {{ iv.student_name }}
              </div>
            </div>
            <el-tag :type="statusTagType[iv.status] ?? 'info'" :class="{ 'pulse-tag': iv.status === 'ongoing' }">
              {{ t(`enterprise.interviews.status.${iv.status}`) }}
            </el-tag>
          </div>

          <!-- Meta -->
          <div class="interview-meta">
            <span>📅 {{ formatDateTime(iv.scheduled_at) }}</span>
            <span>⏱ {{ iv.duration_minutes }} min</span>
            <span v-if="iv.interviewer_name">👤 {{ iv.interviewer_name }}</span>
          </div>

          <!-- Actions -->
          <div class="interview-actions">
            <el-button
              v-if="canJoin(iv)"
              type="warning"
              size="small"
              @click="joinInterview(iv.id)"
            >
              🎥 {{ t('enterprise.interviews.joinInterview') }}
            </el-button>

            <el-button
              v-if="iv.status === 'scheduled'"
              size="small"
              @click="openEditDialog(iv)"
            >
              {{ t('enterprise.interviews.edit') }}
            </el-button>

            <el-button
              v-if="iv.status === 'scheduled' || iv.status === 'ongoing'"
              size="small"
              type="danger"
              plain
              @click="cancelInterview(iv.id)"
            >
              {{ t('enterprise.interviews.cancel') }}
            </el-button>

            <el-button
              v-if="iv.status === 'ongoing' || iv.status === 'scheduled'"
              size="small"
              type="success"
              plain
              @click="openCompleteDialog(iv)"
            >
              {{ t('enterprise.interviews.markComplete') }}
            </el-button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty -->
    <el-empty v-else :description="t('enterprise.interviews.noInterviews')" />

    <!-- Schedule / Edit Interview dialog -->
    <el-dialog
      v-model="scheduleDialogVisible"
      :title="editingId ? t('enterprise.interviews.edit') : t('enterprise.interviews.scheduleNew')"
      width="520px"
      :close-on-click-modal="false"
    >
      <el-form :model="scheduleForm" label-position="top">
        <el-form-item :label="t('enterprise.interviews.title')">
          <el-input v-model="scheduleForm.title" />
        </el-form-item>

        <el-form-item :label="t('enterprise.interviews.scheduledAt')" required>
          <el-date-picker
            v-model="scheduleForm.scheduled_at"
            type="datetime"
            :placeholder="t('enterprise.interviews.scheduledAt')"
            format="YYYY-MM-DD HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
            :disabled-date="disablePastDate"
            style="width: 100%"
          />
        </el-form-item>

        <el-form-item :label="t('enterprise.interviews.duration')">
          <el-select v-model="scheduleForm.duration_minutes" style="width: 100%">
            <el-option :label="t('enterprise.interviews.durationOptions.30')" :value="30" />
            <el-option :label="t('enterprise.interviews.durationOptions.45')" :value="45" />
            <el-option :label="t('enterprise.interviews.durationOptions.60')" :value="60" />
            <el-option :label="t('enterprise.interviews.durationOptions.90')" :value="90" />
          </el-select>
        </el-form-item>

        <el-form-item :label="t('enterprise.interviews.interviewer')">
          <el-input v-model="scheduleForm.interviewer_name" />
        </el-form-item>

        <el-form-item :label="t('enterprise.interviews.notes')">
          <el-input v-model="scheduleForm.notes" type="textarea" :rows="3" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="scheduleDialogVisible = false">{{ t('common.cancel') }}</el-button>
        <el-button type="primary" :loading="scheduleLoading" @click="submitSchedule">
          {{ editingId ? t('common.save') : t('enterprise.interviews.scheduleNew') }}
        </el-button>
      </template>
    </el-dialog>

    <!-- Mark Complete dialog -->
    <el-dialog
      v-model="completeDialogVisible"
      :title="t('enterprise.interviews.markComplete')"
      width="480px"
      :close-on-click-modal="false"
    >
      <el-form :model="completeForm" label-position="top">
        <el-form-item :label="t('enterprise.interviews.result')">
          <el-radio-group v-model="completeForm.result">
            <el-radio value="pass">{{ t('enterprise.interviews.resultOptions.pass') }}</el-radio>
            <el-radio value="fail">{{ t('enterprise.interviews.resultOptions.fail') }}</el-radio>
            <el-radio value="pending">{{ t('enterprise.interviews.resultOptions.pending') }}</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item :label="t('enterprise.interviews.resultNotes')">
          <el-input v-model="completeForm.result_notes" type="textarea" :rows="4" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="completeDialogVisible = false">{{ t('common.cancel') }}</el-button>
        <el-button type="primary" :loading="completeLoading" @click="submitComplete">
          {{ t('enterprise.interviews.markComplete') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.interviews-page { padding: 24px; }
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.page-title { font-size: 24px; font-weight: 600; color: #003366; }

.status-tabs {
  display: flex;
  gap: 0;
  border-bottom: 1px solid #dee2e6;
  margin-bottom: 24px;
  overflow-x: auto;
}
.tab-btn {
  padding: 10px 20px;
  border: none;
  background: none;
  font-size: 14px;
  font-weight: 500;
  color: #6c757d;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  white-space: nowrap;
  transition: all 0.2s;
}
.tab-btn:hover { color: #003366; }
.tab-btn.active { color: #003366; border-bottom-color: #003366; }
.tab-count {
  display: inline-block;
  margin-left: 6px;
  background: #e6f0ff;
  color: #003366;
  padding: 1px 7px;
  border-radius: 10px;
  font-size: 12px;
}

.interview-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.interview-card {
  display: flex;
  gap: 16px;
  padding: 20px;
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  transition: border-color 0.2s;
}
.interview-card:hover { border-color: #003366; }
.interview-card.is-ongoing {
  border-color: #28a745;
  background: #f0fff4;
}

.student-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #e6f0ff;
  color: #003366;
  font-weight: 700;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.interview-info { flex: 1; min-width: 0; }

.interview-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
  gap: 12px;
}
.interview-title { font-size: 15px; font-weight: 600; }
.interview-student { font-size: 13px; color: #6c757d; }

.interview-meta {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  font-size: 12px;
  color: #6c757d;
  margin-bottom: 12px;
}

.interview-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.pulse-tag {
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}
</style>

