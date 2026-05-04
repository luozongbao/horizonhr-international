<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import enterpriseApi from '@/api/enterprise'

const { t } = useI18n()
const router = useRouter()

// ─── Types ────────────────────────────────────────────────────────────────────
interface EducationItem {
  degree?: string
  institution?: string
  year?: string | number
}

interface WorkItem {
  title?: string
  company?: string
  period?: string
}

interface Resume {
  id: number
  student_id?: number
  name?: string
  photo_url?: string
  nationality?: string
  education_level?: string
  availability?: string
  skills?: string[]
  languages?: string[]
  bio?: string
  education?: EducationItem[]
  work_experience?: WorkItem[]
  download_url?: string
  status?: string
}

interface PaginationMeta {
  current_page: number
  last_page: number
  total: number
  per_page: number
}

// ─── State ────────────────────────────────────────────────────────────────────
const resumes = ref<Resume[]>([])
const loading = ref(false)
const meta = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0, per_page: 12 })

const searchQuery = ref('')
const nationalityFilter = ref('')
const educationFilter = ref('')
const availabilityFilter = ref('')
const languageFilter = ref('')
const currentPage = ref(1)
const PER_PAGE = 12

// Detail drawer
const drawerVisible = ref(false)
const selectedResume = ref<Resume | null>(null)
const detailLoading = ref(false)

// Schedule interview dialog
const scheduleDialogVisible = ref(false)
const scheduleLoading = ref(false)
const scheduleForm = ref({
  title: '',
  student_id: undefined as number | undefined,
  scheduled_at: '',
  duration_minutes: 60,
  interviewer_name: '',
  notes: '',
})

// ─── Fetch ────────────────────────────────────────────────────────────────────
async function fetchResumes() {
  loading.value = true
  try {
    const res = await enterpriseApi.getApprovedResumes({
      search: searchQuery.value || undefined,
      nationality: nationalityFilter.value || undefined,
      education_level: educationFilter.value || undefined,
      availability: availabilityFilter.value || undefined,
      language: languageFilter.value || undefined,
      page: currentPage.value,
      per_page: PER_PAGE,
    })
    const data = res.data
    resumes.value = data?.data ?? []
    meta.value = data?.meta ?? { current_page: 1, last_page: 1, total: 0, per_page: PER_PAGE }
  } catch {
    resumes.value = []
  } finally {
    loading.value = false
  }
}

function resetAndFetch() {
  currentPage.value = 1
  fetchResumes()
}

watch([searchQuery, nationalityFilter, educationFilter, availabilityFilter, languageFilter], () => {
  resetAndFetch()
})

function handlePageChange(page: number) {
  currentPage.value = page
  fetchResumes()
}

// ─── Resume detail ────────────────────────────────────────────────────────────
async function openResume(r: Resume) {
  selectedResume.value = r
  drawerVisible.value = true
  detailLoading.value = true
  try {
    const res = await enterpriseApi.getResume(r.id)
    selectedResume.value = res.data?.data ?? res.data ?? r
  } catch {
    // keep existing data
  } finally {
    detailLoading.value = false
  }
}

function downloadPdf(url: string | undefined) {
  if (url) window.open(url, '_blank', 'noopener,noreferrer')
}

// ─── Invite for interview ─────────────────────────────────────────────────────
function openInviteDialog(r: Resume) {
  scheduleForm.value = {
    title: `Interview with ${r.name ?? 'Candidate'}`,
    student_id: r.student_id,
    scheduled_at: '',
    duration_minutes: 60,
    interviewer_name: '',
    notes: '',
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
    await enterpriseApi.scheduleInterview({
      title: scheduleForm.value.title,
      student_id: scheduleForm.value.student_id,
      scheduled_at: scheduleForm.value.scheduled_at,
      duration_minutes: scheduleForm.value.duration_minutes,
      interviewer_name: scheduleForm.value.interviewer_name || undefined,
      notes: scheduleForm.value.notes || undefined,
    })
    ElMessage.success(t('enterprise.interviews.scheduleSuccess'))
    scheduleDialogVisible.value = false
    router.push('/enterprise/interviews')
  } catch {
    ElMessage.error(t('common.error'))
  } finally {
    scheduleLoading.value = false
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function avatarLetter(name: string | undefined): string {
  return (name ?? '?').charAt(0).toUpperCase()
}

const totalText = computed(() => t('enterprise.talent.resultsFound', { count: meta.value.total }))

onMounted(fetchResumes)
</script>

<template>
  <div class="talent-page">
    <div class="page-header">
      <h1 class="page-title">{{ t('enterprise.talent.pageTitle') }}</h1>
    </div>

    <!-- Search bar -->
    <div class="search-bar">
      <el-input
        v-model="searchQuery"
        :placeholder="t('enterprise.talent.searchPlaceholder')"
        clearable
        :prefix-icon="'Search'"
        class="search-input"
        @keyup.enter="resetAndFetch"
      />
      <el-button type="primary" @click="resetAndFetch">{{ t('common.search') }}</el-button>
    </div>

    <!-- Filters -->
    <div class="filters-row">
      <el-select
        v-model="nationalityFilter"
        :placeholder="t('enterprise.talent.filterNationality')"
        clearable
        class="filter-select"
      >
        <el-option :label="t('enterprise.talent.allNationalities')" value="" />
        <el-option label="Thailand" value="Thailand" />
        <el-option label="Indonesia" value="Indonesia" />
        <el-option label="Vietnam" value="Vietnam" />
        <el-option label="Malaysia" value="Malaysia" />
        <el-option label="Myanmar" value="Myanmar" />
        <el-option label="Philippines" value="Philippines" />
        <el-option label="Cambodia" value="Cambodia" />
        <el-option label="Laos" value="Laos" />
      </el-select>

      <el-select
        v-model="educationFilter"
        :placeholder="t('enterprise.talent.filterEducation')"
        clearable
        class="filter-select"
      >
        <el-option :label="t('enterprise.talent.allEducation')" value="" />
        <el-option :label="t('talent.education.bachelor')" value="bachelor" />
        <el-option :label="t('talent.education.master')" value="master" />
        <el-option :label="t('talent.education.phd')" value="phd" />
      </el-select>

      <el-select
        v-model="availabilityFilter"
        :placeholder="t('enterprise.talent.filterAvailability')"
        clearable
        class="filter-select"
      >
        <el-option :label="t('enterprise.talent.allAvailability')" value="" />
        <el-option :label="t('talent.availability.internship')" value="internship" />
        <el-option :label="t('talent.availability.fulltime')" value="fulltime" />
      </el-select>

      <el-select
        v-model="languageFilter"
        :placeholder="t('enterprise.talent.filterLanguage')"
        clearable
        class="filter-select"
      >
        <el-option :label="t('enterprise.talent.allLanguages')" value="" />
        <el-option label="English" value="en" />
        <el-option label="Chinese" value="zh_cn" />
        <el-option label="Thai" value="th" />
      </el-select>
    </div>

    <!-- Results count -->
    <div class="results-info">
      <span class="results-count">{{ totalText }}</span>
    </div>

    <!-- Loading skeletons -->
    <div v-if="loading" class="talent-grid">
      <el-skeleton v-for="i in 6" :key="i" animated>
        <template #template>
          <el-skeleton-item variant="rect" style="height: 200px; border-radius: 8px;" />
        </template>
      </el-skeleton>
    </div>

    <!-- Talent grid -->
    <div v-else-if="resumes.length" class="talent-grid">
      <div
        v-for="r in resumes"
        :key="r.id"
        class="talent-card"
      >
        <div class="talent-header">
          <div class="talent-avatar">
            <img v-if="r.photo_url" :src="r.photo_url" :alt="r.name" class="avatar-img" />
            <span v-else>{{ avatarLetter(r.name) }}</span>
          </div>
          <div class="talent-info">
            <h4>{{ r.name }}</h4>
            <div class="talent-meta">
              <span v-if="r.nationality">{{ r.nationality }}</span>
            </div>
          </div>
        </div>

        <div v-if="r.education_level" class="talent-edu">
          {{ r.education_level }}
        </div>

        <div v-if="r.languages?.length" class="talent-languages">
          <el-tag
            v-for="lang in r.languages.slice(0, 3)"
            :key="lang"
            size="small"
            type="info"
            class="lang-tag"
          >
            {{ lang }}
          </el-tag>
        </div>

        <div v-if="r.availability" class="talent-availability">
          {{ r.availability }}
        </div>

        <div class="talent-actions">
          <el-button size="small" type="primary" @click="openResume(r)">
            {{ t('enterprise.talent.viewResume') }}
          </el-button>
          <el-button size="small" @click="openInviteDialog(r)">
            {{ t('enterprise.talent.inviteInterview') }}
          </el-button>
        </div>
      </div>
    </div>

    <!-- Empty -->
    <el-empty v-else :description="t('enterprise.talent.noResults')" />

    <!-- Pagination -->
    <div v-if="meta.last_page > 1" class="pagination-wrap">
      <el-pagination
        v-model:current-page="currentPage"
        :page-size="PER_PAGE"
        :total="meta.total"
        layout="prev, pager, next"
        background
        @current-change="handlePageChange"
      />
    </div>

    <!-- Resume detail drawer -->
    <el-drawer
      v-model="drawerVisible"
      direction="rtl"
      size="500px"
      :title="selectedResume?.name ?? t('enterprise.talent.viewResume')"
    >
      <div v-if="detailLoading" class="drawer-loading">
        <el-skeleton animated :rows="6" />
      </div>
      <div v-else-if="selectedResume" class="resume-detail">
        <!-- Avatar + basic info -->
        <div class="resume-hero">
          <div class="resume-avatar">
            <img v-if="selectedResume.photo_url" :src="selectedResume.photo_url" :alt="selectedResume.name" class="avatar-img-lg" />
            <span v-else>{{ avatarLetter(selectedResume.name) }}</span>
          </div>
          <div>
            <h2>{{ selectedResume.name }}</h2>
            <p v-if="selectedResume.nationality" class="detail-meta">{{ selectedResume.nationality }}</p>
            <p v-if="selectedResume.education_level" class="detail-meta">{{ selectedResume.education_level }}</p>
            <p v-if="selectedResume.availability" class="detail-meta">{{ selectedResume.availability }}</p>
          </div>
        </div>

        <!-- Bio -->
        <div v-if="selectedResume.bio" class="detail-section">
          <h3>{{ t('talent.bio') }}</h3>
          <p>{{ selectedResume.bio }}</p>
        </div>

        <!-- Education -->
        <div v-if="selectedResume.education?.length" class="detail-section">
          <h3>{{ t('talent.education_label') }}</h3>
          <div v-for="(edu, idx) in selectedResume.education" :key="idx" class="detail-item">
            <strong>{{ edu.degree }}</strong>
            <span v-if="edu.institution"> — {{ edu.institution }}</span>
            <span v-if="edu.year" class="text-muted"> ({{ edu.year }})</span>
          </div>
        </div>

        <!-- Work experience -->
        <div v-if="selectedResume.work_experience?.length" class="detail-section">
          <h3>{{ t('talent.workExperience') }}</h3>
          <div v-for="(work, idx) in selectedResume.work_experience" :key="idx" class="detail-item">
            <strong>{{ work.title }}</strong>
            <span v-if="work.company"> @ {{ work.company }}</span>
            <span v-if="work.period" class="text-muted"> ({{ work.period }})</span>
          </div>
        </div>

        <!-- Languages -->
        <div v-if="selectedResume.languages?.length" class="detail-section">
          <h3>{{ t('talent.languages') }}</h3>
          <div class="tags-row">
            <el-tag v-for="lang in selectedResume.languages" :key="lang" type="info" class="lang-tag">{{ lang }}</el-tag>
          </div>
        </div>

        <!-- Skills -->
        <div v-if="selectedResume.skills?.length" class="detail-section">
          <h3>{{ t('talent.skills') }}</h3>
          <div class="tags-row">
            <el-tag v-for="skill in selectedResume.skills" :key="skill" class="skill-tag">{{ skill }}</el-tag>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="drawer-actions">
          <el-button
            v-if="selectedResume.download_url"
            type="default"
            @click="downloadPdf(selectedResume.download_url)"
          >
            {{ t('enterprise.talent.downloadResume') }}
          </el-button>
          <el-button type="primary" @click="openInviteDialog(selectedResume)">
            {{ t('enterprise.talent.inviteInterview') }}
          </el-button>
        </div>
      </div>
    </el-drawer>

    <!-- Schedule Interview dialog -->
    <el-dialog
      v-model="scheduleDialogVisible"
      :title="t('enterprise.talent.scheduleInterview')"
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
          {{ t('enterprise.interviews.scheduleNew') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.talent-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; color: #003366; }

.search-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
}
.search-input { flex: 1; }

.filters-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 16px;
}
.filter-select { min-width: 160px; }

.results-info {
  margin-bottom: 16px;
  font-size: 14px;
  color: #6c757d;
}

.talent-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.talent-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 16px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.talent-card:hover {
  border-color: #003366;
  box-shadow: 0 4px 12px rgba(0, 51, 102, 0.08);
}

.talent-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}
.talent-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #e6f0ff;
  color: #003366;
  font-weight: 600;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}
.avatar-img { width: 100%; height: 100%; object-fit: cover; }
.avatar-img-lg { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

.talent-info h4 { font-size: 14px; font-weight: 600; margin: 0; }
.talent-meta { font-size: 12px; color: #6c757d; margin-top: 2px; }

.talent-edu {
  font-size: 12px;
  color: #1a1a2e;
  margin-bottom: 8px;
}
.talent-languages {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 8px;
}
.lang-tag { font-size: 11px; }

.talent-availability {
  font-size: 12px;
  color: #ff6b35;
  font-weight: 500;
  margin-bottom: 12px;
}
.talent-actions {
  display: flex;
  gap: 8px;
}

.pagination-wrap { display: flex; justify-content: center; margin-top: 24px; }

/* Drawer */
.drawer-loading { padding: 16px; }
.resume-detail { padding: 4px; }

.resume-hero {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
}
.resume-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #e6f0ff;
  color: #003366;
  font-weight: 700;
  font-size: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}
.resume-hero h2 { font-size: 18px; font-weight: 600; margin: 0 0 4px; }
.detail-meta { font-size: 13px; color: #6c757d; margin: 2px 0; }

.detail-section { margin-bottom: 20px; }
.detail-section h3 { font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #003366; }
.detail-item { font-size: 13px; margin-bottom: 6px; }
.text-muted { color: #6c757d; }

.tags-row { display: flex; flex-wrap: wrap; gap: 6px; }
.skill-tag { font-size: 11px; }

.drawer-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #dee2e6;
}
</style>

