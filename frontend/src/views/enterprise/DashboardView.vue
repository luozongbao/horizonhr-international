<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import enterpriseApi, { type EnterpriseProfileData } from '@/api/enterprise'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

// ─── Types ─────────────────────────────────────────────────────────────────
interface DashboardStats {
  active_jobs: number
  total_applications: number
  scheduled_interviews: number
  talent_viewed: number
}

interface Job {
  id: number
  title_en: string
  status: string
  applications_count?: number
  created_at: string
}

interface Application {
  id: number
  student_name?: string
  job_title?: string
  created_at: string
  status: string
  resume_url?: string
}

interface Interview {
  id: number
  student_name?: string
  job_title?: string
  scheduled_at: string
  status: string
}

// ─── State ─────────────────────────────────────────────────────────────────
const loading = ref(true)
const profile = ref<EnterpriseProfileData | null>(null)
const stats = ref<DashboardStats>({ active_jobs: 0, total_applications: 0, scheduled_interviews: 0, talent_viewed: 0 })
const recentJobs = ref<Job[]>([])
const recentApplications = ref<Application[]>([])
const upcomingInterviews = ref<Interview[]>([])

const isPending = computed(() => profile.value?.status === 'pending')

// ─── Fetch ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  try {
    const [profileRes, jobsRes] = await Promise.allSettled([
      enterpriseApi.getProfile(),
      enterpriseApi.getJobs({ per_page: 5 }),
    ])

    if (profileRes.status === 'fulfilled') {
      profile.value = (profileRes.value.data.data ?? profileRes.value.data) as EnterpriseProfileData
    }

    if (jobsRes.status === 'fulfilled') {
      const jobs = (jobsRes.value.data.data ?? jobsRes.value.data) as Job[]
      recentJobs.value = jobs.slice(0, 5)
      stats.value.active_jobs = jobs.filter((j) => j.status === 'published').length
    }

    // Try dashboard stats endpoint (may not exist — graceful fallback)
    try {
      const dashRes = await enterpriseApi.getDashboard()
      const d = dashRes.data.data ?? dashRes.data
      if (d) {
        stats.value = { ...stats.value, ...d }
      }
    } catch { /* endpoint optional */ }

    // Fetch recent applications
    try {
      const appRes = await enterpriseApi.getApplications({ per_page: 5 })
      recentApplications.value = (appRes.data.data ?? appRes.data) as Application[]
    } catch { /* optional */ }

    // Fetch upcoming interviews
    try {
      const ivRes = await enterpriseApi.getInterviews({ status: 'scheduled', per_page: 5 })
      upcomingInterviews.value = (ivRes.data.data ?? ivRes.data) as Interview[]
    } catch { /* optional */ }

  } finally {
    loading.value = false
  }
})

// ─── Helpers ────────────────────────────────────────────────────────────────
function formatDate(dt: string) {
  return new Date(dt).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}
function formatDateTime(dt: string) {
  return new Date(dt).toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const appStatusTypeMap: Record<string, string> = { pending: 'warning', reviewed: 'primary', accepted: 'success', rejected: 'danger' }
const jobStatusTypeMap: Record<string, string> = { draft: 'info', published: 'success', closed: 'danger' }
</script>

<template>
  <div class="dashboard-page">

    <!-- Pending banner -->
    <div v-if="!loading && isPending" class="pending-banner">
      <el-icon><Warning /></el-icon>
      <span>{{ t('enterprise.dashboard.pendingBanner') }}</span>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading">
      <div class="skeleton-row">
        <el-skeleton v-for="i in 4" :key="i" animated style="flex:1">
          <template #template>
            <el-skeleton-item variant="rect" style="height: 96px; border-radius: 10px;" />
          </template>
        </el-skeleton>
      </div>
    </div>

    <template v-else>
      <!-- Company info -->
      <div class="company-card">
        <div class="company-logo">
          <img v-if="profile?.logo_url" :src="profile.logo_url" :alt="profile.company_name" />
          <span v-else>{{ profile?.company_name?.charAt(0) ?? auth.user?.name?.charAt(0) ?? 'E' }}</span>
        </div>
        <div class="company-info">
          <h1 class="company-name">{{ profile?.company_name ?? auth.user?.name }}</h1>
          <p v-if="profile?.industry" class="company-industry">{{ profile.industry }}</p>
        </div>
        <el-tag :type="isPending ? 'warning' : 'success'" size="large">
          {{ isPending ? 'Pending Approval' : 'Active' }}
        </el-tag>
        <el-button plain @click="router.push('/enterprise/profile')">Edit Profile</el-button>
      </div>

      <!-- Stats row -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-icon jobs"><el-icon><Briefcase /></el-icon></div>
          <div class="stat-body">
            <p class="stat-value">{{ stats.active_jobs }}</p>
            <p class="stat-label">{{ t('enterprise.dashboard.stats.activeJobs') }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon apps"><el-icon><Document /></el-icon></div>
          <div class="stat-body">
            <p class="stat-value">{{ stats.total_applications }}</p>
            <p class="stat-label">{{ t('enterprise.dashboard.stats.applications') }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ivs"><el-icon><VideoCamera /></el-icon></div>
          <div class="stat-body">
            <p class="stat-value">{{ stats.scheduled_interviews }}</p>
            <p class="stat-label">{{ t('enterprise.dashboard.stats.interviews') }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon talent"><el-icon><User /></el-icon></div>
          <div class="stat-body">
            <p class="stat-value">{{ stats.talent_viewed }}</p>
            <p class="stat-label">{{ t('enterprise.dashboard.stats.talentViewed') }}</p>
          </div>
        </div>
      </div>

      <!-- Content grid -->
      <div class="content-grid">

        <!-- Recent Applications -->
        <div class="section-card span-2">
          <div class="section-header">
            <h2 class="section-title">{{ t('enterprise.dashboard.recentApplications') }}</h2>
            <el-button link @click="router.push('/enterprise/jobs')">View All →</el-button>
          </div>
          <template v-if="recentApplications.length">
            <el-table :data="recentApplications" size="small" style="width:100%">
              <el-table-column :label="t('enterprise.appReview.studentName')" prop="student_name" min-width="140" />
              <el-table-column label="Job" prop="job_title" min-width="160" />
              <el-table-column :label="t('enterprise.appReview.appliedDate')" min-width="110">
                <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
              </el-table-column>
              <el-table-column :label="t('enterprise.appReview.status')" min-width="100">
                <template #default="{ row }">
                  <el-tag :type="appStatusTypeMap[row.status] ?? 'info'" size="small">{{ row.status }}</el-tag>
                </template>
              </el-table-column>
              <el-table-column label="Actions" width="100">
                <template #default="{ row }">
                  <el-button link type="primary" @click="router.push('/enterprise/jobs')">Review</el-button>
                </template>
              </el-table-column>
            </el-table>
          </template>
          <p v-else class="empty-text">{{ t('enterprise.appReview.noApplications') }}</p>
        </div>

        <!-- Upcoming Interviews -->
        <div class="section-card">
          <div class="section-header">
            <h2 class="section-title">{{ t('enterprise.dashboard.upcomingInterviews') }}</h2>
            <el-button link @click="router.push('/enterprise/interviews')">View All →</el-button>
          </div>
          <div v-if="upcomingInterviews.length" class="iv-list">
            <div v-for="iv in upcomingInterviews" :key="iv.id" class="iv-item">
              <div class="iv-info">
                <p class="iv-student">{{ iv.student_name }}</p>
                <p class="iv-meta">{{ iv.job_title }}</p>
                <p class="iv-time">{{ formatDateTime(iv.scheduled_at) }}</p>
              </div>
              <el-button type="primary" size="small" @click="router.push(`/enterprise/interviews/${iv.id}`)">
                Join
              </el-button>
            </div>
          </div>
          <p v-else class="empty-text">No upcoming interviews.</p>
        </div>

        <!-- Recent Jobs -->
        <div class="section-card">
          <div class="section-header">
            <h2 class="section-title">{{ t('enterprise.dashboard.myJobs') }}</h2>
            <el-button link @click="router.push('/enterprise/jobs')">Manage →</el-button>
          </div>
          <div v-if="recentJobs.length" class="job-list">
            <div v-for="job in recentJobs" :key="job.id" class="job-item">
              <div class="job-item-info">
                <p class="job-item-title">{{ job.title_en }}</p>
                <p class="job-item-meta">{{ formatDate(job.created_at) }}</p>
              </div>
              <div class="job-item-right">
                <el-tag :type="jobStatusTypeMap[job.status] ?? 'info'" size="small">{{ job.status }}</el-tag>
                <span v-if="job.applications_count" class="app-count">{{ job.applications_count }} apps</span>
              </div>
            </div>
          </div>
          <p v-else class="empty-text">{{ t('enterprise.jobs.noJobs') }}</p>
        </div>

      </div>
    </template>
  </div>
</template>

<style scoped>
.dashboard-page { padding: 8px 0; display: flex; flex-direction: column; gap: 20px; }

.pending-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-left: 4px solid #f59e0b;
  border-radius: 8px;
  padding: 14px 18px;
  color: #92400e;
  font-size: 14px;
  font-weight: 500;
}

/* Company card */
.company-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 16px;
}
.company-logo {
  width: 64px;
  height: 64px;
  border-radius: 10px;
  background: #e6f0ff;
  color: #003366;
  font-size: 24px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.company-logo img { width: 100%; height: 100%; object-fit: cover; }
.company-info { flex: 1; min-width: 0; }
.company-name { font-size: 20px; font-weight: 700; color: #003366; margin: 0 0 4px; }
.company-industry { font-size: 13px; color: #6c757d; margin: 0; }

/* Stats */
.skeleton-row { display: flex; gap: 16px; }
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.stat-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 10px;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.stat-icon {
  width: 48px; height: 48px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; flex-shrink: 0;
}
.stat-icon.jobs   { background: #e6f0ff; color: #0066cc; }
.stat-icon.apps   { background: #e6fff5; color: #00a854; }
.stat-icon.ivs    { background: #fff0e6; color: #ff6b35; }
.stat-icon.talent { background: #f3e6ff; color: #7c3aed; }
.stat-value { font-size: 24px; font-weight: 700; color: #003366; margin: 0; }
.stat-label { font-size: 12px; color: #6c757d; margin: 0; }

/* Content grid */
.content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.section-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 20px 24px;
}
.span-2 { grid-column: span 2; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.section-title { font-size: 15px; font-weight: 600; color: #003366; margin: 0; }
.empty-text { font-size: 13px; color: #6c757d; text-align: center; padding: 20px 0; }

/* Interview list */
.iv-list { display: flex; flex-direction: column; gap: 12px; }
.iv-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
.iv-item:last-child { border-bottom: none; }
.iv-student { font-size: 14px; font-weight: 600; color: #1a1a2e; margin: 0 0 2px; }
.iv-meta { font-size: 12px; color: #6c757d; margin: 0 0 2px; }
.iv-time { font-size: 12px; color: #0066cc; margin: 0; }

/* Job list */
.job-list { display: flex; flex-direction: column; }
.job-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
.job-item:last-child { border-bottom: none; }
.job-item-title { font-size: 14px; font-weight: 500; color: #1a1a2e; margin: 0 0 2px; }
.job-item-meta { font-size: 12px; color: #6c757d; margin: 0; }
.job-item-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.app-count { font-size: 11px; color: #6c757d; }

@media (max-width: 900px) {
  .stats-row { grid-template-columns: repeat(2, 1fr); }
  .content-grid { grid-template-columns: 1fr; }
  .span-2 { grid-column: span 1; }
}
</style>

