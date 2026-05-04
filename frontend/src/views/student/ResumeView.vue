<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import type { UploadProps, UploadFile } from 'element-plus'
import studentApi from '@/api/student'

const { t } = useI18n()

// ─── Types ───────────────────────────────────────────────────────────────────
interface Resume {
  id: number
  file_name: string
  file_size: number
  status: 'pending' | 'approved' | 'rejected'
  rejection_reason?: string
  created_at: string
  file_url?: string
}

// ─── State ────────────────────────────────────────────────────────────────────
const loading = ref(true)
const resumes = ref<Resume[]>([])
const uploadProgress = ref(0)
const uploading = ref(false)

// ─── Derived ──────────────────────────────────────────────────────────────────
const latestResume = computed<Resume | undefined>(() => resumes.value[0])

const statusTypeMap: Record<string, string> = {
  pending:  'warning',
  approved: 'success',
  rejected: 'danger',
}

function statusType(status: string) {
  return statusTypeMap[status] ?? 'info'
}

function formatDate(dt: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatSize(bytes: number) {
  if (!bytes) return '—'
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

// ─── Load ─────────────────────────────────────────────────────────────────────
async function loadResumes() {
  loading.value = true
  try {
    const { data } = await studentApi.getResumes()
    resumes.value = (data.data ?? data) as Resume[]
  } finally {
    loading.value = false
  }
}

onMounted(loadResumes)

// ─── Upload validation ────────────────────────────────────────────────────────
const ALLOWED_TYPES = [
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'image/jpeg',
  'image/png',
]
const MAX_SIZE = 20 * 1024 * 1024

const beforeUpload: UploadProps['beforeUpload'] = (file) => {
  if (!ALLOWED_TYPES.includes(file.type)) {
    ElMessage.error(t('student.resumePage.invalidFormat'))
    return false
  }
  if (file.size > MAX_SIZE) {
    ElMessage.error(t('student.resumePage.fileTooLarge'))
    return false
  }
  return true
}

async function handleFileChange(uploadFile: UploadFile) {
  if (!uploadFile.raw) return
  uploading.value = true
  uploadProgress.value = 0
  try {
    const fd = new FormData()
    fd.append('resume', uploadFile.raw)
    await studentApi.uploadResume(fd, (pct) => {
      uploadProgress.value = pct
    })
    ElMessage.success(t('student.resumePage.uploadSuccess'))
    await loadResumes()
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    ElMessage.error(err.response?.data?.message ?? 'Upload failed.')
  } finally {
    uploading.value = false
    uploadProgress.value = 0
  }
}

// ─── Delete ───────────────────────────────────────────────────────────────────
async function deleteResume(id: number) {
  try {
    await ElMessageBox.confirm(t('student.resumePage.deleteConfirm'), 'Confirm', {
      type: 'warning',
      confirmButtonText: 'Delete',
      cancelButtonText: 'Cancel',
    })
  } catch {
    return
  }
  try {
    await studentApi.deleteResume(id)
    resumes.value = resumes.value.filter((r) => r.id !== id)
  } catch {
    ElMessage.error('Failed to delete resume.')
  }
}
</script>

<template>
  <div class="resume-page">
    <h1 class="page-heading">{{ t('student.resumePage.title') }}</h1>

    <!-- Current resume status card -->
    <div class="resume-card">
      <h2 class="section-title">{{ t('student.resumePage.currentResume') }}</h2>

      <div v-if="loading">
        <el-skeleton :rows="3" animated />
      </div>

      <div v-else-if="latestResume" class="current-resume">
        <div class="resume-meta">
          <div class="resume-file-info">
            <el-icon class="file-icon"><Document /></el-icon>
            <div>
              <p class="file-name">{{ latestResume.file_name }}</p>
              <p class="file-details">
                {{ formatSize(latestResume.file_size) }} · {{ t('student.resumePage.uploadedAt') }}: {{ formatDate(latestResume.created_at) }}
              </p>
            </div>
          </div>
          <el-tag :type="statusType(latestResume.status)" size="large">
            {{ t(`student.resumePage.status.${latestResume.status}`) }}
          </el-tag>
        </div>

        <!-- Rejection reason -->
        <div v-if="latestResume.status === 'rejected' && latestResume.rejection_reason" class="rejection-reason">
          <strong>{{ t('student.resumePage.rejectionReason') }}:</strong>
          {{ latestResume.rejection_reason }}
        </div>

        <!-- View / Download buttons (approved only) -->
        <div v-if="latestResume.status === 'approved' && latestResume.file_url" class="resume-actions">
          <el-button type="primary" plain :href="latestResume.file_url" tag="a" target="_blank">
            <el-icon class="el-icon--left"><View /></el-icon>
            {{ t('student.resumePage.viewResume') }}
          </el-button>
          <el-button :href="latestResume.file_url" tag="a" download plain>
            <el-icon class="el-icon--left"><Download /></el-icon>
            {{ t('student.resumePage.download') }}
          </el-button>
        </div>
      </div>

      <div v-else>
        <p class="empty-text">{{ t('student.resumePage.noResumes') }}</p>
      </div>
    </div>

    <!-- Upload new resume -->
    <div class="resume-card">
      <h2 class="section-title">{{ t('student.resumePage.upload') }}</h2>

      <el-upload
        class="resume-uploader"
        drag
        :show-file-list="false"
        :before-upload="beforeUpload"
        :on-change="handleFileChange"
        :auto-upload="false"
        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
        :disabled="uploading"
      >
        <div class="upload-area">
          <el-icon class="upload-icon" :class="{ spinning: uploading }"><UploadFilled /></el-icon>
          <p class="upload-text">{{ t('student.resumePage.dragDrop') }}</p>
          <p class="upload-hint">{{ t('student.resumePage.allowedFormats') }}</p>
        </div>
      </el-upload>

      <div v-if="uploading" class="upload-progress">
        <el-progress :percentage="uploadProgress" :show-text="true" status="striped" striped-flow :duration="10" />
      </div>
    </div>

    <!-- Upload history table -->
    <div class="resume-card">
      <h2 class="section-title">{{ t('student.resumePage.uploadHistory') }}</h2>

      <div v-if="loading">
        <el-skeleton :rows="4" animated />
      </div>

      <el-table v-else-if="resumes.length" :data="resumes" class="history-table">
        <el-table-column :label="t('student.resumePage.fileName')" prop="file_name" show-overflow-tooltip />
        <el-table-column :label="t('student.resumePage.uploadedAt')" width="150">
          <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
        </el-table-column>
        <el-table-column label="Size" width="100">
          <template #default="{ row }">{{ formatSize(row.file_size) }}</template>
        </el-table-column>
        <el-table-column label="Status" width="130">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)" size="small">
              {{ t(`student.resumePage.status.${row.status}`) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="" width="80" align="center">
          <template #default="{ row }">
            <el-button type="danger" size="small" circle plain @click="deleteResume(row.id)">
              <el-icon><Delete /></el-icon>
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <p v-else class="empty-text">{{ t('student.resumePage.noResumes') }}</p>
    </div>
  </div>
</template>

<style scoped>
.resume-page {
  padding: 8px 0;
}
.page-heading {
  font-size: 22px;
  font-weight: 700;
  color: #003366;
  margin: 0 0 20px;
}

/* Card */
.resume-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 24px 28px;
  margin-bottom: 20px;
}
.section-title {
  font-size: 16px;
  font-weight: 600;
  color: #003366;
  margin: 0 0 20px;
}

/* Current resume */
.current-resume { display: flex; flex-direction: column; gap: 16px; }
.resume-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.resume-file-info {
  display: flex;
  align-items: center;
  gap: 14px;
}
.file-icon {
  font-size: 36px;
  color: #0066cc;
  flex-shrink: 0;
}
.file-name {
  margin: 0 0 2px;
  font-size: 15px;
  font-weight: 500;
  color: #1a1a2e;
}
.file-details {
  margin: 0;
  font-size: 12px;
  color: #6c757d;
}

.rejection-reason {
  background: #fff5f5;
  border: 1px solid #feb2b2;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 14px;
  color: #c53030;
}

.resume-actions { display: flex; gap: 10px; flex-wrap: wrap; }

/* Upload area */
.resume-uploader {
  width: 100%;
}
:deep(.el-upload-dragger) {
  width: 100%;
  height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  border: 2px dashed #c0c4cc;
  transition: border-color 0.2s;
}
:deep(.el-upload-dragger:hover) { border-color: #0066cc; }
.upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.upload-icon {
  font-size: 40px;
  color: #0066cc;
}
.upload-icon.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.upload-text {
  margin: 0;
  font-size: 14px;
  color: #606266;
}
.upload-hint {
  margin: 0;
  font-size: 12px;
  color: #909399;
}

.upload-progress { margin-top: 14px; }

/* Table */
.history-table { width: 100%; }

.empty-text {
  color: #6c757d;
  font-size: 14px;
  text-align: center;
  padding: 24px 0;
  margin: 0;
}
</style>

