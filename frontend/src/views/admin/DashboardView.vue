<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js'
import { Line, Bar, Doughnut } from 'vue-chartjs'
import adminApi, { type StatsPeriod } from '@/api/admin'

ChartJS.register(
  CategoryScale, LinearScale, PointElement, LineElement,
  BarElement, ArcElement, Title, Tooltip, Legend, Filler,
)

const { t } = useI18n()
const router = useRouter()

// ─── Types ────────────────────────────────────────────────────────────────────
interface DailyPoint { date: string; count: number }

interface StatsData {
  period: string
  total_users?: number
  new_users?: number
  students?: number
  enterprises?: number
  admins?: number
  total_resumes?: number
  pending_resumes?: number
  total_applications?: number
  total_interviews?: number
  active_jobs?: number
  total_seminars?: number
  total_contacts?: number
  resume_status?: { pending?: number; approved?: number; rejected?: number }
  application_status?: Record<string, number>
  interview_status?: Record<string, number>
  daily_trend?: {
    new_users?: DailyPoint[]
    new_resumes?: DailyPoint[]
    new_interviews?: DailyPoint[]
  }
}

// ─── State ────────────────────────────────────────────────────────────────────
const period = ref<StatsPeriod>('30d')
const periods: StatsPeriod[] = ['7d', '30d', '90d', '1y', 'all']
const loading = ref(true)
const stats = ref<StatsData>({} as StatsData)

// ─── Fetch ────────────────────────────────────────────────────────────────────
async function fetchStats() {
  loading.value = true
  try {
    const { data } = await adminApi.getStats(period.value)
    stats.value = data.data ?? data
  } catch {
    stats.value = {} as StatsData
  } finally {
    loading.value = false
  }
}

onMounted(() => { fetchStats(); fetchPendingEnterprises() })
watch(period, fetchStats)

// ─── Pending enterprises ─────────────────────────────────────────────────────
interface PendingEnterprise {
  id: number
  display_name?: string
  email: string
  email_verified: boolean
  created_at: string
}
const pendingEnterprises = ref<PendingEnterprise[]>([])
const pendingLoading = ref(false)

async function fetchPendingEnterprises() {
  pendingLoading.value = true
  try {
    const { data } = await adminApi.getUsers({ role: 'enterprise', status: 'pending', per_page: 50 })
    const res = data.data ?? data
    pendingEnterprises.value = res.data ?? res
  } finally {
    pendingLoading.value = false
  }
}

async function approveEnterprise(ent: PendingEnterprise) {
  await ElMessageBox.confirm(
    `Activate enterprise account for "${ent.display_name ?? ent.email}"?`,
    'Confirm Activation',
    { type: 'warning', confirmButtonText: 'Activate', cancelButtonText: 'Cancel' },
  )
  await adminApi.approveEnterprise(ent.id)
  ElMessage.success('Enterprise account activated!')
  fetchPendingEnterprises()
  fetchStats()
}const statCards = computed(() => [
  { label: t('adminDashboard.stats.totalUsers'), value: stats.value.total_users ?? 0, color: 'blue', icon: '👥' },
  { label: t('adminDashboard.stats.newUsers'), value: stats.value.new_users ?? 0, color: 'green', icon: '🆕' },
  { label: t('adminDashboard.stats.pendingResumes'), value: stats.value.pending_resumes ?? 0, color: 'orange', icon: '📄' },
  { label: t('adminDashboard.stats.totalApplications'), value: stats.value.total_applications ?? 0, color: 'blue', icon: '📋' },
  { label: t('adminDashboard.stats.interviews'), value: stats.value.total_interviews ?? 0, color: 'green', icon: '🎥' },
  { label: t('adminDashboard.stats.activeJobs'), value: stats.value.active_jobs ?? 0, color: 'orange', icon: '💼' },
  { label: t('adminDashboard.stats.seminars'), value: stats.value.total_seminars ?? 0, color: 'blue', icon: '📡' },
  { label: t('adminDashboard.stats.contacts'), value: stats.value.total_contacts ?? 0, color: 'orange', icon: '✉️' },
])

// ─── Chart helpers ────────────────────────────────────────────────────────────
function trendLabels(arr: DailyPoint[] | undefined): string[] {
  return (arr ?? []).map((p) => p.date)
}
function trendValues(arr: DailyPoint[] | undefined): number[] {
  return (arr ?? []).map((p) => p.count)
}

// Line chart — user growth
const userGrowthData = computed(() => ({
  labels: trendLabels(stats.value.daily_trend?.new_users),
  datasets: [
    {
      label: t('adminDashboard.stats.newUsers'),
      data: trendValues(stats.value.daily_trend?.new_users),
      borderColor: '#003366',
      backgroundColor: 'rgba(0,51,102,0.08)',
      fill: true,
      tension: 0.4,
      pointRadius: 3,
    },
  ],
}))

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: { grid: { display: false } },
    y: { beginAtZero: true, ticks: { precision: 0 } },
  },
}

// Bar chart — activity (resumes + interviews)
const activityData = computed(() => {
  const resumeArr = stats.value.daily_trend?.new_resumes ?? []
  const interviewArr = stats.value.daily_trend?.new_interviews ?? []
  const labels = resumeArr.length
    ? resumeArr.map((p) => p.date)
    : interviewArr.map((p) => p.date)
  return {
    labels,
    datasets: [
      {
        label: t('adminDashboard.stats.totalResumes'),
        data: trendValues(stats.value.daily_trend?.new_resumes),
        backgroundColor: '#003366',
        borderRadius: 4,
      },
      {
        label: t('adminDashboard.stats.interviews'),
        data: trendValues(stats.value.daily_trend?.new_interviews),
        backgroundColor: '#E6F0FF',
        borderRadius: 4,
      },
    ],
  }
})

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom' as const } },
  scales: {
    x: { grid: { display: false } },
    y: { beginAtZero: true, ticks: { precision: 0 } },
  },
}

// Doughnut — resume status
const resumeStatusData = computed(() => {
  const s = stats.value.resume_status ?? {}
  return {
    labels: ['Pending', 'Approved', 'Rejected'],
    datasets: [{
      data: [s.pending ?? 0, s.approved ?? 0, s.rejected ?? 0],
      backgroundColor: ['#FFC107', '#28A745', '#DC3545'],
      borderWidth: 0,
    }],
  }
})

// Doughnut — application status
const appStatusData = computed(() => {
  const s = stats.value.application_status ?? {}
  return {
    labels: Object.keys(s),
    datasets: [{
      data: Object.values(s),
      backgroundColor: ['#003366', '#E6F0FF', '#28A745', '#DC3545', '#6c757d'],
      borderWidth: 0,
    }],
  }
})

// Doughnut — interview status
const ivStatusData = computed(() => {
  const s = stats.value.interview_status ?? {}
  return {
    labels: Object.keys(s),
    datasets: [{
      data: Object.values(s),
      backgroundColor: ['#003366', '#28A745', '#E6F0FF', '#DC3545'],
      borderWidth: 0,
    }],
  }
})

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom' as const } },
  cutout: '65%',
}
</script>

<template>
  <div class="admin-dashboard">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">{{ t('adminDashboard.title') }}</h1>

      <!-- Period selector -->
      <div class="period-tabs">
        <button
          v-for="p in periods"
          :key="p"
          class="period-btn"
          :class="{ active: period === p }"
          @click="period = p"
        >
          {{ t(`adminDashboard.period.${p}`) }}
        </button>
      </div>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading" class="stats-grid">
      <el-skeleton v-for="i in 8" :key="i" animated>
        <template #template>
          <el-skeleton-item variant="rect" style="height: 100px; border-radius: 8px;" />
        </template>
      </el-skeleton>
    </div>

    <template v-else>
      <!-- Stat cards -->
      <div class="stats-grid">
        <div
          v-for="card in statCards"
          :key="card.label"
          class="stat-card"
          :class="`stat-${card.color}`"
        >
          <div class="stat-icon">{{ card.icon }}</div>
          <div class="stat-body">
            <div class="stat-value">{{ card.value.toLocaleString() }}</div>
            <div class="stat-label">{{ card.label }}</div>
          </div>
        </div>
      </div>

      <!-- User role breakdown -->
      <div class="role-breakdown">
        <span>👤 {{ t('adminUsers.role.student') }}: <strong>{{ (stats.students ?? 0).toLocaleString() }}</strong></span>
        <span>🏢 {{ t('adminUsers.role.enterprise') }}: <strong>{{ (stats.enterprises ?? 0).toLocaleString() }}</strong></span>
        <span>🔑 {{ t('adminUsers.role.admin') }}: <strong>{{ (stats.admins ?? 0).toLocaleString() }}</strong></span>
      </div>

      <!-- ── Pending Enterprise Accounts (shown above charts for visibility) ── -->
      <div v-if="pendingLoading || pendingEnterprises.length > 0" class="pending-enterprises">
        <div class="section-header">
          <h2 class="section-title">
            🏢 Pending Enterprise Accounts
            <el-badge v-if="pendingEnterprises.length" :value="pendingEnterprises.length" type="danger" class="ml-2" />
          </h2>
          <el-button size="small" text @click="router.push('/admin/users?role=enterprise&status=pending')">
            View All →
          </el-button>
        </div>

        <div v-if="pendingLoading" class="pending-loading">
          <el-skeleton animated :rows="2" />
        </div>
        <div v-else class="pending-list">
          <div v-for="ent in pendingEnterprises" :key="ent.id" class="pending-item">
            <div class="pending-info">
              <div class="pending-company">{{ ent.display_name ?? ent.email }}</div>
              <div class="pending-meta">
                {{ ent.email }}
                <el-tag v-if="!ent.email_verified" type="warning" size="small" class="ml-1">Email unverified</el-tag>
                <el-tag v-else type="success" size="small" class="ml-1">Email verified</el-tag>
                <span class="pending-date">· Registered {{ new Date(ent.created_at).toLocaleDateString() }}</span>
              </div>
            </div>
            <el-button type="success" size="small" @click="approveEnterprise(ent)">Activate</el-button>
          </div>
        </div>
      </div>

      <!-- Charts row 1 -->
      <div class="charts-row">
        <!-- User growth line chart -->
        <div class="chart-card">
          <div class="chart-title">{{ t('adminDashboard.charts.userGrowth') }}</div>
          <div class="chart-wrap">
            <Line v-if="(stats.daily_trend?.new_users?.length ?? 0) > 0" :data="userGrowthData" :options="lineOptions" />
            <div v-else class="chart-empty">{{ t('common.noData') }}</div>
          </div>
        </div>

        <!-- Activity bar chart -->
        <div class="chart-card">
          <div class="chart-title">{{ t('adminDashboard.charts.activity') }}</div>
          <div class="chart-wrap">
            <Bar
              v-if="(stats.daily_trend?.new_resumes?.length ?? 0) > 0 || (stats.daily_trend?.new_interviews?.length ?? 0) > 0"
              :data="activityData"
              :options="barOptions"
            />
            <div v-else class="chart-empty">{{ t('common.noData') }}</div>
          </div>
        </div>
      </div>

      <!-- Charts row 2 — status breakdowns -->
      <div class="charts-row charts-row-3">
        <div class="chart-card">
          <div class="chart-title">{{ t('adminDashboard.charts.resumeStatus') }}</div>
          <div class="chart-wrap chart-wrap-sm">
            <Doughnut :data="resumeStatusData" :options="doughnutOptions" />
          </div>
        </div>

        <div class="chart-card">
          <div class="chart-title">{{ t('adminDashboard.charts.applicationStatus') }}</div>
          <div class="chart-wrap chart-wrap-sm">
            <Doughnut :data="appStatusData" :options="doughnutOptions" />
          </div>
        </div>

        <div class="chart-card">
          <div class="chart-title">{{ t('adminDashboard.charts.interviewStatus') }}</div>
          <div class="chart-wrap chart-wrap-sm">
            <Doughnut :data="ivStatusData" :options="doughnutOptions" />
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="quick-actions">
        <div class="quick-action" @click="router.push('/admin/users')">
          <div class="qa-icon">👥</div>
          <span>{{ t('adminUsers.pageTitle') }}</span>
        </div>
        <div class="quick-action" @click="router.push('/admin/resumes')">
          <div class="qa-icon">📄</div>
          <span>{{ t('admin.resumes') }}</span>
        </div>
        <div class="quick-action" @click="router.push('/admin/interviews')">
          <div class="qa-icon">🎥</div>
          <span>{{ t('admin.interviews') }}</span>
        </div>
        <div class="quick-action" @click="router.push('/admin/seminars')">
          <div class="qa-icon">📡</div>
          <span>{{ t('admin.seminars') }}</span>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.admin-dashboard { padding: 24px; }

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 12px;
}
.page-title { font-size: 24px; font-weight: 600; color: #003366; }

.period-tabs { display: flex; gap: 4px; }
.period-btn {
  padding: 6px 14px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  background: #fff;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}
.period-btn:hover { border-color: #003366; color: #003366; }
.period-btn.active { background: #003366; color: #fff; border-color: #003366; }

/* Stat cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 16px;
}
@media (max-width: 1100px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .stats-grid { grid-template-columns: 1fr; } }

.stat-card {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0,51,102,0.06);
}
.stat-icon { font-size: 28px; }
.stat-value { font-size: 28px; font-weight: 700; color: #1a1a2e; line-height: 1; }
.stat-label { font-size: 13px; color: #6c757d; margin-top: 4px; }
.stat-blue .stat-icon-bg { background: #e6f0ff; }
.stat-green .stat-value { color: #28a745; }
.stat-orange .stat-value { color: #ff6b35; }

/* Role breakdown */
.role-breakdown {
  display: flex;
  gap: 24px;
  font-size: 13px;
  color: #6c757d;
  margin-bottom: 24px;
  flex-wrap: wrap;
}
.role-breakdown strong { color: #003366; }

/* Charts */
.charts-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}
.charts-row-3 { grid-template-columns: repeat(3, 1fr); }
@media (max-width: 900px) {
  .charts-row, .charts-row-3 { grid-template-columns: 1fr; }
}

.chart-card {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,51,102,0.06);
}
.chart-title { font-size: 15px; font-weight: 600; color: #003366; margin-bottom: 16px; }
.chart-wrap { position: relative; height: 220px; }
.chart-wrap-sm { height: 180px; }
.chart-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #6c757d;
  font-size: 14px;
}

/* Pending Enterprise Accounts */
.pending-enterprises {
  background: #fff;
  border-radius: 12px;
  border: 2px solid #ffc107;
  padding: 20px 24px;
  margin-bottom: 24px;
}
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.section-title {
  font-size: 16px;
  font-weight: 600;
  color: #003366;
  display: flex;
  align-items: center;
  gap: 8px;
}
.ml-1 { margin-left: 4px; }
.ml-2 { margin-left: 8px; }
.pending-loading { padding: 8px 0; }
.pending-empty { padding: 8px 0; }
.pending-list { display: flex; flex-direction: column; gap: 10px; }
.pending-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: #fffbf0;
  border: 1px solid #ffe58f;
  border-radius: 8px;
}
.pending-company { font-weight: 600; font-size: 14px; color: #1a1a1a; }
.pending-meta { font-size: 12px; color: #6c757d; margin-top: 2px; display: flex; align-items: center; flex-wrap: wrap; gap: 4px; }
.pending-date { color: #aaa; }

/* Quick actions */
.quick-actions {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-top: 8px;
}
@media (max-width: 700px) { .quick-actions { grid-template-columns: repeat(2, 1fr); } }

.quick-action {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
  font-weight: 500;
}
.quick-action:hover { border-color: #003366; box-shadow: 0 4px 12px rgba(0,51,102,0.1); }
.qa-icon { font-size: 28px; }
</style>

