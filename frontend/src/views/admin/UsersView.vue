<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import adminApi, { type UserParams, type CreateAdminData } from '@/api/admin'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const authStore = useAuthStore()

// ─── Types ────────────────────────────────────────────────────────────────────
interface UserItem {
  id: number
  name: string
  email: string
  role: 'student' | 'enterprise' | 'admin'
  status: 'active' | 'pending' | 'suspended'
  avatar?: string
  created_at: string
  enterprise?: { id: number; company_name?: string; status?: string }
  student?: { id: number }
}

interface Pagination {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// ─── Filter & pagination state ────────────────────────────────────────────────
const search = ref('')
const roleFilter = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const perPage = 20

// ─── Users data ───────────────────────────────────────────────────────────────
const loading = ref(false)
const users = ref<UserItem[]>([])
const pagination = ref<Pagination | null>(null)

const roleTabs = ['', 'student', 'enterprise', 'admin']
const activeTab = ref('')

async function fetchUsers() {
  loading.value = true
  try {
    const params: UserParams = {
      search: search.value || undefined,
      role: (activeTab.value || roleFilter.value) || undefined,
      status: statusFilter.value || undefined,
      per_page: perPage,
      page: currentPage.value,
    }
    const { data } = await adminApi.getUsers(params)
    const res = data.data ?? data
    users.value = res.data ?? res
    pagination.value = res.meta ?? null
  } finally {
    loading.value = false
  }
}

onMounted(fetchUsers)
watch([search, roleFilter, statusFilter, activeTab, currentPage], fetchUsers)

// Reset page when filters change
watch([search, roleFilter, statusFilter, activeTab], () => { currentPage.value = 1 })

// ─── Profile drawer ───────────────────────────────────────────────────────────
const profileDrawer = ref(false)
const selectedUser = ref<UserItem | null>(null)
const profileLoading = ref(false)

async function viewProfile(user: UserItem) {
  profileDrawer.value = true
  profileLoading.value = true
  selectedUser.value = user
  try {
    const { data } = await adminApi.getUser(user.id)
    selectedUser.value = data.data ?? data
  } finally {
    profileLoading.value = false
  }
}

// ─── Actions ──────────────────────────────────────────────────────────────────
async function approveEnterprise(user: UserItem) {
  await ElMessageBox.confirm(
    t('adminUsers.approveConfirm'),
    t('adminUsers.approve'),
    { type: 'warning' },
  )
  await adminApi.approveEnterprise(user.id)
  ElMessage.success(t('adminUsers.approveSuccess'))
  fetchUsers()
}

async function suspendUser(user: UserItem) {
  await ElMessageBox.confirm(t('adminUsers.suspendConfirm'), t('adminUsers.suspend'), { type: 'warning' })
  await adminApi.updateUserStatus(user.id, 'suspended')
  ElMessage.success(t('adminUsers.suspendSuccess'))
  fetchUsers()
}

async function activateUser(user: UserItem) {
  await adminApi.updateUserStatus(user.id, 'active')
  ElMessage.success(t('adminUsers.activateSuccess'))
  fetchUsers()
}

async function deleteUser(user: UserItem) {
  await ElMessageBox.confirm(
    t('adminUsers.deleteConfirm'),
    t('adminUsers.deleteUser'),
    { type: 'error', confirmButtonClass: 'el-button--danger' },
  )
  await adminApi.deleteUser(user.id)
  ElMessage.success(t('adminUsers.deleteSuccess'))
  fetchUsers()
}

// ─── Create Admin dialog ──────────────────────────────────────────────────────
const createAdminDialog = ref(false)
const createAdminForm = ref<CreateAdminData>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const createAdminLoading = ref(false)
const createAdminRef = ref<any>(null)

const createAdminRules = computed(() => ({
  name: [{ required: true, message: t('validation.required'), trigger: 'blur' }],
  email: [
    { required: true, message: t('validation.required'), trigger: 'blur' },
    { type: 'email', message: t('validation.email'), trigger: 'blur' },
  ],
  password: [
    { required: true, message: t('validation.required'), trigger: 'blur' },
    { min: 8, message: t('validation.minLength', { n: 8 }), trigger: 'blur' },
  ],
  password_confirmation: [
    { required: true, message: t('validation.required'), trigger: 'blur' },
    {
      validator: (_: any, value: string, callback: any) => {
        if (value !== createAdminForm.value.password) {
          callback(new Error(t('validation.passwordMismatch')))
        } else {
          callback()
        }
      },
      trigger: 'blur',
    },
  ],
}))

function openCreateAdmin() {
  createAdminForm.value = { name: '', email: '', password: '', password_confirmation: '' }
  createAdminDialog.value = true
}

async function submitCreateAdmin() {
  if (!createAdminRef.value) return
  await createAdminRef.value.validate()
  createAdminLoading.value = true
  try {
    await adminApi.createAdmin(createAdminForm.value)
    ElMessage.success(t('adminUsers.createSuccess'))
    createAdminDialog.value = false
    fetchUsers()
  } finally {
    createAdminLoading.value = false
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
const roleTagType = (role: string) => role === 'admin' ? 'danger' : role === 'enterprise' ? 'warning' : 'info'
const statusTagType = (status: string) => status === 'active' ? 'success' : status === 'pending' ? 'warning' : 'danger'

function formatDate(dateStr: string): string {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString()
}

function userInitials(name: string): string {
  return name.split(' ').map((p) => p[0]).join('').toUpperCase().slice(0, 2)
}
</script>

<template>
  <div class="users-page">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">{{ t('adminUsers.pageTitle') }}</h1>
      <el-button type="primary" @click="openCreateAdmin">
        + {{ t('adminUsers.createAdmin') }}
      </el-button>
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
      <el-select v-model="statusFilter" :placeholder="t('adminUsers.allStatuses')" clearable style="width: 160px;">
        <el-option :label="t('adminUsers.status.active')" value="active" />
        <el-option :label="t('adminUsers.status.pending')" value="pending" />
        <el-option :label="t('adminUsers.status.suspended')" value="suspended" />
      </el-select>
    </div>

    <!-- Role tabs -->
    <el-tabs v-model="activeTab" class="role-tabs" @tab-change="currentPage = 1">
      <el-tab-pane
        v-for="role in roleTabs"
        :key="role"
        :label="role === '' ? t('adminUsers.allRoles') : t(`adminUsers.role.${role}`)"
        :name="role"
      />
    </el-tabs>

    <!-- Table -->
    <div class="table-card">
      <el-table
        v-loading="loading"
        :data="users"
        style="width: 100%"
        row-key="id"
      >
        <!-- Avatar + Name -->
        <el-table-column :label="t('auth.name')" min-width="200">
          <template #default="{ row }">
            <div class="user-cell">
              <div class="avatar-circle">
                <img v-if="row.avatar" :src="row.avatar" :alt="row.name" />
                <span v-else>{{ userInitials(row.name) }}</span>
              </div>
              <div>
                <div class="user-name">{{ row.name }}</div>
                <div class="user-email">{{ row.email }}</div>
              </div>
            </div>
          </template>
        </el-table-column>

        <!-- Role -->
        <el-table-column :label="t('auth.role')" width="120">
          <template #default="{ row }">
            <el-tag :type="roleTagType(row.role)" size="small">
              {{ t(`adminUsers.role.${row.role}`) }}
            </el-tag>
          </template>
        </el-table-column>

        <!-- Status -->
        <el-table-column :label="t('common.status')" width="120">
          <template #default="{ row }">
            <el-tag :type="statusTagType(row.status)" size="small">
              {{ t(`adminUsers.status.${row.status}`) }}
            </el-tag>
          </template>
        </el-table-column>

        <!-- Registered -->
        <el-table-column :label="t('adminUsers.registeredAt')" width="130">
          <template #default="{ row }">
            {{ formatDate(row.created_at) }}
          </template>
        </el-table-column>

        <!-- Actions -->
        <el-table-column :label="t('common.actions')" width="260" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button size="small" link @click="viewProfile(row)">
                {{ t('adminUsers.viewProfile') }}
              </el-button>
              <el-button
                v-if="row.role === 'enterprise' && (row.status === 'pending' || row.enterprise?.status === 'pending')"
                size="small"
                type="success"
                link
                @click="approveEnterprise(row)"
              >
                {{ t('adminUsers.approve') }}
              </el-button>
              <el-button
                v-if="row.status === 'active'"
                size="small"
                type="warning"
                link
                @click="suspendUser(row)"
              >
                {{ t('adminUsers.suspend') }}
              </el-button>
              <el-button
                v-if="row.status === 'suspended'"
                size="small"
                type="success"
                link
                @click="activateUser(row)"
              >
                {{ t('adminUsers.activate') }}
              </el-button>
              <el-button
                v-if="authStore.user?.id !== row.id"
                size="small"
                type="danger"
                link
                @click="deleteUser(row)"
              >
                {{ t('adminUsers.deleteUser') }}
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- Empty -->
      <div v-if="!loading && users.length === 0" class="empty-state">
        <el-empty :description="t('adminUsers.noUsers')" />
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

    <!-- Profile Drawer -->
    <el-drawer
      v-model="profileDrawer"
      :title="t('adminUsers.viewProfile')"
      size="420px"
      direction="rtl"
    >
      <div v-if="profileLoading" class="profile-loading">
        <el-skeleton :rows="6" animated />
      </div>
      <div v-else-if="selectedUser" class="profile-content">
        <div class="profile-header">
          <div class="profile-avatar">{{ userInitials(selectedUser.name) }}</div>
          <div>
            <div class="profile-name">{{ selectedUser.name }}</div>
            <div class="profile-email">{{ selectedUser.email }}</div>
          </div>
        </div>
        <div class="profile-details">
          <div class="detail-row">
            <span class="detail-label">{{ t('auth.role') }}</span>
            <el-tag :type="roleTagType(selectedUser.role)" size="small">
              {{ t(`adminUsers.role.${selectedUser.role}`) }}
            </el-tag>
          </div>
          <div class="detail-row">
            <span class="detail-label">{{ t('common.status') }}</span>
            <el-tag :type="statusTagType(selectedUser.status)" size="small">
              {{ t(`adminUsers.status.${selectedUser.status}`) }}
            </el-tag>
          </div>
          <div class="detail-row">
            <span class="detail-label">{{ t('adminUsers.registeredAt') }}</span>
            <span>{{ formatDate(selectedUser.created_at) }}</span>
          </div>
          <div v-if="selectedUser.enterprise?.company_name" class="detail-row">
            <span class="detail-label">{{ t('enterprise.companyName') }}</span>
            <span>{{ selectedUser.enterprise.company_name }}</span>
          </div>
        </div>
      </div>
    </el-drawer>

    <!-- Create Admin Dialog -->
    <el-dialog
      v-model="createAdminDialog"
      :title="t('adminUsers.createAdmin')"
      width="460px"
      :close-on-click-modal="false"
    >
      <el-form
        ref="createAdminRef"
        :model="createAdminForm"
        :rules="createAdminRules"
        label-position="top"
      >
        <el-form-item :label="t('adminUsers.adminName')" prop="name">
          <el-input v-model="createAdminForm.name" />
        </el-form-item>
        <el-form-item :label="t('adminUsers.adminEmail')" prop="email">
          <el-input v-model="createAdminForm.email" type="email" />
        </el-form-item>
        <el-form-item :label="t('adminUsers.adminPassword')" prop="password">
          <el-input v-model="createAdminForm.password" type="password" show-password />
        </el-form-item>
        <el-form-item :label="t('adminUsers.adminPasswordConfirm')" prop="password_confirmation">
          <el-input v-model="createAdminForm.password_confirmation" type="password" show-password />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="createAdminDialog = false">{{ t('common.cancel') }}</el-button>
        <el-button type="primary" :loading="createAdminLoading" @click="submitCreateAdmin">
          {{ t('adminUsers.createAdmin') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.users-page { padding: 24px; }

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
}

.role-tabs { margin-bottom: 8px; }

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
.user-name { font-weight: 500; font-size: 14px; color: #1a1a2e; }
.user-email { font-size: 12px; color: #6c757d; }

.action-buttons { display: flex; flex-wrap: wrap; gap: 4px; }

.empty-state { padding: 40px; }
.pagination { padding: 16px; display: flex; justify-content: center; }

/* Profile drawer */
.profile-loading, .profile-content { padding: 8px; }
.profile-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
}
.profile-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #E6F0FF;
  color: #003366;
  font-size: 18px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}
.profile-name { font-size: 18px; font-weight: 600; color: #1a1a2e; }
.profile-email { font-size: 13px; color: #6c757d; margin-top: 2px; }
.profile-details { display: flex; flex-direction: column; gap: 16px; }
.detail-row { display: flex; justify-content: space-between; align-items: center; }
.detail-label { font-size: 13px; color: #6c757d; }
</style>

