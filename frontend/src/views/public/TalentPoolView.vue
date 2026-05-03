<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { publicApi } from '@/api/public'
import { useAuthStore } from '@/stores/auth'
import { useSanitize } from '@/composables/useSanitize'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()
const { sanitize } = useSanitize()

/* ─── Types ──────────────────────────────────────────────── */
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
  pdf_url?: string
  status?: string
}

interface PaginationMeta {
  current_page: number
  last_page: number
  total: number
  per_page: number
}

/* ─── State ──────────────────────────────────────────────── */
const resumes = ref<Resume[]>([])
const loading = ref(false)
const meta = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0, per_page: 12 })

const searchQuery = ref('')
const nationalityFilter = ref('')
const educationFilter = ref('')
const availabilityFilter = ref('')
const currentPage = ref(1)
const PER_PAGE = 12

// Detail modal
const modalVisible = ref(false)
const selectedResume = ref<Resume | null>(null)
const detailLoading = ref(false)

/* ─── Fetch ──────────────────────────────────────────────── */
async function fetchResumes() {
  loading.value = true
  try {
    const res = await publicApi.getResumes({
      search: searchQuery.value || undefined,
      nationality: nationalityFilter.value || undefined,
      education_level: educationFilter.value || undefined,
      availability: availabilityFilter.value || undefined,
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

watch([searchQuery, nationalityFilter, educationFilter, availabilityFilter], () => {
  resetAndFetch()
})

function handlePageChange(page: number) {
  currentPage.value = page
  fetchResumes()
  document.getElementById('talent-grid')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

/* ─── Resume Detail ──────────────────────────────────────── */
async function openResume(r: Resume) {
  selectedResume.value = r
  modalVisible.value = true
  detailLoading.value = true
  try {
    const res = await publicApi.getResume(r.id)
    selectedResume.value = res.data?.data ?? r
  } catch {
    // use existing data
  } finally {
    detailLoading.value = false
  }
}

function closeModal() {
  modalVisible.value = false
  selectedResume.value = null
}

function downloadPdf(url: string) {
  window.open(url, '_blank', 'noopener,noreferrer')
}

function inviteInterview(studentId: number | undefined) {
  router.push({ path: '/enterprise/interviews', query: { student_id: studentId } })
}

onMounted(fetchResumes)
</script>

<template>
  <div class="talent-page">

    <!-- Page Hero -->
    <section class="page-hero">
      <div class="container">
        <h1 class="hero-title">{{ t('talent.pageTitle') }}</h1>
        <p class="hero-subtitle">{{ t('talent.subtitle') }}</p>
        <nav class="breadcrumb">
          <router-link to="/">{{ t('nav.home') }}</router-link>
          <span class="sep">/</span>
          <span>{{ t('talent.pageTitle') }}</span>
        </nav>
      </div>
    </section>

    <!-- Filters -->
    <section class="filter-section">
      <div class="container">
        <div class="filter-bar">
          <el-input
            v-model="searchQuery"
            :placeholder="t('talent.searchPlaceholder')"
            clearable
            class="filter-search"
            prefix-icon="Search"
          />
          <el-select
            v-model="nationalityFilter"
            :placeholder="t('talent.filterNationality')"
            clearable
            class="filter-select"
          >
            <el-option value="" :label="t('talent.allNationalities')" />
            <el-option value="TH" label="Thailand" />
            <el-option value="VN" label="Vietnam" />
            <el-option value="MM" label="Myanmar" />
            <el-option value="ID" label="Indonesia" />
            <el-option value="MY" label="Malaysia" />
            <el-option value="PH" label="Philippines" />
            <el-option value="KH" label="Cambodia" />
            <el-option value="LA" label="Laos" />
          </el-select>
          <el-select
            v-model="educationFilter"
            :placeholder="t('talent.filterEducation')"
            clearable
            class="filter-select"
          >
            <el-option value="" :label="t('talent.allEducation')" />
            <el-option value="bachelor" :label="t('talent.education.bachelor')" />
            <el-option value="master" :label="t('talent.education.master')" />
            <el-option value="phd" :label="t('talent.education.phd')" />
          </el-select>
          <el-select
            v-model="availabilityFilter"
            :placeholder="t('talent.filterAvailability')"
            clearable
            class="filter-select"
          >
            <el-option value="" :label="t('talent.allAvailability')" />
            <el-option value="internship" :label="t('talent.availability.internship')" />
            <el-option value="fulltime" :label="t('talent.availability.fulltime')" />
          </el-select>
        </div>
      </div>
    </section>

    <!-- Talent Grid -->
    <section id="talent-grid" class="section section-bg">
      <div class="container">
        <template v-if="loading">
          <div class="talent-grid">
            <el-skeleton v-for="i in PER_PAGE" :key="i" style="height:280px" animated />
          </div>
        </template>
        <template v-else-if="resumes.length === 0">
          <el-empty :description="t('talent.noResults')" />
        </template>
        <template v-else>
          <div class="talent-grid">
            <div v-for="r in resumes" :key="r.id" class="talent-card">
              <div class="card-avatar-wrap">
                <img
                  v-if="r.photo_url"
                  :src="r.photo_url"
                  :alt="r.name"
                  class="card-avatar"
                />
                <div v-else class="card-avatar-placeholder">
                  {{ r.name?.charAt(0)?.toUpperCase() ?? '?' }}
                </div>
              </div>
              <div class="card-body">
                <h3 class="card-name">{{ r.name }}</h3>
                <div class="card-meta">
                  <span v-if="r.nationality" class="card-nationality">{{ r.nationality }}</span>
                  <span v-if="r.education_level" class="card-edu">{{ t(`talent.education.${r.education_level}`) }}</span>
                </div>
                <div v-if="r.availability" class="availability-badge" :class="`avail-${r.availability}`">
                  {{ t(`talent.availability.${r.availability}`) }}
                </div>
                <div v-if="r.skills?.length" class="skills-row">
                  <span v-for="s in r.skills.slice(0, 3)" :key="s" class="skill-chip">{{ s }}</span>
                  <span v-if="r.skills.length > 3" class="skill-more">+{{ r.skills.length - 3 }}</span>
                </div>
              </div>
              <div class="card-footer">
                <el-button type="primary" size="small" @click="openResume(r)">
                  {{ t('talent.viewResume') }}
                </el-button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="meta.last_page > 1" class="pagination-wrap">
            <el-pagination
              :current-page="currentPage"
              :page-size="PER_PAGE"
              :total="meta.total"
              layout="prev, pager, next"
              @current-change="handlePageChange"
            />
          </div>
        </template>
      </div>
    </section>

    <!-- Resume Detail Dialog -->
    <el-dialog
      v-model="modalVisible"
      width="720px"
      :before-close="closeModal"
      class="resume-dialog"
    >
      <template #header>
        <div class="dialog-header-slot">
          <img
            v-if="selectedResume?.photo_url"
            :src="selectedResume.photo_url"
            :alt="selectedResume?.name"
            class="dialog-avatar"
          />
          <div v-else class="dialog-avatar-placeholder">
            {{ selectedResume?.name?.charAt(0)?.toUpperCase() ?? '?' }}
          </div>
          <div>
            <h3 class="dialog-name">{{ selectedResume?.name }}</h3>
            <div class="dialog-meta">
              <span v-if="selectedResume?.nationality">{{ selectedResume.nationality }}</span>
              <span v-if="selectedResume?.education_level"> · {{ t(`talent.education.${selectedResume.education_level}`) }}</span>
            </div>
          </div>
        </div>
      </template>

      <div v-if="detailLoading" class="dialog-loading">
        <el-skeleton :rows="6" animated />
      </div>
      <div v-else-if="selectedResume" class="dialog-content">
        <!-- Bio -->
        <div v-if="selectedResume.bio" class="resume-section">
          <h4 class="section-label">{{ t('talent.bio') }}</h4>
          <p class="resume-text">{{ selectedResume.bio }}</p>
        </div>

        <!-- Skills -->
        <div v-if="selectedResume.skills?.length" class="resume-section">
          <h4 class="section-label">{{ t('talent.skills') }}</h4>
          <div class="skills-row">
            <span v-for="s in selectedResume.skills" :key="s" class="skill-chip">{{ s }}</span>
          </div>
        </div>

        <!-- Languages -->
        <div v-if="selectedResume.languages?.length" class="resume-section">
          <h4 class="section-label">{{ t('talent.languages') }}</h4>
          <div class="skills-row">
            <span v-for="l in selectedResume.languages" :key="l" class="skill-chip">{{ l }}</span>
          </div>
        </div>

        <!-- Education -->
        <div v-if="selectedResume.education?.length" class="resume-section">
          <h4 class="section-label">{{ t('talent.education_label') }}</h4>
          <div v-for="(edu, i) in selectedResume.education" :key="i" class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <strong>{{ edu.degree }}</strong>
              <span class="timeline-sub">{{ edu.institution }} {{ edu.year ? `· ${edu.year}` : '' }}</span>
            </div>
          </div>
        </div>

        <!-- Work Experience -->
        <div v-if="selectedResume.work_experience?.length" class="resume-section">
          <h4 class="section-label">{{ t('talent.workExperience') }}</h4>
          <div v-for="(w, i) in selectedResume.work_experience" :key="i" class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <strong>{{ w.title }}</strong>
              <span class="timeline-sub">{{ w.company }} {{ w.period ? `· ${w.period}` : '' }}</span>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <el-button @click="closeModal">{{ t('studyInChina.close') }}</el-button>
        <el-button
          v-if="selectedResume?.pdf_url"
          @click="downloadPdf(selectedResume.pdf_url)"
        >{{ t('talent.downloadResume') }}</el-button>
        <el-button
          v-if="auth.isEnterprise && selectedResume?.student_id"
          type="primary"
          @click="inviteInterview(selectedResume.student_id)"
        >{{ t('talent.inviteInterview') }}</el-button>
      </template>
    </el-dialog>

  </div>
</template>

<style scoped>
.talent-page {
  --c-primary: #003366;
  --c-accent: #FF6B35;
  --c-secondary: #E6F0FF;
  --c-text: #1A1A2E;
  --c-muted: #6C757D;
  --c-border: #DEE2E6;
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 48px; }
.section { padding: 48px 0; }
.section-bg { background: #f8faff; }

/* Hero */
.page-hero {
  background: linear-gradient(135deg, var(--c-primary) 0%, #004080 100%);
  padding: 48px 0;
  color: #fff;
}
.hero-title { font-size: 40px; font-weight: 700; margin-bottom: 8px; }
.hero-subtitle { font-size: 16px; opacity: 0.85; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.8; }
.breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.breadcrumb a:hover { color: #fff; }
.sep { opacity: 0.5; }

/* Filters */
.filter-section { background: #fff; border-bottom: 1px solid var(--c-border); padding: 20px 0; }
.filter-bar { display: flex; gap: 12px; flex-wrap: wrap; }
.filter-search { flex: 1; min-width: 200px; }
.filter-select { width: 180px; }

/* Grid */
.talent-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px; }

.talent-card {
  background: #fff;
  border: 1px solid var(--c-border);
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.2s;
}
.talent-card:hover { box-shadow: 0 4px 16px rgba(0,51,102,0.10); }

.card-avatar-wrap {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px 24px 12px;
}
.card-avatar { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; }
.card-avatar-placeholder {
  width: 72px; height: 72px; border-radius: 50%;
  background: var(--c-secondary);
  color: var(--c-primary);
  font-size: 28px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}

.card-body { padding: 0 16px 12px; flex: 1; text-align: center; }
.card-name { font-size: 16px; font-weight: 600; color: var(--c-primary); margin-bottom: 6px; }
.card-meta { display: flex; gap: 8px; justify-content: center; font-size: 13px; color: var(--c-muted); margin-bottom: 8px; }
.card-nationality, .card-edu { padding: 2px 8px; background: #f0f0f0; border-radius: 10px; }

.availability-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  margin-bottom: 10px;
}
.avail-internship { background: #fff3e0; color: #e65100; }
.avail-fulltime { background: #e8f5e9; color: #2e7d32; }

.skills-row { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; }
.skill-chip { padding: 2px 10px; background: var(--c-secondary); color: var(--c-primary); border-radius: 12px; font-size: 12px; font-weight: 500; }
.skill-more { padding: 2px 8px; background: #f0f0f0; color: var(--c-muted); border-radius: 12px; font-size: 12px; }

.card-footer {
  padding: 12px 16px;
  border-top: 1px solid var(--c-border);
  display: flex;
  justify-content: center;
}

.pagination-wrap { display: flex; justify-content: center; padding-top: 16px; }

/* Dialog */
.dialog-header-slot { display: flex; align-items: center; gap: 16px; }
.dialog-avatar { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; }
.dialog-avatar-placeholder {
  width: 64px; height: 64px; border-radius: 50%;
  background: var(--c-secondary); color: var(--c-primary);
  font-size: 24px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.dialog-name { font-size: 20px; font-weight: 600; color: var(--c-primary); }
.dialog-meta { font-size: 14px; color: var(--c-muted); margin-top: 4px; }
.dialog-loading { min-height: 200px; }

.resume-section { margin-bottom: 20px; }
.section-label { font-size: 14px; font-weight: 600; color: var(--c-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px; }
.resume-text { font-size: 14px; color: var(--c-text); line-height: 1.7; }

.timeline-item { display: flex; gap: 12px; margin-bottom: 10px; }
.timeline-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--c-primary); margin-top: 5px; flex-shrink: 0; }
.timeline-content { display: flex; flex-direction: column; gap: 2px; }
.timeline-content strong { font-size: 14px; color: var(--c-text); }
.timeline-sub { font-size: 13px; color: var(--c-muted); }

/* Responsive */
@media (max-width: 1024px) {
  .talent-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .container { padding: 0 24px; }
  .hero-title { font-size: 28px; }
  .talent-grid { grid-template-columns: 1fr; }
  .filter-select { width: 100%; }
}
</style>
