<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'
import { useSanitize } from '@/composables/useSanitize'

const { t } = useI18n()
const { sanitize } = useSanitize()

/* ─── Types ─────────────────────────────────────────── */
interface CmsPage {
  title?: string
  content?: string
  meta?: Record<string, unknown>
}

interface University {
  id: number
  name: string
  name_zh?: string
  logo_url?: string
  website?: string
  location?: string
  region?: string
  programs?: string[]
  description?: string
  scholarships?: string
  hsk_required?: string
}

interface PaginationMeta {
  current_page: number
  last_page: number
  total: number
  per_page: number
}

/* ─── State ──────────────────────────────────────────── */
const page = ref<CmsPage | null>(null)
const pageLoading = ref(true)

const universities = ref<University[]>([])
const univLoading = ref(false)
const paginationMeta = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0, per_page: 12 })

const searchQuery = ref('')
const regionFilter = ref('')
const programFilter = ref('')
const currentPage = ref(1)
const PER_PAGE = 12

// Detail dialog
const dialogVisible = ref(false)
const selectedUniversity = ref<University | null>(null)
const detailLoading = ref(false)

/* ─── Why Study Cards ────────────────────────────────── */
const whyCards = computed(() => [
  { icon: '&#128176;', titleKey: 'studyInChina.why.tuition', descKey: 'studyInChina.why.tuitionDesc' },
  { icon: '&#127968;', titleKey: 'studyInChina.why.culture', descKey: 'studyInChina.why.cultureDesc' },
  { icon: '&#128218;', titleKey: 'studyInChina.why.hsk', descKey: 'studyInChina.why.hskDesc' },
  { icon: '&#128188;', titleKey: 'studyInChina.why.career', descKey: 'studyInChina.why.careerDesc' },
])

/* ─── Data Fetching ──────────────────────────────────── */
async function fetchPage() {
  try {
    const res = await publicApi.getAboutPage()
    page.value = res.data?.data ?? null
  } catch {
    // use defaults
  } finally {
    pageLoading.value = false
  }
}

async function fetchUniversities() {
  univLoading.value = true
  try {
    const res = await publicApi.getUniversities({
      search: searchQuery.value || undefined,
      region: regionFilter.value || undefined,
      program_type: programFilter.value || undefined,
      page: currentPage.value,
      per_page: PER_PAGE,
    })
    const data = res.data
    universities.value = data?.data ?? []
    paginationMeta.value = data?.meta ?? { current_page: 1, last_page: 1, total: 0, per_page: PER_PAGE }
  } catch {
    universities.value = []
  } finally {
    univLoading.value = false
  }
}

function resetAndFetch() {
  currentPage.value = 1
  fetchUniversities()
}

watch([searchQuery, regionFilter, programFilter], () => {
  resetAndFetch()
})

function handlePageChange(newPage: number) {
  currentPage.value = newPage
  fetchUniversities()
  const el = document.getElementById('university-directory')
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function openDetail(u: University) {
  selectedUniversity.value = u
  dialogVisible.value = true
  detailLoading.value = true
  try {
    const res = await publicApi.getUniversity(u.id)
    selectedUniversity.value = res.data?.data ?? u
  } catch {
    // use existing data
  } finally {
    detailLoading.value = false
  }
}

function closeDetail() {
  dialogVisible.value = false
  selectedUniversity.value = null
}

onMounted(() => {
  fetchPage()
  fetchUniversities()
})
</script>

<template>
  <div class="study-page">

    <!-- Page Hero -->
    <section class="page-hero">
      <div class="container">
        <h1 class="hero-title">{{ t('studyInChina.pageTitle') }}</h1>
        <nav class="breadcrumb">
          <router-link to="/">{{ t('nav.home') }}</router-link>
          <span class="sep">/</span>
          <span>{{ t('studyInChina.pageTitle') }}</span>
        </nav>
      </div>
    </section>

    <!-- Intro (CMS) -->
    <section class="section section-white">
      <div class="container">
        <template v-if="pageLoading">
          <el-skeleton :rows="4" animated />
        </template>
        <template v-else>
          <h2 class="section-title">{{ t('studyInChina.introTitle') }}</h2>
          <div
            v-if="page?.content"
            class="rich-content intro-content"
            v-html="sanitize(page.content)"
          ></div>
        </template>
      </div>
    </section>

    <!-- Why Study in China -->
    <section class="section section-light">
      <div class="container">
        <h2 class="section-title">{{ t('studyInChina.whyTitle') }}</h2>
        <div class="why-grid">
          <div v-for="card in whyCards" :key="card.titleKey" class="why-card">
            <div class="why-icon" v-html="card.icon"></div>
            <h3>{{ t(card.titleKey) }}</h3>
            <p>{{ t(card.descKey) }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- University Directory -->
    <section id="university-directory" class="section section-secondary">
      <div class="container">
        <h2 class="section-title">{{ t('studyInChina.pageTitle') }}</h2>

        <!-- Filters -->
        <div class="filter-bar">
          <el-input
            v-model="searchQuery"
            :placeholder="t('studyInChina.searchPlaceholder')"
            clearable
            class="filter-search"
            prefix-icon="Search"
          />
          <el-select
            v-model="regionFilter"
            :placeholder="t('studyInChina.filterRegion')"
            clearable
            class="filter-select"
          >
            <el-option value="" :label="t('studyInChina.allRegions')" />
            <el-option value="north" label="North China" />
            <el-option value="east" label="East China" />
            <el-option value="south" label="South China" />
            <el-option value="central" label="Central China" />
            <el-option value="southwest" label="Southwest China" />
            <el-option value="northwest" label="Northwest China" />
            <el-option value="northeast" label="Northeast China" />
          </el-select>
          <el-select
            v-model="programFilter"
            :placeholder="t('studyInChina.filterProgram')"
            clearable
            class="filter-select"
          >
            <el-option value="" :label="t('studyInChina.allPrograms')" />
            <el-option value="bachelor" :label="t('studyInChina.programs.bachelor')" />
            <el-option value="master" :label="t('studyInChina.programs.master')" />
            <el-option value="phd" :label="t('studyInChina.programs.phd')" />
            <el-option value="language" :label="t('studyInChina.programs.language')" />
          </el-select>
        </div>

        <!-- University grid -->
        <template v-if="univLoading">
          <div class="univ-grid">
            <el-skeleton v-for="i in PER_PAGE" :key="i" style="height:280px" animated />
          </div>
        </template>
        <template v-else-if="universities.length === 0">
          <el-empty :description="t('studyInChina.noUniversities')" />
        </template>
        <template v-else>
          <div class="univ-grid">
            <div
              v-for="u in universities"
              :key="u.id"
              class="univ-card"
            >
              <div class="univ-logo-wrap">
                <img v-if="u.logo_url" :src="u.logo_url" :alt="u.name" class="univ-logo" />
                <div v-else class="univ-logo-placeholder">&#127979;</div>
              </div>
              <div class="univ-body">
                <h3 class="univ-name">{{ u.name }}</h3>
                <p v-if="u.location" class="univ-location">
                  <span>&#128205;</span> {{ u.location }}
                </p>
                <div v-if="u.programs?.length" class="program-chips">
                  <span v-for="p in u.programs.slice(0, 3)" :key="p" class="chip">{{ p }}</span>
                </div>
              </div>
              <div class="univ-footer">
                <el-button type="primary" size="small" @click="openDetail(u)">
                  {{ t('studyInChina.viewDetails') }}
                </el-button>
                <a
                  v-if="u.website"
                  :href="u.website"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="univ-website-link"
                >{{ t('studyInChina.website') }}</a>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="paginationMeta.last_page > 1" class="pagination-wrap">
            <el-pagination
              :current-page="currentPage"
              :page-size="PER_PAGE"
              :total="paginationMeta.total"
              layout="prev, pager, next"
              @current-change="handlePageChange"
            />
          </div>
        </template>
      </div>
    </section>

    <!-- University Detail Dialog -->
    <el-dialog
      v-model="dialogVisible"
      :title="t('studyInChina.universityDetail')"
      width="640px"
      :before-close="closeDetail"
      class="univ-dialog"
    >
      <div v-if="detailLoading" class="dialog-loading">
        <el-skeleton :rows="6" animated />
      </div>
      <div v-else-if="selectedUniversity" class="dialog-content">
        <div class="dialog-header">
          <img
            v-if="selectedUniversity.logo_url"
            :src="selectedUniversity.logo_url"
            :alt="selectedUniversity.name"
            class="dialog-logo"
          />
          <div v-else class="dialog-logo-placeholder">&#127979;</div>
          <div>
            <h3 class="dialog-univ-name">{{ selectedUniversity.name }}</h3>
            <p v-if="selectedUniversity.name_zh" class="dialog-univ-name-zh">{{ selectedUniversity.name_zh }}</p>
          </div>
        </div>
        <el-divider />
        <div class="dialog-info">
          <div v-if="selectedUniversity.location" class="info-row">
            <strong>{{ t('studyInChina.location') }}:</strong>
            <span>{{ selectedUniversity.location }}</span>
          </div>
          <div v-if="selectedUniversity.programs?.length" class="info-row">
            <strong>{{ t('studyInChina.majors') }}:</strong>
            <div class="program-chips">
              <span v-for="p in selectedUniversity.programs" :key="p" class="chip">{{ p }}</span>
            </div>
          </div>
          <div v-if="selectedUniversity.hsk_required" class="info-row">
            <strong>HSK:</strong>
            <span>{{ selectedUniversity.hsk_required }}</span>
          </div>
          <div v-if="selectedUniversity.scholarships" class="info-row">
            <strong>Scholarships:</strong>
            <span>{{ selectedUniversity.scholarships }}</span>
          </div>
          <div v-if="selectedUniversity.description" class="info-row description">
            <div class="rich-content" v-html="sanitize(selectedUniversity.description)"></div>
          </div>
        </div>
      </div>
      <template #footer>
        <el-button @click="closeDetail">{{ t('studyInChina.close') }}</el-button>
        <el-button
          v-if="selectedUniversity?.website"
          type="primary"
          tag="a"
          :href="selectedUniversity.website"
          target="_blank"
          rel="noopener noreferrer"
        >{{ t('studyInChina.website') }}</el-button>
      </template>
    </el-dialog>

  </div>
</template>

<style scoped>
.study-page {
  --c-primary: #003366;
  --c-primary-light: #004080;
  --c-secondary: #E6F0FF;
  --c-accent: #FF6B35;
  --c-text: #1A1A2E;
  --c-muted: #6C757D;
  --c-border: #DEE2E6;
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 48px; }
.section { padding: 64px 0; }
.section-white { background: #fff; }
.section-light { background: #F5F7FA; }
.section-secondary { background: #f8faff; }

/* Hero */
.page-hero {
  background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-light) 100%);
  padding: 48px 0;
  color: #fff;
}
.hero-title { font-size: 40px; font-weight: 700; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.8; }
.breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.breadcrumb a:hover { color: #fff; }
.sep { opacity: 0.5; }

.section-title { font-size: 32px; font-weight: 600; color: var(--c-primary); text-align: center; margin-bottom: 40px; }

.rich-content { font-size: 16px; color: var(--c-text); line-height: 1.8; }
.rich-content :deep(p) { margin-bottom: 12px; }
.rich-content :deep(h2),
.rich-content :deep(h3) { color: var(--c-primary); margin: 20px 0 12px; }
.intro-content { max-width: 820px; margin: 0 auto; }

/* Why cards */
.why-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}
.why-card {
  background: #fff;
  border-radius: 8px;
  padding: 32px 24px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,51,102,0.06);
  transition: transform 0.2s, box-shadow 0.2s;
}
.why-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,51,102,0.10); }
.why-icon { font-size: 40px; margin-bottom: 16px; }
.why-card h3 { font-size: 16px; font-weight: 600; color: var(--c-primary); margin-bottom: 10px; }
.why-card p { font-size: 14px; color: var(--c-muted); line-height: 1.6; }

/* Filters */
.filter-bar { display: flex; gap: 12px; margin-bottom: 32px; flex-wrap: wrap; }
.filter-search { flex: 1; min-width: 200px; }
.filter-select { width: 180px; }

/* University grid */
.univ-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-bottom: 32px;
}
.univ-card {
  background: #fff;
  border: 1px solid var(--c-border);
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.2s;
}
.univ-card:hover { box-shadow: 0 4px 16px rgba(0,51,102,0.10); }

.univ-logo-wrap {
  height: 120px;
  background: var(--c-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.univ-logo { max-width: 100%; max-height: 80px; object-fit: contain; }
.univ-logo-placeholder { font-size: 48px; }

.univ-body { padding: 16px; flex: 1; }
.univ-name { font-size: 15px; font-weight: 600; color: var(--c-primary); margin-bottom: 8px; line-height: 1.3; }
.univ-location { font-size: 13px; color: var(--c-muted); margin-bottom: 10px; }

.program-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.chip { padding: 2px 10px; background: var(--c-secondary); color: var(--c-primary); border-radius: 12px; font-size: 12px; font-weight: 500; }

.univ-footer {
  padding: 12px 16px;
  border-top: 1px solid var(--c-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
.univ-website-link { font-size: 13px; color: var(--c-accent); text-decoration: none; }
.univ-website-link:hover { text-decoration: underline; }

/* Pagination */
.pagination-wrap { display: flex; justify-content: center; padding-top: 16px; }

/* Dialog */
.dialog-loading { min-height: 200px; }
.dialog-header { display: flex; gap: 20px; align-items: center; margin-bottom: 16px; }
.dialog-logo { width: 80px; height: 80px; object-fit: contain; border: 1px solid var(--c-border); border-radius: 8px; padding: 6px; }
.dialog-logo-placeholder { width: 80px; height: 80px; font-size: 48px; display: flex; align-items: center; justify-content: center; background: var(--c-secondary); border-radius: 8px; }
.dialog-univ-name { font-size: 20px; font-weight: 600; color: var(--c-primary); }
.dialog-univ-name-zh { font-size: 14px; color: var(--c-muted); margin-top: 4px; }
.dialog-info { display: flex; flex-direction: column; gap: 12px; }
.info-row { display: flex; gap: 10px; font-size: 14px; align-items: flex-start; }
.info-row strong { color: var(--c-text); min-width: 100px; flex-shrink: 0; }
.info-row span { color: var(--c-muted); }
.info-row.description { flex-direction: column; }

/* Responsive */
@media (max-width: 1024px) {
  .why-grid { grid-template-columns: repeat(2, 1fr); }
  .univ-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .container { padding: 0 24px; }
  .section { padding: 40px 0; }
  .hero-title { font-size: 28px; }
  .why-grid { grid-template-columns: 1fr 1fr; }
  .univ-grid { grid-template-columns: 1fr; }
  .filter-select { width: 100%; }
}
@media (max-width: 480px) {
  .why-grid { grid-template-columns: 1fr; }
}
</style>
