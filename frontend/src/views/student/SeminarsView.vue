<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { publicApi } from '@/api/public'
import studentApi from '@/api/student'

const { t } = useI18n()
const router = useRouter()

// ─── Types ─────────────────────────────────────────────────────────────────
interface Seminar {
  id: number
  title: string
  description?: string
  category?: string
  presenter_name?: string
  scheduled_at: string
  duration_minutes?: number
  location?: string
  status: 'upcoming' | 'live' | 'ended'
  thumbnail_url?: string
  registration_count?: number
}

interface Registration {
  id: number
  seminar_id: number
  seminar?: Seminar
  created_at: string
}

// ─── State ─────────────────────────────────────────────────────────────────
const activeTab = ref<'browse' | 'my'>('browse')

// Browse tab
const browsing = ref(true)
const seminars = ref<Seminar[]>([])
const registeredIds = ref<Set<number>>(new Set())
const registeringId = ref<number | null>(null)

// My registrations tab
const myLoading = ref(false)
const myRegistrations = ref<Registration[]>([])
const cancellingId = ref<number | null>(null)

// ─── Load ───────────────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([fetchSeminars(), fetchMyRegistrations()])
})

async function fetchSeminars() {
  browsing.value = true
  try {
    const { data } = await publicApi.getSeminars({ per_page: 50 })
    seminars.value = (data.data ?? data) as Seminar[]
  } finally {
    browsing.value = false
  }
}

async function fetchMyRegistrations() {
  myLoading.value = true
  try {
    const { data } = await studentApi.getSeminarRegistrations()
    myRegistrations.value = (data.data ?? data) as Registration[]
    registeredIds.value = new Set(myRegistrations.value.map((r) => r.seminar_id))
  } finally {
    myLoading.value = false
  }
}

// ─── Register ───────────────────────────────────────────────────────────────
async function register(seminar: Seminar) {
  registeringId.value = seminar.id
  try {
    await publicApi.registerSeminar(seminar.id)
    registeredIds.value = new Set([...registeredIds.value, seminar.id])
    ElMessage.success('Registered successfully!')
    // Refresh My Registrations list in background
    fetchMyRegistrations()
  } catch {
    ElMessage.error('Registration failed. Please try again.')
  } finally {
    registeringId.value = null
  }
}

// ─── Cancel registration ────────────────────────────────────────────────────
async function cancelRegistration(reg: Registration) {
  try {
    await ElMessageBox.confirm(
      t('studentSeminars.cancelConfirm'),
      t('studentSeminars.cancelRegistration'),
      { type: 'warning', confirmButtonText: t('studentSeminars.cancelRegistration'), cancelButtonText: 'Keep' },
    )
  } catch {
    return
  }
  cancellingId.value = reg.id
  try {
    await publicApi.unregisterSeminar(reg.seminar_id)
    const newIds = new Set(registeredIds.value)
    newIds.delete(reg.seminar_id)
    registeredIds.value = newIds
    myRegistrations.value = myRegistrations.value.filter((r) => r.id !== reg.id)
    ElMessage.success('Registration cancelled.')
  } catch {
    ElMessage.error('Could not cancel registration.')
  } finally {
    cancellingId.value = null
  }
}

// ─── Helpers ────────────────────────────────────────────────────────────────
function formatDateTime(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleString(undefined, {
    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

function formatDateParts(dt: string) {
  if (!dt) return { day: '—', month: '—' }
  const d = new Date(dt)
  return {
    day: d.getDate().toString(),
    month: d.toLocaleString(undefined, { month: 'short' }).toUpperCase(),
  }
}

const statusTypeMap: Record<string, string> = { live: 'danger', upcoming: 'primary', ended: 'info' }
function statusTagType(status: string) { return statusTypeMap[status] ?? 'info' }

const myCount = computed(() => myRegistrations.value.length)
</script>

<template>
  <div class="seminars-page">
    <h1 class="page-heading">{{ t('studentSeminars.pageTitle') }}</h1>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab-btn" :class="{ active: activeTab === 'browse' }" @click="activeTab = 'browse'">
        {{ t('studentSeminars.browse') }}
      </button>
      <button class="tab-btn" :class="{ active: activeTab === 'my' }" @click="activeTab = 'my'">
        {{ t('studentSeminars.myRegistrations') }}
        <span v-if="myCount > 0" class="tab-count">{{ myCount }}</span>
      </button>
    </div>

    <!-- ── Browse Seminars ───────────────────────────────────────────── -->
    <div v-if="activeTab === 'browse'">
      <!-- Skeleton -->
      <div v-if="browsing" class="seminar-list">
        <div v-for="i in 4" :key="i">
          <el-skeleton animated>
            <template #template>
              <el-skeleton-item variant="rect" style="height: 120px; border-radius: 10px;" />
            </template>
          </el-skeleton>
        </div>
      </div>

      <div v-else-if="seminars.length" class="seminar-list">
        <div
          v-for="sem in seminars"
          :key="sem.id"
          class="seminar-item"
          :class="{ 'is-live': sem.status === 'live', 'is-ended': sem.status === 'ended' }"
        >
          <!-- Thumbnail -->
          <div class="seminar-thumb" :class="{ live: sem.status === 'live', past: sem.status === 'ended' }">
            <img v-if="sem.thumbnail_url" :src="sem.thumbnail_url" :alt="sem.title" class="thumb-img" />
            <template v-else>
              <el-icon class="thumb-icon"><VideoPlay /></el-icon>
              <span v-if="sem.duration_minutes" class="thumb-duration">{{ sem.duration_minutes }} min</span>
            </template>
            <span v-if="sem.status === 'live'" class="live-label">{{ t('studentSeminars.live') }}</span>
          </div>

          <!-- Info -->
          <div class="seminar-info">
            <p v-if="sem.category" class="sem-category">{{ sem.category }}</p>
            <div class="sem-title-row">
              <p class="sem-title">{{ sem.title }}</p>
              <el-tag v-if="registeredIds.has(sem.id)" type="success" size="small">
                {{ t('studentSeminars.registered') }}
              </el-tag>
            </div>
            <div class="sem-meta">
              <span v-if="sem.scheduled_at">
                <el-icon><Calendar /></el-icon>
                {{ formatDateTime(sem.scheduled_at) }}
              </span>
              <span v-if="sem.presenter_name">
                <el-icon><User /></el-icon>
                {{ sem.presenter_name }}
              </span>
              <span v-if="sem.location">
                <el-icon><Location /></el-icon>
                {{ sem.location }}
              </span>
            </div>
            <div class="sem-actions">
              <!-- Watch: available for live or ended (if student registered) -->
              <el-button
                v-if="sem.status === 'live' || (sem.status === 'ended' && registeredIds.has(sem.id))"
                type="primary"
                size="small"
                @click="router.push(`/seminars/${sem.id}/watch`)"
              >
                <el-icon class="el-icon--left"><VideoPlay /></el-icon>
                {{ t('studentSeminars.watch') }}
              </el-button>
              <!-- Register -->
              <el-button
                v-else-if="!registeredIds.has(sem.id) && sem.status === 'upcoming'"
                type="primary"
                size="small"
                :loading="registeringId === sem.id"
                @click="register(sem)"
              >
                {{ t('studentSeminars.register') }}
              </el-button>
              <!-- View detail -->
              <el-button size="small" plain @click="router.push(`/seminars/${sem.id}`)">
                View Details
              </el-button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        <el-icon class="empty-icon"><VideoPlay /></el-icon>
        <p>No seminars available.</p>
      </div>
    </div>

    <!-- ── My Registrations ──────────────────────────────────────────── -->
    <div v-if="activeTab === 'my'">
      <div v-if="myLoading" class="seminar-list">
        <el-skeleton v-for="i in 3" :key="i" animated>
          <template #template>
            <el-skeleton-item variant="rect" style="height: 72px; border-radius: 10px; margin-bottom: 12px;" />
          </template>
        </el-skeleton>
      </div>

      <div v-else-if="myRegistrations.length" class="registrations-list">
        <div v-for="reg in myRegistrations" :key="reg.id" class="reg-item">
          <!-- Date box -->
          <div class="reg-date-box">
            <span class="reg-day">{{ formatDateParts(reg.seminar?.scheduled_at ?? reg.created_at).day }}</span>
            <span class="reg-month">{{ formatDateParts(reg.seminar?.scheduled_at ?? reg.created_at).month }}</span>
          </div>

          <!-- Info -->
          <div class="reg-info">
            <p class="reg-title">{{ reg.seminar?.title ?? `Seminar #${reg.seminar_id}` }}</p>
            <p class="reg-time">
              {{ reg.seminar?.scheduled_at ? formatDateTime(reg.seminar.scheduled_at) : '—' }}
            </p>
          </div>

          <!-- Status badge -->
          <el-tag
            v-if="reg.seminar?.status"
            :type="statusTagType(reg.seminar.status)"
            size="small"
          >
            {{ reg.seminar.status === 'live' ? t('studentSeminars.live') : reg.seminar.status }}
          </el-tag>

          <!-- Actions -->
          <div class="reg-actions">
            <el-button
              v-if="reg.seminar?.status === 'live' || reg.seminar?.status === 'ended'"
              type="primary"
              size="small"
              @click="router.push(`/seminars/${reg.seminar_id}/watch`)"
            >
              <el-icon class="el-icon--left"><VideoPlay /></el-icon>
              {{ t('studentSeminars.watch') }}
            </el-button>
            <el-button
              v-if="reg.seminar?.status !== 'ended'"
              type="danger"
              size="small"
              plain
              :loading="cancellingId === reg.id"
              @click="cancelRegistration(reg)"
            >
              {{ t('studentSeminars.cancelRegistration') }}
            </el-button>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        <el-icon class="empty-icon"><Collection /></el-icon>
        <p class="empty-title">{{ t('studentSeminars.noRegistrations') }}</p>
        <el-button type="primary" @click="activeTab = 'browse'">
          {{ t('studentSeminars.browse') }}
        </el-button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.seminars-page { padding: 8px 0; }
.page-heading { font-size: 22px; font-weight: 700; color: #003366; margin: 0 0 20px; }

/* Tabs */
.tabs {
  display: flex;
  border-bottom: 2px solid #dee2e6;
  margin-bottom: 20px;
}
.tab-btn {
  padding: 10px 24px;
  font-size: 14px;
  font-weight: 500;
  color: #6c757d;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  cursor: pointer;
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

/* Seminar list */
.seminar-list { display: flex; flex-direction: column; gap: 14px; }
.seminar-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 10px;
  transition: border-color 0.2s;
}
.seminar-item:hover { border-color: #0066cc; }
.seminar-item.is-live { border-color: #dc3545; background: #fff5f5; }
.seminar-item.is-ended { opacity: 0.8; }

/* Thumbnail */
.seminar-thumb {
  width: 140px;
  height: 90px;
  border-radius: 8px;
  background: linear-gradient(135deg, #003366 0%, #004080 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #fff;
  position: relative;
  flex-shrink: 0;
  overflow: hidden;
}
.seminar-thumb.live { background: linear-gradient(135deg, #dc3545 0%, #c0392b 100%); }
.seminar-thumb.past { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
.thumb-img { width: 100%; height: 100%; object-fit: cover; }
.thumb-icon { font-size: 28px; }
.thumb-duration { font-size: 11px; opacity: 0.8; margin-top: 4px; }
.live-label {
  position: absolute;
  top: 8px;
  left: 8px;
  background: #fff;
  color: #dc3545;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
  letter-spacing: 0.04em;
}

/* Seminar info */
.seminar-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
.sem-category { font-size: 11px; text-transform: uppercase; color: #ff6b35; font-weight: 500; margin: 0; }
.sem-title-row { display: flex; align-items: flex-start; gap: 8px; }
.sem-title { font-size: 15px; font-weight: 600; color: #1a1a2e; margin: 0; flex: 1; }
.sem-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  font-size: 12px;
  color: #6c757d;
}
.sem-meta span { display: flex; align-items: center; gap: 4px; }
.sem-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }

/* Registrations list */
.registrations-list { display: flex; flex-direction: column; gap: 0; }
.reg-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 0;
  border-bottom: 1px solid #f0f0f0;
}
.reg-item:last-child { border-bottom: none; }

.reg-date-box {
  width: 52px;
  height: 52px;
  background: #e6f0ff;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.reg-day { font-size: 20px; font-weight: 700; color: #003366; line-height: 1.1; }
.reg-month { font-size: 10px; color: #6c757d; text-transform: uppercase; }

.reg-info { flex: 1; min-width: 0; }
.reg-title { font-size: 14px; font-weight: 600; color: #1a1a2e; margin: 0 0 2px; }
.reg-time { font-size: 12px; color: #6c757d; margin: 0; }

.reg-actions { display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; }

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

@media (max-width: 600px) {
  .seminar-thumb { width: 90px; height: 60px; }
  .reg-actions { flex-direction: column; }
}
</style>

