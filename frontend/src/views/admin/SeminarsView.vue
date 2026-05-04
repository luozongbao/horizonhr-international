<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import adminApi, { type AdminSeminarParams, type SeminarFormData } from '@/api/admin'

const { t } = useI18n()

// ─── Types ────────────────────────────────────────────────────────────────────
interface SeminarItem {
  id: number
  title: string
  title_en?: string
  title_zh_cn?: string
  title_th?: string
  description_en?: string
  description_zh_cn?: string
  description_th?: string
  speaker_name?: string
  speaker_title?: string
  speaker_bio?: string
  speaker_photo_url?: string
  thumbnail_url?: string
  scheduled_at: string
  duration: number
  language?: string
  max_registrations?: number
  status: 'scheduled' | 'live' | 'ended'
  registrations_count?: number
  recording_url?: string
}

interface Pagination {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// ─── Filters & data ───────────────────────────────────────────────────────────
const activeTab = ref('')
const search = ref('')
const currentPage = ref(1)
const perPage = 20

const loading = ref(false)
const seminars = ref<SeminarItem[]>([])
const pagination = ref<Pagination | null>(null)

async function fetchSeminars() {
  loading.value = true
  try {
    const params: AdminSeminarParams = {
      search: search.value || undefined,
      status: activeTab.value || undefined,
      per_page: perPage,
      page: currentPage.value,
    }
    const { data } = await adminApi.getSeminars(params)
    const res = data.data ?? data
    seminars.value = res.data ?? res
    pagination.value = res.meta ?? null
  } finally {
    loading.value = false
  }
}

onMounted(fetchSeminars)
watch([activeTab, search, currentPage], fetchSeminars)
watch([activeTab, search], () => { currentPage.value = 1 })

// ─── Form dialog ──────────────────────────────────────────────────────────────
const formDialog = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const formLoading = ref(false)
const langTab = ref('en')

const emptyForm = (): SeminarFormData => ({
  title_en: '',
  title_zh_cn: '',
  title_th: '',
  description_en: '',
  description_zh_cn: '',
  description_th: '',
  speaker_name: '',
  speaker_title: '',
  speaker_bio: '',
  scheduled_at: '',
  duration: 60,
  language: 'en',
  max_registrations: 0,
})

const form = ref<SeminarFormData>(emptyForm())
const speakerPhotoFile = ref<File | null>(null)
const thumbnailFile = ref<File | null>(null)
const speakerPhotoPreview = ref<string | null>(null)
const thumbnailPreview = ref<string | null>(null)
const formRef = ref<any>(null)

const formRules = computed(() => ({
  title_en: [{ required: true, message: t('validation.required'), trigger: 'blur' }],
  scheduled_at: [{ required: true, message: t('validation.required'), trigger: 'change' }],
  duration: [{ required: true, message: t('validation.required'), trigger: 'blur' }],
}))

function openCreate() {
  isEditing.value = false
  editingId.value = null
  form.value = emptyForm()
  speakerPhotoFile.value = null
  thumbnailFile.value = null
  speakerPhotoPreview.value = null
  thumbnailPreview.value = null
  langTab.value = 'en'
  formDialog.value = true
}

function openEdit(seminar: SeminarItem) {
  isEditing.value = true
  editingId.value = seminar.id
  form.value = {
    title_en: seminar.title_en ?? '',
    title_zh_cn: seminar.title_zh_cn ?? '',
    title_th: seminar.title_th ?? '',
    description_en: seminar.description_en ?? '',
    description_zh_cn: seminar.description_zh_cn ?? '',
    description_th: seminar.description_th ?? '',
    speaker_name: seminar.speaker_name ?? '',
    speaker_title: seminar.speaker_title ?? '',
    speaker_bio: seminar.speaker_bio ?? '',
    scheduled_at: seminar.scheduled_at,
    duration: seminar.duration,
    language: seminar.language ?? 'en',
    max_registrations: seminar.max_registrations ?? 0,
  }
  speakerPhotoFile.value = null
  thumbnailFile.value = null
  speakerPhotoPreview.value = seminar.speaker_photo_url ?? null
  thumbnailPreview.value = seminar.thumbnail_url ?? null
  langTab.value = 'en'
  formDialog.value = true
}

function handleSpeakerPhoto(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  speakerPhotoFile.value = file
  speakerPhotoPreview.value = URL.createObjectURL(file)
}

function handleThumbnail(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  thumbnailFile.value = file
  thumbnailPreview.value = URL.createObjectURL(file)
}

async function submitForm() {
  if (!formRef.value) return
  await formRef.value.validate()
  formLoading.value = true
  try {
    let payload: SeminarFormData | FormData
    if (speakerPhotoFile.value || thumbnailFile.value) {
      const fd = new FormData()
      Object.entries(form.value).forEach(([k, v]) => {
        if (v !== null && v !== undefined) fd.append(k, String(v))
      })
      if (speakerPhotoFile.value) fd.append('speaker_photo', speakerPhotoFile.value)
      if (thumbnailFile.value) fd.append('thumbnail', thumbnailFile.value)
      payload = fd
    } else {
      payload = { ...form.value }
    }

    if (isEditing.value && editingId.value) {
      await adminApi.updateSeminar(editingId.value, payload)
      ElMessage.success(t('adminSeminars.updateSuccess'))
    } else {
      await adminApi.createSeminar(payload)
      ElMessage.success(t('adminSeminars.createSuccess'))
    }
    formDialog.value = false
    fetchSeminars()
  } finally {
    formLoading.value = false
  }
}

// ─── Delete ───────────────────────────────────────────────────────────────────
async function deleteSeminar(seminar: SeminarItem) {
  await ElMessageBox.confirm(
    t('adminSeminars.deleteConfirm'),
    t('adminSeminars.pageTitle'),
    { type: 'error', confirmButtonClass: 'el-button--danger' },
  )
  await adminApi.deleteSeminar(seminar.id)
  ElMessage.success(t('adminSeminars.deleteSuccess'))
  fetchSeminars()
}

// ─── Live Controls drawer ─────────────────────────────────────────────────────
const liveDrawer = ref(false)
const liveTarget = ref<SeminarItem | null>(null)
const liveUrls = ref<{ push_url?: string; pull_url?: string }>({})
const liveLoading = ref(false)
const showPushUrl = ref(false)

async function openLiveControls(seminar: SeminarItem) {
  liveTarget.value = seminar
  liveUrls.value = {}
  showPushUrl.value = false
  liveDrawer.value = true
  await refreshUrls()
}

async function refreshUrls() {
  if (!liveTarget.value) return
  liveLoading.value = true
  try {
    const { data } = await adminApi.getSeminarLiveUrls(liveTarget.value.id)
    liveUrls.value = data.data ?? data
  } finally {
    liveLoading.value = false
  }
}

async function copyUrl(url: string) {
  try {
    await navigator.clipboard.writeText(url)
    ElMessage.success(t('adminSeminars.urlCopied'))
  } catch {
    ElMessage.error('Copy failed')
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
const statusTagType = (s: string) =>
  s === 'live' ? 'danger' : s === 'ended' ? 'info' : 'success'

function formatDateTime(d: string): string {
  if (!d) return '—'
  return new Date(d).toLocaleString()
}
</script>

<template>
  <div class="seminars-page">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">{{ t('adminSeminars.pageTitle') }}</h1>
      <el-button type="primary" @click="openCreate">
        + {{ t('adminSeminars.createSeminar') }}
      </el-button>
    </div>

    <!-- Search + status tabs -->
    <div class="filter-bar">
      <el-input
        v-model="search"
        :placeholder="t('adminUsers.searchPlaceholder')"
        clearable
        style="width: 260px;"
        prefix-icon="Search"
      />
    </div>

    <el-tabs v-model="activeTab" class="status-tabs">
      <el-tab-pane :label="t('seminar.allStatuses')" name="" />
      <el-tab-pane :label="t('adminSeminars.status.scheduled')" name="scheduled" />
      <el-tab-pane :label="t('adminSeminars.status.live')" name="live" />
      <el-tab-pane :label="t('adminSeminars.status.ended')" name="ended" />
    </el-tabs>

    <!-- Table -->
    <div class="table-card">
      <el-table v-loading="loading" :data="seminars" style="width: 100%" row-key="id">
        <!-- Thumbnail -->
        <el-table-column width="90">
          <template #default="{ row }">
            <img
              v-if="row.thumbnail_url"
              :src="row.thumbnail_url"
              class="seminar-thumb"
              :alt="row.title"
            />
            <div v-else class="seminar-thumb-placeholder">📡</div>
          </template>
        </el-table-column>

        <!-- Title + Speaker -->
        <el-table-column :label="t('adminSeminars.titleLabel')" min-width="220">
          <template #default="{ row }">
            <div class="seminar-title">{{ row.title_en || row.title }}</div>
            <div v-if="row.speaker_name" class="seminar-speaker">{{ row.speaker_name }}</div>
          </template>
        </el-table-column>

        <!-- Scheduled -->
        <el-table-column :label="t('adminSeminars.scheduledAt')" width="170">
          <template #default="{ row }">{{ formatDateTime(row.scheduled_at) }}</template>
        </el-table-column>

        <!-- Status -->
        <el-table-column :label="t('common.status')" width="110">
          <template #default="{ row }">
            <el-tag :type="statusTagType(row.status)" size="small">
              {{ t(`adminSeminars.status.${row.status}`) }}
            </el-tag>
          </template>
        </el-table-column>

        <!-- Registered -->
        <el-table-column :label="t('adminSeminars.registeredCount')" width="110" align="center">
          <template #default="{ row }">{{ row.registrations_count ?? 0 }}</template>
        </el-table-column>

        <!-- Actions -->
        <el-table-column :label="t('common.actions')" width="220" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button size="small" link @click="openEdit(row)">{{ t('common.edit') }}</el-button>
              <el-button
                v-if="row.status === 'scheduled' || row.status === 'live'"
                size="small"
                type="primary"
                link
                @click="openLiveControls(row)"
              >
                {{ t('adminSeminars.liveControls') }}
              </el-button>
              <el-button
                v-if="row.status === 'ended' && row.recording_url"
                size="small"
                type="success"
                link
                @click="window.open(row.recording_url, '_blank')"
              >
                {{ t('adminSeminars.viewRecording') }}
              </el-button>
              <el-button
                v-if="row.status === 'scheduled'"
                size="small"
                type="danger"
                link
                @click="deleteSeminar(row)"
              >
                {{ t('common.delete') }}
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- Empty -->
      <div v-if="!loading && seminars.length === 0" class="empty-state">
        <el-empty :description="t('adminSeminars.noSeminars')" />
      </div>

      <!-- Pagination -->
      <el-pagination
        v-if="pagination && pagination.last_page > 1"
        v-model:current-page="currentPage"
        :total="pagination.total"
        :page-size="perPage"
        layout="prev, pager, next, total"
        class="pagination"
        background
      />
    </div>

    <!-- Create / Edit Dialog -->
    <el-dialog
      v-model="formDialog"
      :title="isEditing ? t('adminSeminars.editSeminar') : t('adminSeminars.createSeminar')"
      width="640px"
      :close-on-click-modal="false"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="formRules"
        label-position="top"
      >
        <!-- Multi-language title/description tabs -->
        <div class="section-label">{{ t('adminSeminars.titleLabel') }} / {{ t('adminSeminars.descriptionLabel') }}</div>
        <el-tabs v-model="langTab" size="small" class="lang-tabs">
          <el-tab-pane label="English" name="en">
            <el-form-item :label="t('adminSeminars.titleLabel') + ' (EN)'" prop="title_en">
              <el-input v-model="form.title_en" />
            </el-form-item>
            <el-form-item :label="t('adminSeminars.descriptionLabel') + ' (EN)'">
              <el-input v-model="form.description_en" type="textarea" :rows="3" />
            </el-form-item>
          </el-tab-pane>
          <el-tab-pane label="中文" name="zh">
            <el-form-item :label="t('adminSeminars.titleLabel') + ' (中文)'">
              <el-input v-model="form.title_zh_cn" />
            </el-form-item>
            <el-form-item :label="t('adminSeminars.descriptionLabel') + ' (中文)'">
              <el-input v-model="form.description_zh_cn" type="textarea" :rows="3" />
            </el-form-item>
          </el-tab-pane>
          <el-tab-pane label="ภาษาไทย" name="th">
            <el-form-item :label="t('adminSeminars.titleLabel') + ' (ภาษาไทย)'">
              <el-input v-model="form.title_th" />
            </el-form-item>
            <el-form-item :label="t('adminSeminars.descriptionLabel') + ' (ภาษาไทย)'">
              <el-input v-model="form.description_th" type="textarea" :rows="3" />
            </el-form-item>
          </el-tab-pane>
        </el-tabs>

        <!-- Speaker -->
        <el-divider />
        <div class="form-row">
          <el-form-item :label="t('adminSeminars.speakerName')" style="flex:1">
            <el-input v-model="form.speaker_name" />
          </el-form-item>
          <el-form-item :label="t('adminSeminars.speakerTitle')" style="flex:1">
            <el-input v-model="form.speaker_title" />
          </el-form-item>
        </div>
        <el-form-item :label="t('adminSeminars.speakerBio')">
          <el-input v-model="form.speaker_bio" type="textarea" :rows="2" />
        </el-form-item>

        <!-- Speaker photo upload -->
        <el-form-item :label="t('adminSeminars.speakerPhoto')">
          <div class="upload-row">
            <div v-if="speakerPhotoPreview" class="photo-preview">
              <img :src="speakerPhotoPreview" alt="speaker" />
            </div>
            <label class="upload-btn">
              {{ t('common.uploadPhoto') }}
              <input type="file" accept="image/jpeg,image/png" style="display:none" @change="handleSpeakerPhoto" />
            </label>
          </div>
        </el-form-item>

        <!-- Thumbnail upload -->
        <el-form-item :label="t('adminSeminars.thumbnail')">
          <div class="upload-row">
            <div v-if="thumbnailPreview" class="thumb-preview">
              <img :src="thumbnailPreview" alt="thumbnail" />
            </div>
            <label class="upload-btn">
              {{ t('common.uploadPhoto') }}
              <input type="file" accept="image/jpeg,image/png" style="display:none" @change="handleThumbnail" />
            </label>
          </div>
        </el-form-item>

        <el-divider />

        <!-- Schedule + duration -->
        <div class="form-row">
          <el-form-item :label="t('adminSeminars.scheduledAt')" prop="scheduled_at" style="flex:2">
            <el-date-picker
              v-model="form.scheduled_at"
              type="datetime"
              value-format="YYYY-MM-DD HH:mm:ss"
              style="width: 100%"
            />
          </el-form-item>
          <el-form-item :label="t('adminSeminars.durationMin')" prop="duration" style="flex:1">
            <el-input-number v-model="form.duration" :min="15" :max="480" style="width: 100%" />
          </el-form-item>
        </div>

        <div class="form-row">
          <el-form-item :label="t('adminSeminars.language')" style="flex:1">
            <el-select v-model="form.language" style="width:100%">
              <el-option label="English" value="en" />
              <el-option label="中文" value="zh_cn" />
              <el-option label="ภาษาไทย" value="th" />
            </el-select>
          </el-form-item>
          <el-form-item :label="t('adminSeminars.maxRegistrations')" style="flex:1">
            <el-input-number v-model="form.max_registrations" :min="0" style="width: 100%" />
          </el-form-item>
        </div>
      </el-form>

      <template #footer>
        <el-button @click="formDialog = false">{{ t('common.cancel') }}</el-button>
        <el-button type="primary" :loading="formLoading" @click="submitForm">
          {{ isEditing ? t('common.save') : t('adminSeminars.createSeminar') }}
        </el-button>
      </template>
    </el-dialog>

    <!-- Live Controls Drawer -->
    <el-drawer
      v-model="liveDrawer"
      :title="t('adminSeminars.liveControls')"
      size="440px"
      direction="rtl"
    >
      <div v-if="liveLoading" class="live-loading">
        <el-skeleton :rows="4" animated />
      </div>
      <div v-else class="live-content">
        <div v-if="liveTarget" class="live-seminar-name">
          {{ liveTarget.title_en || liveTarget.title }}
          <el-tag :type="statusTagType(liveTarget.status)" size="small" style="margin-left: 8px">
            {{ t(`adminSeminars.status.${liveTarget.status}`) }}
          </el-tag>
        </div>

        <!-- Push URL (sensitive — show as password field) -->
        <div class="url-section">
          <div class="url-label">🔴 {{ t('adminSeminars.pushUrl') }}</div>
          <div class="url-field">
            <el-input
              :model-value="liveUrls.push_url || ''"
              :type="showPushUrl ? 'text' : 'password'"
              readonly
            >
              <template #append>
                <el-button @click="showPushUrl = !showPushUrl">
                  {{ showPushUrl ? '🙈' : '👁️' }}
                </el-button>
              </template>
            </el-input>
            <el-button
              v-if="liveUrls.push_url"
              size="small"
              class="copy-btn"
              @click="copyUrl(liveUrls.push_url!)"
            >
              {{ t('adminSeminars.copyUrl') }}
            </el-button>
          </div>
          <p class="obs-instructions">{{ t('adminSeminars.obsInstructions') }}</p>
        </div>

        <!-- Pull URL -->
        <div class="url-section">
          <div class="url-label">🟢 {{ t('adminSeminars.pullUrl') }}</div>
          <div class="url-field">
            <el-input :model-value="liveUrls.pull_url || ''" readonly />
            <el-button
              v-if="liveUrls.pull_url"
              size="small"
              class="copy-btn"
              @click="copyUrl(liveUrls.pull_url!)"
            >
              {{ t('adminSeminars.copyUrl') }}
            </el-button>
          </div>
        </div>

        <!-- Recording -->
        <div v-if="liveTarget?.recording_url" class="url-section">
          <el-button type="success" @click="window.open(liveTarget.recording_url, '_blank')">
            {{ t('adminSeminars.viewRecording') }}
          </el-button>
        </div>

        <el-button
          class="refresh-btn"
          :loading="liveLoading"
          @click="refreshUrls"
        >
          🔄 {{ t('adminSeminars.refreshUrls') }}
        </el-button>
      </div>
    </el-drawer>
  </div>
</template>

<style scoped>
.seminars-page { padding: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-title { font-size: 24px; font-weight: 600; color: #003366; }
.filter-bar { display: flex; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; align-items: center; }
.status-tabs { margin-bottom: 8px; }

.table-card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,51,102,0.06);
  overflow: hidden;
}

.seminar-thumb {
  width: 70px; height: 44px; object-fit: cover; border-radius: 4px; display: block;
}
.seminar-thumb-placeholder {
  width: 70px; height: 44px; background: #E6F0FF; border-radius: 4px;
  display: flex; align-items: center; justify-content: center; font-size: 20px;
}
.seminar-title { font-weight: 500; font-size: 14px; color: #1a1a2e; }
.seminar-speaker { font-size: 12px; color: #6c757d; margin-top: 2px; }

.action-buttons { display: flex; flex-wrap: wrap; gap: 4px; }
.empty-state { padding: 40px; }
.pagination { padding: 16px; display: flex; justify-content: center; }

/* Form */
.section-label { font-size: 13px; font-weight: 600; color: #6c757d; margin-bottom: 8px; }
.lang-tabs { margin-bottom: 12px; }
.form-row { display: flex; gap: 16px; }
.form-row .el-form-item { min-width: 0; }

.upload-row { display: flex; align-items: center; gap: 12px; }
.photo-preview { width: 60px; height: 60px; border-radius: 50%; overflow: hidden; border: 2px solid #dee2e6; }
.photo-preview img { width: 100%; height: 100%; object-fit: cover; }
.thumb-preview { width: 112px; height: 63px; border-radius: 4px; overflow: hidden; border: 2px solid #dee2e6; }
.thumb-preview img { width: 100%; height: 100%; object-fit: cover; }
.upload-btn {
  display: inline-flex; align-items: center; padding: 8px 16px;
  border: 1px solid #dee2e6; border-radius: 6px; cursor: pointer;
  font-size: 13px; background: #fff; transition: all 0.2s;
}
.upload-btn:hover { border-color: #003366; color: #003366; }

/* Live controls */
.live-loading, .live-content { padding: 4px 0; }
.live-seminar-name { font-size: 16px; font-weight: 600; color: #1a1a2e; margin-bottom: 20px; display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
.url-section { margin-bottom: 20px; }
.url-label { font-size: 13px; font-weight: 600; color: #6c757d; margin-bottom: 8px; }
.url-field { display: flex; flex-direction: column; gap: 6px; }
.copy-btn { align-self: flex-start; }
.obs-instructions { font-size: 12px; color: #6c757d; margin-top: 6px; background: #f8f9fa; padding: 8px; border-radius: 4px; }
.refresh-btn { margin-top: 12px; width: 100%; }
</style>
