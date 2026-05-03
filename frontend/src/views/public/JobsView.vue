<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { useAuthStore } from '@/stores/auth'
import jobsApi from '@/api/jobs'
import studentApi from '@/api/student'
import { useSanitize } from '@/composables/useSanitize'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()
const { sanitize } = useSanitize()

// ─── Types ────────────────────────────────────────────────────────────────────
interface Job {
  id: number
  title: string
  company_name: string
  company_logo?: string
  company_size?: string
  industry?: string
  location: string
  job_type: string
  salary_min?: number
  salary_max?: number
  salary_disclosed?: boolean
  description: string
  requirements?: string
  posted_at: string
  applied?: boolean
}

// ─── Filter state ─────────────────────────────────────────────────────────────
const filters = reactive({
  search: '',
  location: '',
  job_type: '',
  industry: '',
})

const industries = [
  'Technology', 'Finance', 'Education', 'Healthcare', 'Manufacturing',
  'Retail', 'Hospitality', 'Construction', 'Media', 'Logistics', 'Other',
]

const jobTypeOptions = [
  { value: 'full_time',  label: '' },
  { value: 'part_time',  label: '' },
  { value: 'internship', label: '' },
  { value: 'remote',     label: '' },
]

// ─── Data state ───────────────────────────────────────────────────────────────
const loading = ref(true)
const jobs = ref<Job[]>([])
const currentPage = ref(1)
const totalJobs = ref(0)
const perPage = 15

const selectedJob = ref<Job | null>(null)
const applyingJobId = ref<number | null>(null)

// Applied job ids — to track "already applied" after applying
const appliedIds = ref<Set<number>>(new Set())

// ─── Resume status for apply check ───────────────────────────────────────────
const hasApprovedResume = ref(false)

async function checkResume() {
  if (!auth.isStudent) return
  try {
    const { data } = await studentApi.getResumes()
    const resumes = data.data ?? data
    hasApprovedResume.value = Array.isArray(resumes) && resumes.some((r: { status: string }) => r.status === 'approved')
  } catch {
    hasApprovedResume.value = false
  }
}

// ─── Fetch jobs ───────────────────────────────────────────────────────────────
async function fetchJobs() {
  loading.value = true
  try {
    const { data } = await jobsApi.getJobs({
      search:   filters.search   || undefined,
      location: filters.location || undefined,
      job_type: filters.job_type || undefined,
      industry: filters.industry || undefined,
      page:     currentPage.value,
      per_page: perPage,
    })
    const result = data.data ?? data
    if (Array.isArray(result)) {
      jobs.value = result as Job[]
      totalJobs.value = data.total ?? result.length
    } else {
      jobs.value = result.data ?? []
      totalJobs.value = result.total ?? 0
    }

    // Mark already-applied jobs
    jobs.value.forEach((j) => {
      if (j.applied) appliedIds.value.add(j.id)
    })

    // Auto-select first job on desktop
    if (jobs.value.length && !selectedJob.value) {
      selectedJob.value = jobs.value[0]
    }
  } finally {
    loading.value = false
  }
}

function onSearch() {
  currentPage.value = 1
  selectedJob.value = null
  fetchJobs()
}

watch(currentPage, fetchJobs)

onMounted(() => {
  fetchJobs()
  if (auth.isStudent) checkResume()
})

// ─── Job selection ────────────────────────────────────────────────────────────
function selectJob(job: Job) {
  selectedJob.value = job
}

// ─── Computed helpers ─────────────────────────────────────────────────────────
function isApplied(job: Job): boolean {
  return appliedIds.value.has(job.id) || !!job.applied
}

function formatSalary(job: Job): string {
  if (!job.salary_disclosed || (!job.salary_min && !job.salary_max)) {
    return t('jobs.notDisclosed')
  }
  if (job.salary_min && job.salary_max) {
    return `¥${job.salary_min.toLocaleString()} – ¥${job.salary_max.toLocaleString()}`
  }
  return job.salary_min ? `¥${job.salary_min.toLocaleString()}+` : t('jobs.notDisclosed')
}

function formatDate(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
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

// ─── Apply logic ──────────────────────────────────────────────────────────────
async function apply(job: Job) {
  if (!auth.isLoggedIn) {
    router.push({ path: '/login', query: { redirect: '/jobs' } })
    return
  }

  if (!auth.isStudent) {
    ElMessage.warning('Only students can apply for jobs.')
    return
  }

  if (!hasApprovedResume.value) {
    ElMessage.warning(t('jobs.noResumeWarning'))
    return
  }

  try {
    await ElMessageBox.confirm(
      t('jobs.applyConfirm', { jobTitle: job.title, company: job.company_name }),
      t('jobs.applyNow'),
      { type: 'info', confirmButtonText: t('jobs.applyNow'), cancelButtonText: 'Cancel' },
    )
  } catch {
    return
  }

  applyingJobId.value = job.id
  try {
    await jobsApi.applyForJob(job.id)
    appliedIds.value.add(job.id)
    // Update the selected job state
    if (selectedJob.value?.id === job.id) {
      selectedJob.value = { ...selectedJob.value, applied: true }
    }
    ElMessage.success(t('jobs.applySuccess'))
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    ElMessage.error(err.response?.data?.message ?? 'Failed to submit application.')
  } finally {
    applyingJobId.value = null
  }
}

const canApply = computed(() => {
  if (!selectedJob.value) return false
  if (isApplied(selectedJob.value)) return false
  return auth.isStudent
})
</script>

<template>
  <div class="jobs-page">
    <!-- Page header -->
    <section class="page-hero">
      <div class="hero-inner">
        <h1>{{ t('jobs.pageTitle') }}</h1>
      </div>
    </section>

    <!-- Filters -->
    <section class="filters-section">
      <div class="filters-inner">
        <el-input
          v-model="filters.search"
          :placeholder="t('jobs.searchPlaceholder')"
          clearable
          class="filter-search"
          @keyup.enter="onSearch"
        >
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>

        <el-input
          v-model="filters.location"
          :placeholder="t('jobs.filterLocation')"
          clearable
          class="filter-input"
        />

        <el-select
          v-model="filters.job_type"
          :placeholder="t('jobs.filterType')"
          clearable
          class="filter-select"
        >
          <el-option :label="t('jobs.allTypes')" value="" />
          <el-option
            v-for="jt in jobTypeOptions"
            :key="jt.value"
            :label="t(`jobs.jobTypes.${jt.value}`)"
            :value="jt.value"
          />
        </el-select>

        <el-select
          v-model="filters.industry"
          :placeholder="t('jobs.filterIndustry')"
          clearable
          class="filter-select"
        >
          <el-option :label="t('jobs.allIndustries')" value="" />
          <el-option v-for="ind in industries" :key="ind" :label="ind" :value="ind" />
        </el-select>

        <el-button type="primary" @click="onSearch">
          <el-icon class="el-icon--left"><Search /></el-icon>
          {{ t('jobs.search') }}
        </el-button>
      </div>
    </section>

    <!-- Main content -->
    <section class="jobs-content">
      <div class="jobs-inner">
        <!-- Job list (left column) -->
        <div class="jobs-list-col">
          <div v-if="loading" class="jobs-list">
            <div v-for="i in 6" :key="i" class="job-card-skeleton">
              <el-skeleton animated>
                <template #template>
                  <el-skeleton-item variant="rect" style="height: 110px; border-radius: 10px;" />
                </template>
              </el-skeleton>
            </div>
          </div>

          <div v-else-if="jobs.length" class="jobs-list">
            <div
              v-for="job in jobs"
              :key="job.id"
              class="job-card"
              :class="{ active: selectedJob?.id === job.id, applied: isApplied(job) }"
              @click="selectJob(job)"
            >
              <div class="job-card-header">
                <div class="company-logo">
                  <img v-if="job.company_logo" :src="job.company_logo" :alt="job.company_name" />
                  <span v-else>{{ job.company_name.charAt(0) }}</span>
                </div>
                <div class="job-card-info">
                  <p class="job-title">{{ job.title }}</p>
                  <p class="job-company">{{ job.company_name }}</p>
                </div>
                <el-tag v-if="isApplied(job)" type="success" size="small" class="applied-badge">
                  {{ t('jobs.alreadyApplied') }}
                </el-tag>
              </div>
              <div class="job-card-meta">
                <span><el-icon><Location /></el-icon> {{ job.location }}</span>
                <el-tag :type="typeTagType(job.job_type)" size="small">
                  {{ t(`jobs.jobTypes.${job.job_type}`) }}
                </el-tag>
                <span class="job-date">{{ formatDate(job.posted_at) }}</span>
              </div>
            </div>
          </div>

          <div v-else class="empty-jobs">
            <p>{{ t('jobs.noJobs') }}</p>
          </div>

          <!-- Pagination -->
          <div v-if="totalJobs > perPage" class="list-pagination">
            <el-pagination
              v-model:current-page="currentPage"
              :page-size="perPage"
              :total="totalJobs"
              layout="prev, pager, next"
              small
            />
          </div>
        </div>

        <!-- Job detail panel (right column) -->
        <div class="job-detail-col">
          <div v-if="!selectedJob" class="detail-placeholder">
            <el-icon class="placeholder-icon"><Briefcase /></el-icon>
            <p>{{ t('jobs.selectJob') }}</p>
          </div>

          <div v-else class="job-detail">
            <!-- Company header -->
            <div class="detail-company-header">
              <div class="detail-logo">
                <img v-if="selectedJob.company_logo" :src="selectedJob.company_logo" :alt="selectedJob.company_name" />
                <span v-else>{{ selectedJob.company_name.charAt(0) }}</span>
              </div>
              <div>
                <h2 class="detail-title">{{ selectedJob.title }}</h2>
                <p class="detail-company">{{ selectedJob.company_name }}</p>
                <div class="detail-tags">
                  <el-tag :type="typeTagType(selectedJob.job_type)" size="small">
                    {{ t(`jobs.jobTypes.${selectedJob.job_type}`) }}
                  </el-tag>
                  <span class="detail-location"><el-icon><Location /></el-icon> {{ selectedJob.location }}</span>
                  <span v-if="selectedJob.industry" class="detail-industry">{{ selectedJob.industry }}</span>
                </div>
              </div>
            </div>

            <!-- Salary -->
            <div class="detail-section salary-row">
              <span class="salary-label">{{ t('jobs.salary') }}:</span>
              <span class="salary-value">{{ formatSalary(selectedJob) }}</span>
              <span class="posted-date">{{ t('jobs.postedOn') }}: {{ formatDate(selectedJob.posted_at) }}</span>
            </div>

            <!-- Apply button -->
            <div class="detail-apply">
              <!-- Already applied -->
              <el-tag v-if="isApplied(selectedJob)" type="success" size="large" class="applied-badge-lg">
                <el-icon><Check /></el-icon> {{ t('jobs.alreadyApplied') }}
              </el-tag>

              <!-- Not logged in -->
              <el-button
                v-else-if="!auth.isLoggedIn"
                type="primary"
                size="large"
                @click="router.push({ path: '/login', query: { redirect: '/jobs' } })"
              >
                {{ t('jobs.loginToApply') }}
              </el-button>

              <!-- Student apply -->
              <el-button
                v-else-if="auth.isStudent"
                type="primary"
                size="large"
                :loading="applyingJobId === selectedJob.id"
                @click="apply(selectedJob)"
              >
                {{ t('jobs.applyNow') }}
              </el-button>
            </div>

            <!-- Description -->
            <div class="detail-section">
              <h3 class="section-heading">{{ t('jobs.description') }}</h3>
              <!-- eslint-disable-next-line vue/no-v-html -->
              <div class="rich-content" v-html="sanitize(selectedJob.description)" />
            </div>

            <!-- Requirements -->
            <div v-if="selectedJob.requirements" class="detail-section">
              <h3 class="section-heading">{{ t('jobs.requirements') }}</h3>
              <!-- eslint-disable-next-line vue/no-v-html -->
              <div class="rich-content" v-html="sanitize(selectedJob.requirements)" />
            </div>

            <!-- Company info -->
            <div v-if="selectedJob.company_size || selectedJob.industry" class="detail-section company-info-section">
              <h3 class="section-heading">{{ t('jobs.companyInfo') }}</h3>
              <div class="company-info-grid">
                <div v-if="selectedJob.industry" class="info-item">
                  <span class="info-label">Industry</span>
                  <span class="info-value">{{ selectedJob.industry }}</span>
                </div>
                <div v-if="selectedJob.company_size" class="info-item">
                  <span class="info-label">Company Size</span>
                  <span class="info-value">{{ selectedJob.company_size }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.jobs-page {
  min-height: 100vh;
  background: #f5f7fa;
}

/* Hero */
.page-hero {
  background: linear-gradient(135deg, #003366 0%, #0055a4 100%);
  padding: 48px 0 40px;
}
.hero-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 48px;
}
.page-hero h1 {
  color: #fff;
  font-size: 32px;
  font-weight: 700;
  margin: 0;
}

/* Filters */
.filters-section {
  background: #fff;
  border-bottom: 1px solid #dee2e6;
  padding: 16px 0;
  position: sticky;
  top: 0;
  z-index: 10;
}
.filters-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 48px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}
.filter-search { width: 260px; }
.filter-input  { width: 160px; }
.filter-select { width: 160px; }

/* Main content */
.jobs-content { padding: 24px 0 48px; }
.jobs-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 48px;
  display: grid;
  grid-template-columns: 2fr 3fr;
  gap: 20px;
  align-items: start;
}

/* Job list column */
.jobs-list-col { position: sticky; top: 72px; }
.jobs-list { display: flex; flex-direction: column; gap: 10px; }
.job-card-skeleton { margin-bottom: 4px; }

.job-card {
  background: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 10px;
  padding: 14px 16px;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.job-card:hover { border-color: #0066cc; box-shadow: 0 2px 8px rgba(0,102,204,0.1); }
.job-card.active { border-color: #003366; box-shadow: 0 2px 12px rgba(0,51,102,0.15); }
.job-card.applied { border-color: #86efac; }

.job-card-header {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 8px;
}
.company-logo {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: #e6f0ff;
  color: #003366;
  font-weight: 700;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.company-logo img { width: 100%; height: 100%; object-fit: cover; }
.job-card-info { flex: 1; min-width: 0; }
.job-title {
  margin: 0 0 2px;
  font-size: 14px;
  font-weight: 600;
  color: #1a1a2e;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.job-company {
  margin: 0;
  font-size: 12px;
  color: #6c757d;
}
.applied-badge { flex-shrink: 0; }

.job-card-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12px;
  color: #6c757d;
  flex-wrap: wrap;
}
.job-card-meta span { display: flex; align-items: center; gap: 3px; }
.job-date { margin-left: auto; }

.empty-jobs {
  background: #fff;
  border-radius: 10px;
  padding: 48px;
  text-align: center;
  color: #6c757d;
  font-size: 15px;
}

.list-pagination { display: flex; justify-content: center; margin-top: 16px; }

/* Detail column */
.job-detail-col {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  min-height: 400px;
  overflow: hidden;
}

.detail-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  height: 400px;
  color: #6c757d;
  font-size: 15px;
}
.placeholder-icon { font-size: 48px; color: #dee2e6; }

.job-detail { padding: 24px; }

.detail-company-header {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}
.detail-logo {
  width: 60px;
  height: 60px;
  border-radius: 10px;
  background: #e6f0ff;
  color: #003366;
  font-weight: 700;
  font-size: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.detail-logo img { width: 100%; height: 100%; object-fit: cover; }
.detail-title { font-size: 20px; font-weight: 700; color: #1a1a2e; margin: 0 0 4px; }
.detail-company { font-size: 14px; color: #6c757d; margin: 0 0 8px; }
.detail-tags { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.detail-location, .detail-industry {
  display: flex; align-items: center; gap: 4px;
  font-size: 13px; color: #6c757d;
}

.detail-section { margin-bottom: 20px; }
.salary-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  font-size: 14px;
}
.salary-label { color: #6c757d; }
.salary-value { font-weight: 600; color: #1a1a2e; font-size: 16px; }
.posted-date { margin-left: auto; font-size: 12px; color: #6c757d; }

.detail-apply { margin-bottom: 20px; }
.applied-badge-lg {
  font-size: 15px;
  height: 40px;
  line-height: 38px;
  padding: 0 20px;
}

.section-heading {
  font-size: 15px;
  font-weight: 600;
  color: #003366;
  margin: 0 0 12px;
}
.rich-content {
  font-size: 14px;
  line-height: 1.7;
  color: #1a1a2e;
}
:deep(.rich-content ul), :deep(.rich-content ol) {
  padding-left: 20px;
  margin: 8px 0;
}
:deep(.rich-content p) { margin: 6px 0; }

.company-info-section { background: #f8f9fa; border-radius: 8px; padding: 14px 16px; }
.company-info-grid { display: flex; gap: 24px; flex-wrap: wrap; }
.info-item { display: flex; flex-direction: column; gap: 2px; }
.info-label { font-size: 11px; text-transform: uppercase; color: #6c757d; letter-spacing: 0.05em; }
.info-value { font-size: 14px; font-weight: 500; color: #1a1a2e; }

@media (max-width: 900px) {
  .jobs-inner { grid-template-columns: 1fr; }
  .jobs-list-col { position: static; }
  .hero-inner, .filters-inner, .jobs-inner { padding: 0 20px; }
  .filter-search, .filter-input, .filter-select { width: 100%; }
}
</style>
