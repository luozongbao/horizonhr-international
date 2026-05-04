<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import jobsApi from '@/api/jobs'

const { t } = useI18n()
const router = useRouter()

// ─── Types ────────────────────────────────────────────────────────────────────
interface Application {
  id: number
  job_id: number
  job_title: string
  company_name: string
  company_logo?: string
  location?: string
  job_type?: string
  applied_at: string
  status: 'pending' | 'reviewed' | 'accepted' | 'rejected' | 'withdrawn'
}

// ─── State ────────────────────────────────────────────────────────────────────
const loading = ref(true)
const applications = ref<Application[]>([])
const activeTab = ref('all')
const withdrawingId = ref<number | null>(null)

const tabs = ['all', 'pending', 'reviewed', 'accepted', 'rejected']

// ─── Fetch ────────────────────────────────────────────────────────────────────
async function fetchApplications() {
  loading.value = true
  try {
    const params = activeTab.value !== 'all' ? { status: activeTab.value } : {}
    const { data } = await jobsApi.getMyApplications(params)
    applications.value = (data.data ?? data) as Application[]
  } finally {
    loading.value = false
  }
}

onMounted(fetchApplications)

function onTabChange(tab: string) {
  activeTab.value = tab
  fetchApplications()
}

// ─── Tab counts ───────────────────────────────────────────────────────────────
const counts = computed(() => {
  const all = applications.value
  return {
    all:      all.length,
    pending:  all.filter((a) => a.status === 'pending').length,
    reviewed: all.filter((a) => a.status === 'reviewed').length,
    accepted: all.filter((a) => a.status === 'accepted').length,
    rejected: all.filter((a) => a.status === 'rejected').length,
  }
})

// ─── Withdraw ─────────────────────────────────────────────────────────────────
async function withdraw(app: Application) {
  try {
    await ElMessageBox.confirm(
      t('applications.withdrawConfirm'),
      t('applications.withdraw'),
      { type: 'warning', confirmButtonText: t('applications.withdraw'), cancelButtonText: 'Cancel' },
    )
  } catch {
    return
  }

  withdrawingId.value = app.id
  try {
    await jobsApi.withdrawApplication(app.id)
    applications.value = applications.value.filter((a) => a.id !== app.id)
    ElMessage.success(t('applications.withdrawSuccess'))
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    ElMessage.error(err.response?.data?.message ?? 'Failed to withdraw application.')
  } finally {
    withdrawingId.value = null
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function formatDate(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

const statusTypeMap: Record<string, string> = {
  pending:   'warning',
  reviewed:  'primary',
  accepted:  'success',
  rejected:  'danger',
  withdrawn: 'info',
}

function statusTagType(status: string) {
  return statusTypeMap[status] ?? 'info'
}

const jobTypeColorMap: Record<string, string> = {
  full_time:  'success',
  part_time:  'warning',
  internship: 'primary',
  remote:     'info',
}

function typeTagType(type: string) {
  return jobTypeColorMap[type] ?? 'info'
}
</script>

<template>
  <div class="applications-page">
    <h1 class="page-heading">{{ t('applications.pageTitle') }}</h1>

    <!-- Status tabs -->
    <div class="status-tabs">
      <button
        v-for="tab in tabs"
        :key="tab"
        class="tab-btn"
        :class="{ active: activeTab === tab }"
        @click="onTabChange(tab)"
      >
        {{ tab === 'all' ? t('applications.allStatuses') : t(`applications.status.${tab}`) }}
        <span v-if="tab === 'all' || counts[tab as keyof typeof counts] > 0" class="tab-count">
          {{ counts[tab as keyof typeof counts] }}
        </span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="app-list">
      <div v-for="i in 4" :key="i" class="app-skeleton">
        <el-skeleton animated>
          <template #template>
            <el-skeleton-item variant="rect" style="height: 120px; border-radius: 10px;" />
          </template>
        </el-skeleton>
      </div>
    </div>

    <!-- Applications list -->
    <div v-else-if="applications.length" class="app-list">
      <div v-for="app in applications" :key="app.id" class="app-card">
        <!-- Company logo -->
        <div class="app-logo">
          <img v-if="app.company_logo" :src="app.company_logo" :alt="app.company_name" />
          <span v-else>{{ app.company_name.charAt(0) }}</span>
        </div>

        <!-- App info -->
        <div class="app-info">
          <div class="app-header">
            <div>
              <p class="app-title">{{ app.job_title }}</p>
              <p class="app-company">{{ app.company_name }}</p>
            </div>
            <el-tag :type="statusTagType(app.status)" class="status-badge">
              {{ t(`applications.status.${app.status}`) }}
            </el-tag>
          </div>

          <div class="app-meta">
            <span v-if="app.location"><el-icon><Location /></el-icon> {{ app.location }}</span>
            <span v-if="app.job_type">
              <el-tag :type="typeTagType(app.job_type)" size="small">
                {{ t(`jobs.jobTypes.${app.job_type}`) }}
              </el-tag>
            </span>
            <span><el-icon><Calendar /></el-icon> {{ t('applications.appliedOn') }}: {{ formatDate(app.applied_at) }}</span>
          </div>

          <div class="app-actions">
            <el-button
              size="small"
              plain
              @click="router.push(`/jobs?highlight=${app.job_id}`)"
            >
              <el-icon class="el-icon--left"><View /></el-icon>
              {{ t('applications.viewJob') }}
            </el-button>

            <el-button
              v-if="app.status === 'pending'"
              size="small"
              type="danger"
              plain
              :loading="withdrawingId === app.id"
              @click="withdraw(app)"
            >
              <el-icon class="el-icon--left"><Delete /></el-icon>
              {{ t('applications.withdraw') }}
            </el-button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="empty-state">
      <el-icon class="empty-icon"><Briefcase /></el-icon>
      <p class="empty-title">{{ t('applications.noApplications') }}</p>
      <el-button type="primary" @click="router.push('/jobs')">
        {{ t('applications.browseJobs') }}
      </el-button>
    </div>
  </div>
</template>

<style scoped>
.applications-page {
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

/* App list */
.app-list { display: flex; flex-direction: column; gap: 12px; }
.app-skeleton { margin: 0; }

.app-card {
  background: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 10px;
  padding: 18px 20px;
  display: flex;
  gap: 16px;
  align-items: flex-start;
  transition: border-color 0.2s;
}
.app-card:hover { border-color: #0066cc; }

.app-logo {
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
.app-logo img { width: 100%; height: 100%; object-fit: cover; }

.app-info { flex: 1; min-width: 0; }
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 8px;
}
.app-title {
  margin: 0 0 3px;
  font-size: 15px;
  font-weight: 600;
  color: #1a1a2e;
}
.app-company {
  margin: 0;
  font-size: 13px;
  color: #6c757d;
}
.status-badge { flex-shrink: 0; }

.app-meta {
  display: flex;
  align-items: center;
  gap: 14px;
  font-size: 12px;
  color: #6c757d;
  flex-wrap: wrap;
  margin-bottom: 10px;
}
.app-meta span { display: flex; align-items: center; gap: 4px; }

.app-actions { display: flex; gap: 8px; flex-wrap: wrap; }

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
</style>

