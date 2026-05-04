<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import adminApi, { type InterviewParams } from '@/api/admin'

const { t } = useI18n()

// ─── Types ────────────────────────────────────────────────────────────────────
interface InterviewItem {
  id: number
  title: string
  status: 'scheduled' | 'ongoing' | 'completed' | 'cancelled'
  scheduled_at: string
  duration: number
  interviewer?: string
  trtc_room_id?: string
  result?: string
  result_notes?: string
  student?: { id: number; name: string; email: string; avatar?: string }
  enterprise?: { id: number; company_name?: string }
  job?: { id: number; title: string }
}

interface Pagination {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// ─── Filters ──────────────────────────────────────────────────────────────────
const search = ref('')
const statusFilter = ref('')
const dateRange = ref<[string, string] | null>(null)
const currentPage = ref(1)
const perPage = 20

// ─── Data ─────────────────────────────────────────────────────────────────────
const loading = ref(false)
const interviews = ref<InterviewItem[]>([])
const pagination = ref<Pagination | null>(null)

async function fetchInterviews() {
  loading.value = true
  try {
    const params: InterviewParams = {
      search: search.value || undefined,
      status: statusFilter.value || undefined,
      date_from: dateRange.value?.[0] || undefined,
      date_to: dateRange.value?.[1] || undefined,
      per_page: perPage,
      page: currentPage.value,
    }
    const { data } = await adminApi.getInterviews(params)
    const res = data.data ?? data
    interviews.value = res.data ?? res
    pagination.value = res.meta ?? null
  } finally {
    loading.value = false
  }
}

onMounted(fetchInterviews)
watch([search, statusFilter, dateRange, currentPage], fetchInterviews)
watch([search, statusFilter, dateRange], () => { currentPage.value = 1 })

// ─── Detail modal ─────────────────────────────────────────────────────────────
const detailDialog = ref(false)
const selectedInterview = ref<InterviewItem | null>(null)

function viewDetails(iv: InterviewItem) {
  selectedInterview.value = iv
  detailDialog.value = true
}

// ─── Cancel ───────────────────────────────────────────────────────────────────
async function cancelInterview(iv: InterviewItem) {
  await ElMessageBox.confirm(
    t('adminInterviews.cancelConfirm'),
    t('adminInterviews.cancelInterview'),
    { type: 'warning' },
  )
  await adminApi.cancelInterview(iv.id)
  ElMessage.success(t('adminInterviews.cancelSuccess'))
  const idx = interviews.value.findIndex((i) => i.id === iv.id)
  if (idx !== -1) interviews.value[idx] = { ...interviews.value[idx], status: 'cancelled' }
  if (selectedInterview.value?.id === iv.id) {
    selectedInterview.value = { ...selectedInterview.value, status: 'cancelled' }
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
const statusTagType = (s: string) =>
  s === 'completed' ? 'success' : s === 'ongoing' ? 'warning' : s === 'cancelled' ? 'danger' : 'info'

function formatDateTime(d: string): string {
  if (!d) return '—'
  return new Date(d).toLocaleString()
}

function userInitials(name: string): string {
  return name.split(' ').map((p) => p[0]).join('').toUpperCase().slice(0, 2)
}
</script>

<template>
  <div class="interviews-page">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">{{ t('adminInterviews.pageTitle') }}</h1>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
      <el-input
        v-model="search"
        :placeholder="t('adminUsers.searchPlaceholder')"
        clearable
        style="width: 260px;"
        prefix-icon="Search"
      />
      <el-select v-model="statusFilter" :placeholder="t('common.allStatuses')" clearable style="width: 160px;">
        <el-option :label="t('adminInterviews.status.scheduled')" value="scheduled" />
        <el-option :label="t('adminInterviews.status.ongoing')" value="ongoing" />
        <el-option :label="t('adminInterviews.status.completed')" value="completed" />
        <el-option :label="t('adminInterviews.status.cancelled')" value="cancelled" />
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
      <el-table v-loading="loading" :data="interviews" style="width: 100%" row-key="id">
        <!-- Student -->
        <el-table-column :label="t('adminInterviews.student')" min-width="170">
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

        <!-- Enterprise -->
        <el-table-column :label="t('adminInterviews.enterprise')" min-width="140">
          <template #default="{ row }">{{ row.enterprise?.company_name || '—' }}</template>
        </el-table-column>

        <!-- Job -->
        <el-table-column :label="t('adminInterviews.job')" min-width="150">
          <template #default="{ row }">{{ row.job?.title || row.title || '—' }}</template>
        </el-table-column>

        <!-- Scheduled -->
        <el-table-column :label="t('adminInterviews.scheduledTime')" width="170">
          <template #default="{ row }">{{ formatDateTime(row.scheduled_at) }}</template>
        </el-table-column>

        <!-- Duration -->
        <el-table-column :label="t('adminInterviews.duration')" width="100">
          <template #default="{ row }">{{ row.duration }} {{ t('seminar.minutes') }}</template>
        </el-table-column>

        <!-- Status -->
        <el-table-column :label="t('common.status')" width="120">
          <template #default="{ row }">
            <el-tag :type="statusTagType(row.status)" size="small">
              {{ t(`adminInterviews.status.${row.status}`) }}
            </el-tag>
          </template>
        </el-table-column>

        <!-- Actions -->
        <el-table-column :label="t('common.actions')" width="190" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button size="small" link @click="viewDetails(row)">
                {{ t('adminInterviews.viewDetails') }}
              </el-button>
              <el-button
                v-if="row.status === 'scheduled' || row.status === 'ongoing'"
                size="small"
                type="danger"
                link
                @click="cancelInterview(row)"
              >
                {{ t('adminInterviews.cancelInterview') }}
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- Empty -->
      <div v-if="!loading && interviews.length === 0" class="empty-state">
        <el-empty :description="t('adminInterviews.noInterviews')" />
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

    <!-- Detail Modal -->
    <el-dialog
      v-model="detailDialog"
      :title="selectedInterview?.title || t('adminInterviews.viewDetails')"
      width="520px"
    >
      <div v-if="selectedInterview" class="detail-content">
        <div class="detail-grid">
          <div class="detail-row">
            <span class="detail-label">{{ t('common.status') }}</span>
            <el-tag :type="statusTagType(selectedInterview.status)" size="small">
              {{ t(`adminInterviews.status.${selectedInterview.status}`) }}
            </el-tag>
          </div>
          <div class="detail-row">
            <span class="detail-label">{{ t('adminInterviews.student') }}</span>
            <span>{{ selectedInterview.student?.name }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">{{ t('adminInterviews.enterprise') }}</span>
            <span>{{ selectedInterview.enterprise?.company_name || '—' }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">{{ t('adminInterviews.job') }}</span>
            <span>{{ selectedInterview.job?.title || selectedInterview.title }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">{{ t('adminInterviews.scheduledTime') }}</span>
            <span>{{ formatDateTime(selectedInterview.scheduled_at) }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">{{ t('adminInterviews.duration') }}</span>
            <span>{{ selectedInterview.duration }} {{ t('seminar.minutes') }}</span>
          </div>
          <div v-if="selectedInterview.interviewer" class="detail-row">
            <span class="detail-label">{{ t('enterprise.interviews.interviewer') }}</span>
            <span>{{ selectedInterview.interviewer }}</span>
          </div>
          <div v-if="selectedInterview.trtc_room_id" class="detail-row">
            <span class="detail-label">{{ t('adminInterviews.trtcRoomId') }}</span>
            <code class="room-id">{{ selectedInterview.trtc_room_id }}</code>
          </div>
          <div v-if="selectedInterview.result" class="detail-row">
            <span class="detail-label">{{ t('adminInterviews.result') }}</span>
            <span>{{ selectedInterview.result }}</span>
          </div>
          <div v-if="selectedInterview.result_notes" class="detail-row">
            <span class="detail-label">{{ t('enterprise.interviews.resultNotes') }}</span>
            <span class="result-notes">{{ selectedInterview.result_notes }}</span>
          </div>
        </div>
      </div>
      <template #footer>
        <el-button @click="detailDialog = false">{{ t('common.close') }}</el-button>
        <el-button
          v-if="selectedInterview && (selectedInterview.status === 'scheduled' || selectedInterview.status === 'ongoing')"
          type="danger"
          @click="cancelInterview(selectedInterview)"
        >
          {{ t('adminInterviews.cancelInterview') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.interviews-page { padding: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-title { font-size: 24px; font-weight: 600; color: #003366; }
.filter-bar { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; align-items: center; }

.table-card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,51,102,0.06);
  overflow: hidden;
}

.user-cell { display: flex; align-items: center; gap: 10px; }
.avatar-circle {
  width: 36px; height: 36px; border-radius: 50%;
  background: #E6F0FF; color: #003366;
  font-weight: 600; font-size: 13px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; overflow: hidden;
}
.avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
.user-name { font-weight: 500; font-size: 14px; color: #1a1a2e; }
.user-email { font-size: 12px; color: #6c757d; }

.action-buttons { display: flex; flex-wrap: wrap; gap: 4px; }
.empty-state { padding: 40px; }
.pagination { padding: 16px; display: flex; justify-content: center; }

/* Detail modal */
.detail-content { padding: 4px 0; }
.detail-grid { display: flex; flex-direction: column; gap: 0; }
.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 10px 0;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
  gap: 16px;
}
.detail-label { color: #6c757d; flex-shrink: 0; min-width: 120px; }
.room-id { font-family: monospace; font-size: 13px; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; }
.result-notes { font-size: 13px; color: #555; text-align: right; }
</style>
