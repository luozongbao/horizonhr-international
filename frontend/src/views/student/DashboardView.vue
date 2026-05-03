<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import studentApi from '@/api/student'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

// ─── State ─────────────────────────────────────────────────────────────────
const loading = ref(true)

interface Application {
  id: number
  job_title: string
  company_name: string
  applied_at: string
  status: string
}

interface Interview {
  id: number
  job_title: string
  company_name: string
  scheduled_at: string
}

interface SeminarReg {
  id: number
  seminar_id: number
  seminar_title: string
  start_at: string
  status: string
}

const applications = ref<Application[]>([])
const interviews = ref<Interview[]>([])
const seminars = ref<SeminarReg[]>([])

// ─── Stats ─────────────────────────────────────────────────────────────────
const resumeStatus = computed(() => {
  const user = auth.user as (typeof auth.user & { resume_status?: string }) | null
  return user?.resume_status ?? 'none'
})

// Profile completion (simple heuristic based on auth.user fields)
const profileCompletion = computed(() => {
  const u = auth.user as Record<string, unknown> | null
  if (!u) return 0
  const fields: string[] = ['name', 'email', 'phone', 'date_of_birth', 'nationality', 'current_city', 'bio', 'avatar_url']
  const filled = fields.filter((f) => !!u[f]).length
  return Math.round((filled / fields.length) * 100)
})

// ─── Fetch ──────────────────────────────────────────────────────────────────
async function fetchData() {
  loading.value = true
  try {
    const [appsRes, intRes, semRes] = await Promise.allSettled([
      studentApi.getApplications({ per_page: 5 }),
      studentApi.getInterviews({ per_page: 5 }),
      studentApi.getSeminarRegistrations({ per_page: 5 }),
    ])

    if (appsRes.status === 'fulfilled') {
      const d = appsRes.value.data
      applications.value = (d.data ?? d) as Application[]
    }
    if (intRes.status === 'fulfilled') {
      const d = intRes.value.data
      interviews.value = (d.data ?? d) as Interview[]
    }
    if (semRes.status === 'fulfilled') {
      const d = semRes.value.data
      seminars.value = (d.data ?? d) as SeminarReg[]
    }
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)

// ─── Helpers ────────────────────────────────────────────────────────────────
function formatDate(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatDateTime(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const statusColorMap: Record<string, string> = {
  pending:   'warning',
  reviewing: 'warning',
  accepted:  'success',
  rejected:  'danger',
  approved:  'success',
  cancelled: 'info',
}

function statusType(status: string) {
  return statusColorMap[status?.toLowerCase()] ?? 'info'
}
</script>

<template>
  <div class="student-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
      <el-avatar :size="56" class="welcome-avatar">
        {{ auth.user?.name?.charAt(0).toUpperCase() ?? 'S' }}
      </el-avatar>
      <div class="welcome-text">
        <h1>{{ t('student.dashboardPage.welcome', { name: auth.user?.name ?? '' }) }}</h1>
        <p class="welcome-email">{{ auth.user?.email }}</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div v-if="loading" class="stats-row">
      <el-skeleton v-for="i in 4" :key="i" class="stat-card-skeleton" animated>
        <template #template>
          <el-skeleton-item variant="rect" style="height: 100px; border-radius: 12px;" />
        </template>
      </el-skeleton>
    </div>

    <div v-else class="stats-row">
      <div class="stat-card">
        <div class="stat-icon applications"><el-icon><Briefcase /></el-icon></div>
        <div class="stat-value">{{ applications.length }}</div>
        <div class="stat-label">{{ t('student.dashboardPage.stats.applications') }}</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon interviews"><el-icon><VideoCamera /></el-icon></div>
        <div class="stat-value">{{ interviews.length }}</div>
        <div class="stat-label">{{ t('student.dashboardPage.stats.interviews') }}</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon seminars"><el-icon><Collection /></el-icon></div>
        <div class="stat-value">{{ seminars.length }}</div>
        <div class="stat-label">{{ t('student.dashboardPage.stats.seminars') }}</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon resume"><el-icon><Document /></el-icon></div>
        <el-tag :type="statusType(resumeStatus)" size="large" class="resume-tag">
          {{ resumeStatus !== 'none' ? t(`student.resumePage.status.${resumeStatus}`) : '—' }}
        </el-tag>
        <div class="stat-label">{{ t('student.dashboardPage.stats.resumeStatus') }}</div>
      </div>
    </div>

    <!-- Profile Completion -->
    <div class="dashboard-card">
      <h2 class="card-title">{{ t('student.dashboardPage.profileCompletion') }}</h2>
      <el-progress
        :percentage="profileCompletion"
        :color="profileCompletion === 100 ? '#52c41a' : '#0066CC'"
        :stroke-width="10"
      />
      <p class="completion-hint" v-if="profileCompletion < 100">
        <router-link to="/student/profile">Complete your profile</router-link> to improve your chances.
      </p>
    </div>

    <!-- Content grid: applications + interviews + seminars -->
    <div class="dashboard-grid">
      <!-- Recent Applications -->
      <div class="dashboard-card">
        <h2 class="card-title">{{ t('student.dashboardPage.recentApplications') }}</h2>
        <div v-if="loading">
          <el-skeleton :rows="3" animated />
        </div>
        <template v-else-if="applications.length">
          <el-table :data="applications" size="small" class="portal-table">
            <el-table-column prop="company_name" label="Company" />
            <el-table-column prop="job_title" label="Position" />
            <el-table-column label="Date" width="120">
              <template #default="{ row }">{{ formatDate(row.applied_at) }}</template>
            </el-table-column>
            <el-table-column label="Status" width="110">
              <template #default="{ row }">
                <el-tag :type="statusType(row.status)" size="small">{{ row.status }}</el-tag>
              </template>
            </el-table-column>
          </el-table>
        </template>
        <p v-else class="empty-text">{{ t('student.dashboardPage.noApplications') }}</p>
      </div>

      <!-- Upcoming Interviews -->
      <div class="dashboard-card">
        <h2 class="card-title">{{ t('student.dashboardPage.upcomingInterviews') }}</h2>
        <div v-if="loading">
          <el-skeleton :rows="3" animated />
        </div>
        <template v-else-if="interviews.length">
          <ul class="item-list">
            <li v-for="iv in interviews" :key="iv.id" class="item-row">
              <div class="item-info">
                <p class="item-title">{{ iv.job_title }}</p>
                <p class="item-sub">{{ iv.company_name }} · {{ formatDateTime(iv.scheduled_at) }}</p>
              </div>
              <el-button
                size="small"
                type="primary"
                @click="router.push(`/student/interviews/${iv.id}`)"
              >
                {{ t('student.dashboardPage.joinInterview') }}
              </el-button>
            </li>
          </ul>
        </template>
        <p v-else class="empty-text">{{ t('student.dashboardPage.noInterviews') }}</p>
      </div>

      <!-- Upcoming Seminars -->
      <div class="dashboard-card">
        <h2 class="card-title">{{ t('student.dashboardPage.upcomingSeminars') }}</h2>
        <div v-if="loading">
          <el-skeleton :rows="3" animated />
        </div>
        <template v-else-if="seminars.length">
          <ul class="item-list">
            <li v-for="sem in seminars" :key="sem.id" class="item-row">
              <div class="item-info">
                <p class="item-title">{{ sem.seminar_title }}</p>
                <p class="item-sub">{{ formatDateTime(sem.start_at) }}</p>
              </div>
              <el-button
                size="small"
                type="primary"
                @click="router.push(`/seminars/${sem.seminar_id}`)"
              >
                {{ t('student.dashboardPage.watchSeminar') }}
              </el-button>
            </li>
          </ul>
        </template>
        <p v-else class="empty-text">{{ t('student.dashboardPage.noSeminars') }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.student-dashboard {
  padding: 8px 0;
}

/* Welcome banner */
.welcome-banner {
  display: flex;
  align-items: center;
  gap: 16px;
  background: linear-gradient(135deg, #003366 0%, #0055a4 100%);
  border-radius: 16px;
  padding: 24px 32px;
  margin-bottom: 24px;
  color: #fff;
}
.welcome-avatar {
  background: rgba(255,255,255,0.25);
  color: #fff;
  font-size: 24px;
  font-weight: 700;
  flex-shrink: 0;
}
.welcome-text h1 {
  margin: 0 0 4px;
  font-size: 22px;
  font-weight: 700;
}
.welcome-email {
  margin: 0;
  opacity: 0.8;
  font-size: 14px;
}

/* Stats row */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
.stat-card-skeleton {
  border-radius: 12px;
}
.stat-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 20px 16px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  text-align: center;
}
.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}
.stat-icon.applications { background: #e6f0ff; color: #0066cc; }
.stat-icon.interviews   { background: #e8f5e9; color: #2e7d32; }
.stat-icon.seminars     { background: #fff8e1; color: #f59e0b; }
.stat-icon.resume       { background: #fce4ec; color: #c62828; }

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #1a1a2e;
  line-height: 1;
}
.stat-label {
  font-size: 12px;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.resume-tag {
  font-size: 13px;
  height: 28px;
  line-height: 26px;
}

/* Completion */
.completion-hint {
  margin: 8px 0 0;
  font-size: 13px;
  color: #6c757d;
}
.completion-hint a {
  color: #0066cc;
  text-decoration: none;
}
.completion-hint a:hover { text-decoration: underline; }

/* Dashboard grid */
.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.dashboard-grid > .dashboard-card:first-child {
  grid-column: 1 / -1;
}

/* Cards */
.dashboard-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 20px 24px;
  margin-bottom: 20px;
}
.card-title {
  font-size: 16px;
  font-weight: 600;
  color: #003366;
  margin: 0 0 16px;
}

/* Portal table */
.portal-table {
  width: 100%;
}

/* Item list */
.item-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.item-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  background: #f8f9fa;
  border-radius: 8px;
  gap: 12px;
}
.item-info { flex: 1; min-width: 0; }
.item-title {
  margin: 0 0 2px;
  font-size: 14px;
  font-weight: 500;
  color: #1a1a2e;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.item-sub {
  margin: 0;
  font-size: 12px;
  color: #6c757d;
}

.empty-text {
  color: #6c757d;
  font-size: 14px;
  text-align: center;
  padding: 24px 0;
  margin: 0;
}

@media (max-width: 900px) {
  .stats-row { grid-template-columns: repeat(2, 1fr); }
  .dashboard-grid { grid-template-columns: 1fr; }
  .dashboard-grid > .dashboard-card:first-child { grid-column: 1; }
}
</style>

