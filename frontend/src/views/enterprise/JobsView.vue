<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import enterpriseApi, { type JobData } from '@/api/enterprise'

const { t } = useI18n()

// ─── Types ─────────────────────────────────────────────────────────────────
interface Job {
  id: number
  title_en: string
  title_zh_cn?: string
  title_th?: string
  job_type: string
  location?: string
  salary_min?: number | null
  salary_max?: number | null
  salary_currency?: string
  salary_not_disclosed?: boolean
  description_en?: string
  description_zh_cn?: string
  description_th?: string
  requirements_en?: string
  requirements_zh_cn?: string
  requirements_th?: string
  deadline?: string | null
  industry?: string
  status: 'draft' | 'published' | 'closed'
  applications_count?: number
  created_at: string
}

interface Application {
  id: number
  student_name?: string
  nationality?: string
  resume_url?: string
  created_at: string
  status: string
}

// ─── List state ─────────────────────────────────────────────────────────────
const loading = ref(true)
const jobs = ref<Job[]>([])
const activeTab = ref('all')

const tabs = ['all', 'published', 'draft', 'closed']

const filteredJobs = computed(() =>
  activeTab.value === 'all' ? jobs.value : jobs.value.filter((j) => j.status === activeTab.value),
)

const counts = computed(() => ({
  all:       jobs.value.length,
  published: jobs.value.filter((j) => j.status === 'published').length,
  draft:     jobs.value.filter((j) => j.status === 'draft').length,
  closed:    jobs.value.filter((j) => j.status === 'closed').length,
}))

// ─── Fetch jobs ─────────────────────────────────────────────────────────────
onMounted(fetchJobs)

async function fetchJobs() {
  loading.value = true
  try {
    const { data } = await enterpriseApi.getJobs({ per_page: 100 })
    jobs.value = (data.data ?? data) as Job[]
  } finally {
    loading.value = false
  }
}

// ─── Job form dialog ─────────────────────────────────────────────────────────
const formVisible = ref(false)
const formSaving = ref(false)
const formMode = ref<'create' | 'edit'>('create')
const editingJobId = ref<number | null>(null)
const jobFormTab = ref('en')
const descFormTab = ref('en')
const reqFormTab = ref('en')

const defaultForm = (): Partial<JobData> => ({
  title_en: '', title_zh_cn: '', title_th: '',
  job_type: 'full_time',
  location: '',
  salary_min: null, salary_max: null,
  salary_currency: 'USD', salary_not_disclosed: false,
  description_en: '', description_zh_cn: '', description_th: '',
  requirements_en: '', requirements_zh_cn: '', requirements_th: '',
  deadline: null,
  industry: '',
  status: 'draft',
})

const jobForm = reactive<Partial<JobData>>(defaultForm())

const jobTypeOptions = [
  { label: t('enterprise.jobs.jobTypes.full_time'), value: 'full_time' },
  { label: t('enterprise.jobs.jobTypes.part_time'), value: 'part_time' },
  { label: t('enterprise.jobs.jobTypes.internship'), value: 'internship' },
  { label: t('enterprise.jobs.jobTypes.remote'), value: 'remote' },
]

const industryOptions = [
  'Technology', 'Finance', 'Healthcare', 'Education', 'Manufacturing',
  'Retail', 'Hospitality', 'Construction', 'Media', 'Consulting', 'Other',
]

function openCreateDialog() {
  Object.assign(jobForm, defaultForm())
  jobFormTab.value = 'en'
  descFormTab.value = 'en'
  reqFormTab.value = 'en'
  formMode.value = 'create'
  editingJobId.value = null
  formVisible.value = true
}

function openEditDialog(job: Job) {
  Object.assign(jobForm, {
    title_en:       job.title_en,
    title_zh_cn:    job.title_zh_cn ?? '',
    title_th:       job.title_th ?? '',
    job_type:       job.job_type,
    location:       job.location ?? '',
    salary_min:     job.salary_min ?? null,
    salary_max:     job.salary_max ?? null,
    salary_currency: job.salary_currency ?? 'USD',
    salary_not_disclosed: job.salary_not_disclosed ?? false,
    description_en:  job.description_en ?? '',
    description_zh_cn: job.description_zh_cn ?? '',
    description_th: job.description_th ?? '',
    requirements_en: job.requirements_en ?? '',
    requirements_zh_cn: job.requirements_zh_cn ?? '',
    requirements_th: job.requirements_th ?? '',
    deadline:       job.deadline ?? null,
    industry:       job.industry ?? '',
    status:         job.status,
  })
  jobFormTab.value = 'en'
  descFormTab.value = 'en'
  reqFormTab.value = 'en'
  formMode.value = 'edit'
  editingJobId.value = job.id
  formVisible.value = true
}

async function saveJob(publishNow = false) {
  if (!jobForm.title_en?.trim()) {
    ElMessage.error('Job title (English) is required.')
    return
  }
  formSaving.value = true
  try {
    const payload: Partial<JobData> = {
      ...jobForm,
      status: publishNow ? 'published' : (jobForm.status ?? 'draft'),
    }
    if (formMode.value === 'create') {
      const { data } = await enterpriseApi.createJob(payload)
      jobs.value.unshift((data.data ?? data) as Job)
      ElMessage.success('Job saved.')
    } else if (editingJobId.value !== null) {
      const { data } = await enterpriseApi.updateJob(editingJobId.value, payload)
      const updated = (data.data ?? data) as Job
      const idx = jobs.value.findIndex((j) => j.id === editingJobId.value)
      if (idx !== -1) jobs.value[idx] = updated
      ElMessage.success('Job updated.')
    }
    formVisible.value = false
  } catch {
    ElMessage.error('Failed to save job.')
  } finally {
    formSaving.value = false
  }
}

// ─── Toggle publish ─────────────────────────────────────────────────────────
const togglingId = ref<number | null>(null)

async function togglePublish(job: Job) {
  togglingId.value = job.id
  try {
    if (job.status === 'published') {
      await enterpriseApi.unpublishJob(job.id)
      job.status = 'draft'
      ElMessage.success('Job unpublished.')
    } else {
      await enterpriseApi.publishJob(job.id)
      job.status = 'published'
      ElMessage.success('Job published.')
    }
  } catch {
    ElMessage.error('Failed to update job status.')
  } finally {
    togglingId.value = null
  }
}

// ─── Delete ─────────────────────────────────────────────────────────────────
const deletingId = ref<number | null>(null)

async function deleteJob(job: Job) {
  try {
    await ElMessageBox.confirm(
      t('enterprise.jobs.deleteConfirm'),
      'Delete Job',
      { type: 'warning', confirmButtonText: 'Delete', cancelButtonText: 'Cancel' },
    )
  } catch { return }

  deletingId.value = job.id
  try {
    await enterpriseApi.deleteJob(job.id)
    jobs.value = jobs.value.filter((j) => j.id !== job.id)
    ElMessage.success('Job deleted.')
  } catch {
    ElMessage.error('Failed to delete job.')
  } finally {
    deletingId.value = null
  }
}

// ─── Applications modal ──────────────────────────────────────────────────────
const appsVisible = ref(false)
const appsLoading = ref(false)
const appsJob = ref<Job | null>(null)
const applications = ref<Application[]>([])
const appsStatusFilter = ref('')

const appStatusTypeMap: Record<string, string> = {
  pending: 'warning', reviewed: 'primary', accepted: 'success', rejected: 'danger',
}

async function openApplications(job: Job) {
  appsJob.value = job
  appsVisible.value = true
  appsLoading.value = true
  appsStatusFilter.value = ''
  try {
    const { data } = await enterpriseApi.getApplicationsForJob(job.id)
    applications.value = (data.data ?? data) as Application[]
  } finally {
    appsLoading.value = false
  }
}

const filteredApplications = computed(() =>
  appsStatusFilter.value
    ? applications.value.filter((a) => a.status === appsStatusFilter.value)
    : applications.value,
)

async function changeAppStatus(app: Application, status: string) {
  try {
    await enterpriseApi.updateApplicationStatus(app.id, status)
    app.status = status
    ElMessage.success('Status updated.')
  } catch {
    ElMessage.error('Failed to update status.')
  }
}

// ─── Helpers ────────────────────────────────────────────────────────────────
function formatDate(dt: string) {
  return new Date(dt).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

const statusTypeMap: Record<string, string> = { draft: '', published: 'success', closed: 'danger' }
function statusTagType(s: string) { return statusTypeMap[s] ?? 'info' }
</script>

<template>
  <div class="jobs-page">

    <!-- Header -->
    <div class="page-header">
      <h1 class="page-heading">{{ t('enterprise.jobs.pageTitle') }}</h1>
      <el-button type="primary" @click="openCreateDialog">
        <el-icon class="el-icon--left"><Plus /></el-icon>
        {{ t('enterprise.jobs.postNewJob') }}
      </el-button>
    </div>

    <!-- Status tabs -->
    <div class="status-tabs">
      <button
        v-for="tab in tabs"
        :key="tab"
        class="tab-btn"
        :class="{ active: activeTab === tab }"
        @click="activeTab = tab"
      >
        {{ tab === 'all' ? t('enterprise.jobs.allStatuses') : t(`enterprise.jobs.status.${tab}`) }}
        <span v-if="counts[tab as keyof typeof counts] > 0" class="tab-count">
          {{ counts[tab as keyof typeof counts] }}
        </span>
      </button>
    </div>

    <!-- Loading skeleton -->
    <el-skeleton v-if="loading" animated :rows="5" />

    <!-- Jobs table -->
    <div v-else-if="filteredJobs.length" class="table-card">
      <el-table :data="filteredJobs" style="width:100%" row-key="id">
        <el-table-column :label="t('enterprise.jobs.jobTitle')" min-width="200">
          <template #default="{ row }">
            <p class="job-title-cell">{{ row.title_en }}</p>
            <p class="job-type-cell">{{ row.job_type?.replace('_', ' ') }}</p>
          </template>
        </el-table-column>
        <el-table-column :label="t('enterprise.jobs.location')" prop="location" min-width="120" />
        <el-table-column label="Posted" min-width="110">
          <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
        </el-table-column>
        <el-table-column :label="t('enterprise.jobs.applications')" width="110" align="center">
          <template #default="{ row }">
            <el-button
              link
              type="primary"
              @click="openApplications(row)"
            >
              {{ row.applications_count ?? 0 }}
              <el-icon class="el-icon--right"><ArrowRight /></el-icon>
            </el-button>
          </template>
        </el-table-column>
        <el-table-column label="Status" width="110" align="center">
          <template #default="{ row }">
            <el-tag :type="statusTagType(row.status)" size="small">
              {{ t(`enterprise.jobs.status.${row.status}`) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="200" align="right">
          <template #default="{ row }">
            <el-button size="small" plain @click="openEditDialog(row)">Edit</el-button>
            <el-button
              size="small"
              :type="row.status === 'published' ? 'warning' : 'success'"
              plain
              :loading="togglingId === row.id"
              @click="togglePublish(row)"
            >
              {{ row.status === 'published' ? t('enterprise.jobs.unpublish') : t('enterprise.jobs.publish') }}
            </el-button>
            <el-button
              size="small"
              type="danger"
              plain
              :loading="deletingId === row.id"
              @click="deleteJob(row)"
            >
              Delete
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- Empty state -->
    <div v-else class="empty-state">
      <el-icon class="empty-icon"><Briefcase /></el-icon>
      <p class="empty-title">{{ t('enterprise.jobs.noJobs') }}</p>
      <el-button type="primary" @click="openCreateDialog">
        {{ t('enterprise.jobs.postNewJob') }}
      </el-button>
    </div>

    <!-- ── Job Create/Edit Dialog ────────────────────────────────────────── -->
    <el-dialog
      v-model="formVisible"
      :title="formMode === 'create' ? t('enterprise.jobs.postNewJob') : t('enterprise.jobs.editJob')"
      width="760px"
      align-center
      :close-on-click-modal="false"
    >
      <div class="job-form">

        <!-- Title (multi-lang) -->
        <div class="form-group">
          <label class="form-label">{{ t('enterprise.jobs.jobTitle') }} <span class="req">*</span></label>
          <el-tabs v-model="jobFormTab">
            <el-tab-pane label="English" name="en">
              <el-input v-model="jobForm.title_en" placeholder="Job title in English" />
            </el-tab-pane>
            <el-tab-pane label="中文" name="zh_cn">
              <el-input v-model="jobForm.title_zh_cn" placeholder="职位名称（中文）" />
            </el-tab-pane>
            <el-tab-pane label="ภาษาไทย" name="th">
              <el-input v-model="jobForm.title_th" placeholder="ชื่อตำแหน่ง (ภาษาไทย)" />
            </el-tab-pane>
          </el-tabs>
        </div>

        <!-- Row: Job Type + Location -->
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">{{ t('enterprise.jobs.jobType') }}</label>
            <el-select v-model="jobForm.job_type" style="width:100%">
              <el-option v-for="opt in jobTypeOptions" :key="opt.value" :label="opt.label" :value="opt.value" />
            </el-select>
          </div>
          <div class="form-group">
            <label class="form-label">{{ t('enterprise.jobs.location') }}</label>
            <el-input v-model="jobForm.location" placeholder="City, Country" />
          </div>
        </div>

        <!-- Row: Industry + Deadline -->
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">{{ t('enterprise.jobs.industry') }}</label>
            <el-select v-model="jobForm.industry" clearable style="width:100%">
              <el-option v-for="opt in industryOptions" :key="opt" :label="opt" :value="opt" />
            </el-select>
          </div>
          <div class="form-group">
            <label class="form-label">{{ t('enterprise.jobs.deadline') }}</label>
            <el-date-picker v-model="jobForm.deadline" type="date" value-format="YYYY-MM-DD" style="width:100%" />
          </div>
        </div>

        <!-- Salary -->
        <div class="form-group">
          <label class="form-label">{{ t('enterprise.jobs.salaryRange') }}</label>
          <div class="salary-row">
            <el-checkbox v-model="jobForm.salary_not_disclosed">{{ t('enterprise.jobs.notDisclosed') }}</el-checkbox>
            <template v-if="!jobForm.salary_not_disclosed">
              <el-input-number v-model="jobForm.salary_min as number" :min="0" placeholder="Min" style="width:130px" />
              <span class="salary-dash">–</span>
              <el-input-number v-model="jobForm.salary_max as number" :min="0" placeholder="Max" style="width:130px" />
              <el-select v-model="jobForm.salary_currency" style="width:90px">
                <el-option label="USD" value="USD" />
                <el-option label="CNY" value="CNY" />
                <el-option label="THB" value="THB" />
                <el-option label="EUR" value="EUR" />
              </el-select>
            </template>
          </div>
        </div>

        <!-- Description (multi-lang) -->
        <div class="form-group">
          <label class="form-label">{{ t('enterprise.jobs.description') }}</label>
          <el-tabs v-model="descFormTab">
            <el-tab-pane label="English" name="en">
              <el-input v-model="jobForm.description_en" type="textarea" :rows="4" placeholder="Job description in English..." />
            </el-tab-pane>
            <el-tab-pane label="中文" name="zh_cn">
              <el-input v-model="jobForm.description_zh_cn" type="textarea" :rows="4" placeholder="职位描述（中文）..." />
            </el-tab-pane>
            <el-tab-pane label="ภาษาไทย" name="th">
              <el-input v-model="jobForm.description_th" type="textarea" :rows="4" placeholder="รายละเอียดงาน..." />
            </el-tab-pane>
          </el-tabs>
        </div>

        <!-- Requirements (multi-lang) -->
        <div class="form-group">
          <label class="form-label">{{ t('enterprise.jobs.requirements') }}</label>
          <el-tabs v-model="reqFormTab">
            <el-tab-pane label="English" name="en">
              <el-input v-model="jobForm.requirements_en" type="textarea" :rows="3" placeholder="Requirements in English..." />
            </el-tab-pane>
            <el-tab-pane label="中文" name="zh_cn">
              <el-input v-model="jobForm.requirements_zh_cn" type="textarea" :rows="3" placeholder="任职要求（中文）..." />
            </el-tab-pane>
            <el-tab-pane label="ภาษาไทย" name="th">
              <el-input v-model="jobForm.requirements_th" type="textarea" :rows="3" placeholder="คุณสมบัติ..." />
            </el-tab-pane>
          </el-tabs>
        </div>
      </div>

      <template #footer>
        <el-button @click="formVisible = false">Cancel</el-button>
        <el-button :loading="formSaving" @click="saveJob(false)">
          {{ t('enterprise.jobs.saveAsDraft') }}
        </el-button>
        <el-button type="primary" :loading="formSaving" @click="saveJob(true)">
          {{ t('enterprise.jobs.publish') }}
        </el-button>
      </template>
    </el-dialog>

    <!-- ── Applications Modal ────────────────────────────────────────────── -->
    <el-dialog
      v-model="appsVisible"
      :title="`${t('enterprise.appReview.title')} — ${appsJob?.title_en ?? ''}`"
      width="820px"
      align-center
    >
      <div class="apps-modal">
        <!-- Filter -->
        <div class="apps-filter">
          <el-select v-model="appsStatusFilter" clearable placeholder="All statuses" style="width:160px">
            <el-option label="Pending" value="pending" />
            <el-option label="Reviewed" value="reviewed" />
            <el-option label="Accepted" value="accepted" />
            <el-option label="Rejected" value="rejected" />
          </el-select>
        </div>

        <!-- Loading -->
        <div v-if="appsLoading" class="apps-loading">
          <el-skeleton animated :rows="4" />
        </div>

        <!-- Table -->
        <el-table v-else-if="filteredApplications.length" :data="filteredApplications" size="small">
          <el-table-column :label="t('enterprise.appReview.studentName')" min-width="150">
            <template #default="{ row }">
              <span>{{ row.student_name ?? `#${row.id}` }}</span>
              <small v-if="row.nationality" class="nationality">{{ row.nationality }}</small>
            </template>
          </el-table-column>
          <el-table-column :label="t('enterprise.appReview.appliedDate')" min-width="120">
            <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
          </el-table-column>
          <el-table-column :label="t('enterprise.appReview.status')" width="120">
            <template #default="{ row }">
              <el-tag :type="appStatusTypeMap[row.status] ?? 'info'" size="small">{{ row.status }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Actions" width="230" align="right">
            <template #default="{ row }">
              <el-button
                v-if="row.resume_url"
                size="small"
                plain
                @click="window.open(row.resume_url, '_blank')"
              >
                {{ t('enterprise.appReview.viewResume') }}
              </el-button>
              <el-select
                :model-value="row.status"
                size="small"
                style="width:110px"
                @change="(v: string) => changeAppStatus(row, v)"
              >
                <el-option label="Pending" value="pending" />
                <el-option label="Reviewed" value="reviewed" />
                <el-option label="Accepted" value="accepted" />
                <el-option label="Rejected" value="rejected" />
              </el-select>
            </template>
          </el-table-column>
        </el-table>

        <p v-else class="empty-apps">{{ t('enterprise.appReview.noApplications') }}</p>
      </div>

      <template #footer>
        <el-button @click="appsVisible = false">Close</el-button>
      </template>
    </el-dialog>

  </div>
</template>

<style scoped>
.jobs-page { padding: 8px 0; display: flex; flex-direction: column; gap: 20px; }

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: center; }
.page-heading { font-size: 22px; font-weight: 700; color: #003366; margin: 0; }

/* Tabs */
.status-tabs {
  display: flex;
  border-bottom: 2px solid #dee2e6;
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

/* Table card */
.table-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  overflow: hidden;
}
.job-title-cell { font-size: 14px; font-weight: 600; color: #1a1a2e; margin: 0 0 2px; }
.job-type-cell { font-size: 12px; color: #6c757d; margin: 0; text-transform: capitalize; }

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

/* Job form */
.job-form { display: flex; flex-direction: column; gap: 16px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 13px; font-weight: 500; color: #1a1a2e; }
.req { color: #dc3545; }
.salary-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
.salary-dash { color: #6c757d; }

/* Applications modal */
.apps-modal { display: flex; flex-direction: column; gap: 16px; }
.apps-filter { display: flex; gap: 12px; }
.apps-loading { padding: 16px; }
.empty-apps { text-align: center; color: #6c757d; padding: 32px; font-size: 14px; }
.nationality { display: block; font-size: 11px; color: #6c757d; }
</style>

