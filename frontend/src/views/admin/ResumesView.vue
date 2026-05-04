<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import adminApi, { type ResumeParams } from '@/api/admin'

const { t } = useI18n()

// ─── Types ────────────────────────────────────────────────────────────────────
interface StudentInfo {
  id: number
  name: string
  email: string
  nationality?: string
  avatar?: string
}

interface ResumeItem {
  id: number
  student: StudentInfo
  education_level?: string
  status: 'pending' | 'approved' | 'rejected'
  rejection_reason?: string
  file_name?: string
  file_size?: number
  created_at: string
  file_url?: string
}

interface Pagination {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// ─── Filter state ─────────────────────────────────────────────────────────────
const search = ref('')
const statusFilter = ref('')
const dateRange = ref<[string, string] | null>(null)
const currentPage = ref(1)
const perPage = 20

// ─── Data ─────────────────────────────────────────────────────────────────────
const loading = ref(false)
const resumes = ref<ResumeItem[]>([])
const pagination = ref<Pagination | null>(null)

async function fetchResumes() {
  loading.value = true
  try {
    const params: ResumeParams = {
      search: search.value || undefined,
      status: statusFilter.value || undefined,
      date_from: dateRange.value?.[0] || undefined,
      date_to: dateRange.value?.[1] || undefined,
      per_page: perPage,
      page: currentPage.value,
    }
    const { data } = await adminApi.getResumes(params)
    const res = data.data ?? data
    resumes.value = res.data ?? res
    pagination.value = res.meta ?? null
  } finally {
    loading.value = false
  }
}

onMounted(fetchResumes)
watch([search, statusFilter, dateRange, currentPage], fetchResumes)
watch([search, statusFilter, dateRange], () => { currentPage.value = 1 })

// ─── Resume detail drawer ─────────────────────────────────────────────────────
const drawerVisible = ref(false)
const selectedResume = ref<ResumeItem | null>(null)
const drawerLoading = ref(false)

async function openDrawer(resume: ResumeItem) {
  drawerVisible.value = true
  drawerLoading.value = true
  selectedResume.value = resume
  try {
    const { data } = await adminApi.getResume(resume.id)
    selectedResume.value = data.data ?? data
  } finally {
    drawerLoading.value = false
  }
}

function openResumeFile() {
  const url = selectedResume.value?.file_url
  if (url) window.open(url, '_blank', 'noopener,noreferrer')
}

// ─── Approve ──────────────────────────────────────────────────────────────────
async function approveResume(resume: ResumeItem, fromDrawer = false) {
  await ElMessageBox.confirm(
    t('adminResumes.approveConfirm'),
    t('adminResumes.approve'),
    { type: 'warning' },
  )
  await adminApi.approveResume(resume.id)
  ElMessage.success(t('adminResumes.approveSuccess'))
  updateLocalStatus(resume.id, 'approved')
  if (fromDrawer && selectedResume.value?.id === resume.id) {
    selectedResume.value = { ...selectedResume.value, status: 'approved' }
  }
}

// ─── Reject dialog ────────────────────────────────────────────────────────────
const rejectDialog = ref(false)
const rejectTarget = ref<ResumeItem | null>(null)
const rejectReason = ref('')
const rejectLoading = ref(false)

function openRejectDialog(resume: ResumeItem) {
  rejectTarget.value = resume
  rejectReason.value = ''
  rejectDialog.value = true
}

async function submitReject() {
  if (!rejectTarget.value) return
  if (!rejectReason.value.trim()) {
    ElMessage.warning(t('validation.required'))
    return
  }
  rejectLoading.value = true
  try {
    await adminApi.rejectResume(rejectTarget.value.id, rejectReason.value.trim())
    ElMessage.success(t('adminResumes.rejectSuccess'))
    updateLocalStatus(rejectTarget.value.id, 'rejected', rejectReason.value.trim())
    if (selectedResume.value?.id === rejectTarget.value.id) {
      selectedResume.value = {
        ...selectedResume.value,
        status: 'rejected',
        rejection_reason: rejectReason.value.trim(),
      }
    }
    rejectDialog.value = false
  } finally {
    rejectLoading.value = false
  }
}

function updateLocalStatus(id: number, status: 'approved' | 'rejected', reason?: string) {
  const idx = resumes.value.findIndex((r) => r.id === id)
  if (idx !== -1) {
    resumes.value[idx] = {
      ...resumes.value[idx],
      status,
      ...(reason !== undefined ? { rejection_reason: reason } : {}),
    }
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
const statusTagType = (s: string) =>
  s === 'approved' ? 'success' : s === 'rejected' ? 'danger' : 'warning'

function formatDate(d: string): string {
  if (!d) return '—'
  return new Date(d).toLocaleDateString()
}

function formatSize(bytes?: number): string {
  if (!bytes) return '—'
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function userInitials(name: string): string {
  return name.split(' ').map((p) => p[0]).join('').toUpperCase().slice(0, 2)
}
</script>

<template>
  <div class="resumes-page">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">{{ t('adminResumes.pageTitle') }}</h1>
    </div>

    <!-- Filter bar -->
    <div class="filter-bar">
      <el-input
        v-model="search"
        :placeholder="t('adminUsers.searchPlaceholder')"
        clearable
        style="width: 260px;"
        prefix-icon="Search"
      />
      <el-select
        v-model="statusFilter"
        :placeholder="t('adminResumes.filterStatus')"
        clearable
        style="width: 160px;"
      >
        <el-option :label="t('resume.status.pending')" value="pending" />
        <el-option :label="t('resume.status.approved')" value="approved" />
        <el-option :label="t('resume.status.rejected')" value="rejected" />
      </el-select>
      <el-date-picker
        v-model="dateRange"
        type="daterange"
        value-format="YYYY-MM-DD"
        :start-placeholder="t('adminResumes.filterDateRange')"
        :end-placeholder="t('adminResumes.filterDateRange')"
        style="width: 280px;"
      />
    </div>

    <!-- Table -->
    <div class="table-card">
      <el-table v-loading="loading" :data="resumes" style="width: 100%" row-key="id">
        <!-- Student -->
        <el-table-column :label="t('adminUsers.role.student')" min-width="200">
          <template #default="{ row }">
            <div class="user-cell">
              <div class="avatar-circle">
                <img v-if="row.student?.avatar" :src="row.student.avatar" :alt="row.student?.name" />
                <span v-else>{{ userInitials(row.student?.name ?? '??') }}</span>
              </div>
              <div>
                <div class="user-name">{{ row.student?.name }}</div>
                <div class="user-email">{{ row.student?.email }}</div>
              </div>
            </div>
          </template>
        </el-table-column>

        <!-- Nationality -->
        <el-table-column :label="t('auth.nationality')" width="140">
          <template #default="{ row }">{{ row.student?.nationality || '—' }}</template>
        </el-table-column>

        <!-- Education -->
        <el-table-column :label="t('resume.educationLevel')" width="160">
          <template #default="{ row }">{{ row.education_level || '—' }}</template>
        </el-table-column>

        <!-- Submitted -->
        <el-table-column :label="t('adminResumes.submittedDate')" width="130">
          <template #default="{ row }">{{ formatDate(row.created_at) }}</template>
        </el-table-column>

        <!-- Status -->
        <el-table-column :label="t('common.status')" width="120">
          <template #default="{ row }">
            <el-tag :type="statusTagType(row.status)" size="small">
              {{ t(`resume.status.${row.status}`) }}
            </el-tag>
          </template>
        </el-table-column>

        <!-- Actions -->
        <el-table-column :label="t('common.actions')" width="220" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button size="small" link @click="openDrawer(row)">{{ t('common.view') }}</el-button>
              <el-button size="small" type="success" link @click="approveResume(row)">
                {{ t('adminResumes.approve') }}
              </el-button>
              <el-button size="small" type="danger" link @click="openRejectDialog(row)">
                {{ t('adminResumes.reject') }}
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- Empty -->
      <div v-if="!loading && resumes.length === 0" class="empty-state">
        <el-empty :description="t('adminResumes.noResumes')" />
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

    <!-- Detail Drawer -->
    <el-drawer
      v-model="drawerVisible"
      :title="t('adminResumes.studentInfo')"
      size="440px"
      direction="rtl"
    >
      <div v-if="drawerLoading">
        <el-skeleton :rows="8" animated />
      </div>
      <div v-else-if="selectedResume" class="drawer-content">
        <!-- Student info -->
        <div class="drawer-section">
          <div class="section-title">{{ t('adminResumes.studentInfo') }}</div>
          <div class="profile-header">
            <div class="avatar-circle avatar-lg">
              <img
                v-if="selectedResume.student?.avatar"
                :src="selectedResume.student.avatar"
                :alt="selectedResume.student?.name"
              />
              <span v-else>{{ userInitials(selectedResume.student?.name ?? '??') }}</span>
            </div>
            <div>
              <div class="profile-name">{{ selectedResume.student?.name }}</div>
              <div class="profile-email">{{ selectedResume.student?.email }}</div>
              <div v-if="selectedResume.student?.nationality" class="profile-meta">
                🌏 {{ selectedResume.student.nationality }}
              </div>
            </div>
          </div>
        </div>

        <!-- Status -->
        <div class="drawer-section">
          <div class="detail-row">
            <span class="detail-label">{{ t('common.status') }}</span>
            <el-tag :type="statusTagType(selectedResume.status)" size="small">
              {{ t(`resume.status.${selectedResume.status}`) }}
            </el-tag>
          </div>
          <div v-if="selectedResume.rejection_reason" class="detail-row">
            <span class="detail-label">{{ t('adminResumes.rejectReason') }}</span>
            <span class="reject-reason">{{ selectedResume.rejection_reason }}</span>
          </div>
        </div>

        <!-- File info -->
        <div class="drawer-section">
          <div class="section-title">{{ t('common.file') }}</div>
          <div class="detail-row">
            <span class="detail-label">{{ t('adminResumes.fileName') }}</span>
            <span>{{ selectedResume.file_name || '—' }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">{{ t('adminResumes.fileSize') }}</span>
            <span>{{ formatSize(selectedResume.file_size) }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">{{ t('adminResumes.uploadDate') }}</span>
            <span>{{ formatDate(selectedResume.created_at) }}</span>
          </div>
          <el-button
            v-if="selectedResume.file_url"
            type="primary"
            class="open-resume-btn"
            @click="openResumeFile"
          >
            🔗 {{ t('adminResumes.openResume') }}
          </el-button>
        </div>

        <!-- Quick actions -->
        <div class="drawer-actions">
          <el-button type="success" @click="approveResume(selectedResume!, true)">
            {{ t('adminResumes.approve') }}
          </el-button>
          <el-button type="danger" plain @click="openRejectDialog(selectedResume!)">
            {{ t('adminResumes.reject') }}
          </el-button>
        </div>
      </div>
    </el-drawer>

    <!-- Reject Dialog -->
    <el-dialog
      v-model="rejectDialog"
      :title="t('adminResumes.reject')"
      width="460px"
      :close-on-click-modal="false"
    >
      <div class="reject-dialog-body">
        <p class="reject-target" v-if="rejectTarget">
          {{ rejectTarget.student?.name }}
        </p>
        <el-form>
          <el-form-item :label="t('adminResumes.rejectReason')" required>
            <el-input
              v-model="rejectReason"
              type="textarea"
              :rows="4"
              :placeholder="t('adminResumes.rejectReasonPlaceholder')"
            />
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <el-button @click="rejectDialog = false">{{ t('common.cancel') }}</el-button>
        <el-button type="danger" :loading="rejectLoading" @click="submitReject">
          {{ t('adminResumes.reject') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.resumes-page { padding: 24px; }

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.page-title { font-size: 24px; font-weight: 600; color: #003366; }

.filter-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
  flex-wrap: wrap;
  align-items: center;
}

.table-card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,51,102,0.06);
  overflow: hidden;
}

.user-cell { display: flex; align-items: center; gap: 10px; }
.avatar-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #E6F0FF;
  color: #003366;
  font-weight: 600;
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}
.avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
.avatar-lg { width: 52px; height: 52px; font-size: 18px; }

.user-name { font-weight: 500; font-size: 14px; color: #1a1a2e; }
.user-email { font-size: 12px; color: #6c757d; }

.action-buttons { display: flex; flex-wrap: wrap; gap: 4px; }
.empty-state { padding: 40px; }
.pagination { padding: 16px; display: flex; justify-content: center; }

/* Drawer */
.drawer-content { padding: 4px 0; }
.drawer-section { margin-bottom: 24px; }
.section-title {
  font-size: 13px;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 12px;
}
.profile-header { display: flex; align-items: center; gap: 14px; }
.profile-name { font-size: 17px; font-weight: 600; color: #1a1a2e; }
.profile-email { font-size: 13px; color: #6c757d; margin-top: 2px; }
.profile-meta { font-size: 13px; color: #6c757d; margin-top: 4px; }

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
  gap: 12px;
}
.detail-label { color: #6c757d; flex-shrink: 0; }
.reject-reason { color: #dc3545; font-size: 13px; text-align: right; }
.open-resume-btn { margin-top: 14px; width: 100%; }

.drawer-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f0f0f0;
}
.drawer-actions .el-button { flex: 1; }

/* Reject dialog */
.reject-dialog-body { padding: 0 4px; }
.reject-target {
  font-weight: 600;
  color: #003366;
  margin-bottom: 16px;
}
</style>
