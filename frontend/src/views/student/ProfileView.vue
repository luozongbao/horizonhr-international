<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
import type { UploadProps } from 'element-plus'
import { useAuthStore } from '@/stores/auth'
import studentApi from '@/api/student'

const { t } = useI18n()
const auth = useAuthStore()

// ─── Profile form ───────────────────────────────────────────────────────────
const profileForm = reactive({
  name: '',
  email: '',
  phone: '',
  date_of_birth: '',
  nationality: '',
  current_city: '',
  bio: '',
  prefer_lang: 'en' as 'en' | 'zh_cn' | 'th',
})

const profileSaving = ref(false)
const profileSaved = ref(false)

// ─── Password form ──────────────────────────────────────────────────────────
const pwForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})
const pwSaving = ref(false)
const pwPasswordChanged = ref(false)
const showPwForm = ref(false)

// ─── Avatar ─────────────────────────────────────────────────────────────────
const avatarUploading = ref(false)
const avatarUrl = ref<string | undefined>(undefined)

// ─── Social accounts (read-only display) ────────────────────────────────────
interface SocialAccount {
  provider: string
  label: string
  linked: boolean
}
const socialAccounts = ref<SocialAccount[]>([
  { provider: 'google',   label: 'Google',   linked: false },
  { provider: 'facebook', label: 'Facebook', linked: false },
  { provider: 'linkedin', label: 'LinkedIn', linked: false },
  { provider: 'wechat',   label: 'WeChat',   linked: false },
])

// ─── Init ────────────────────────────────────────────────────────────────────
async function loadProfile() {
  try {
    const { data } = await studentApi.getProfile()
    const u = data.data ?? data
    profileForm.name = u.name ?? ''
    profileForm.email = u.email ?? ''
    profileForm.phone = u.phone ?? ''
    profileForm.date_of_birth = u.date_of_birth ?? ''
    profileForm.nationality = u.nationality ?? ''
    profileForm.current_city = u.current_city ?? ''
    profileForm.bio = u.bio ?? ''
    profileForm.prefer_lang = u.prefer_lang ?? u.language ?? 'en'
    avatarUrl.value = u.avatar_url ?? undefined

    // Hydrate social accounts if backend provides them
    if (Array.isArray(u.social_accounts)) {
      u.social_accounts.forEach((sa: { provider: string }) => {
        const idx = socialAccounts.value.findIndex((a) => a.provider === sa.provider)
        if (idx !== -1) socialAccounts.value[idx].linked = true
      })
    }
  } catch {
    // Fallback to auth store data
    const u = auth.user
    if (u) {
      profileForm.name = u.name ?? ''
      profileForm.email = u.email ?? ''
      profileForm.prefer_lang = (u.language as 'en' | 'zh_cn' | 'th') ?? 'en'
      avatarUrl.value = u.avatar_url ?? undefined
    }
  }
}

onMounted(loadProfile)

// ─── Save profile ─────────────────────────────────────────────────────────
async function saveProfile() {
  profileSaving.value = true
  profileSaved.value = false
  try {
    await studentApi.updateProfile({
      name: profileForm.name,
      phone: profileForm.phone,
      date_of_birth: profileForm.date_of_birth || undefined,
      nationality: profileForm.nationality,
      current_city: profileForm.current_city,
      bio: profileForm.bio,
      prefer_lang: profileForm.prefer_lang,
    })
    profileSaved.value = true
    // Refresh auth store user
    await auth.fetchProfile()
    setTimeout(() => { profileSaved.value = false }, 3000)
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    ElMessage.error(err.response?.data?.message ?? 'Failed to save profile.')
  } finally {
    profileSaving.value = false
  }
}

// ─── Change password ─────────────────────────────────────────────────────
async function changePassword() {
  if (pwForm.password !== pwForm.password_confirmation) {
    ElMessage.error('Passwords do not match.')
    return
  }
  pwSaving.value = true
  pwPasswordChanged.value = false
  try {
    await studentApi.changePassword({
      current_password: pwForm.current_password,
      password: pwForm.password,
      password_confirmation: pwForm.password_confirmation,
    })
    pwPasswordChanged.value = true
    pwForm.current_password = ''
    pwForm.password = ''
    pwForm.password_confirmation = ''
    setTimeout(() => { pwPasswordChanged.value = false }, 3000)
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    ElMessage.error(err.response?.data?.message ?? 'Failed to change password.')
  } finally {
    pwSaving.value = false
  }
}

// ─── Avatar upload ───────────────────────────────────────────────────────
const beforeAvatarUpload: UploadProps['beforeUpload'] = (file) => {
  const allowed = ['image/jpeg', 'image/png']
  if (!allowed.includes(file.type)) {
    ElMessage.error('Only JPG or PNG images are allowed.')
    return false
  }
  if (file.size > 5 * 1024 * 1024) {
    ElMessage.error('Image must be smaller than 5MB.')
    return false
  }
  return true
}

async function handleAvatarChange(uploadFile: { raw?: File }) {
  if (!uploadFile.raw) return
  avatarUploading.value = true
  try {
    const fd = new FormData()
    fd.append('avatar', uploadFile.raw)
    const { data } = await studentApi.uploadAvatar(fd)
    avatarUrl.value = data.avatar_url ?? data.url ?? avatarUrl.value
    await auth.fetchProfile()
    ElMessage.success('Avatar updated.')
  } catch {
    ElMessage.error('Failed to upload avatar.')
  } finally {
    avatarUploading.value = false
  }
}

const nationalities = [
  'Chinese', 'Thai', 'Vietnamese', 'Malaysian', 'Indonesian',
  'Singaporean', 'Filipino', 'Japanese', 'Korean', 'American',
  'British', 'Australian', 'Canadian', 'French', 'German', 'Other',
]

const languageOptions = [
  { value: 'en',    label: 'English' },
  { value: 'zh_cn', label: '中文 (简体)' },
  { value: 'th',    label: 'ภาษาไทย' },
]
</script>

<template>
  <div class="profile-page">
    <h1 class="page-heading">{{ t('student.profilePage.title') }}</h1>

    <!-- Avatar section -->
    <div class="profile-card">
      <h2 class="section-title">{{ t('student.profilePage.avatar') }}</h2>
      <div class="avatar-section">
        <el-avatar :size="96" :src="avatarUrl" class="profile-avatar">
          {{ auth.user?.name?.charAt(0).toUpperCase() ?? 'S' }}
        </el-avatar>
        <div class="avatar-upload-area">
          <el-upload
            class="avatar-uploader"
            :show-file-list="false"
            :before-upload="beforeAvatarUpload"
            :on-change="handleAvatarChange"
            accept="image/jpeg,image/png"
            :auto-upload="false"
          >
            <el-button type="primary" :loading="avatarUploading" plain>
              <el-icon class="el-icon--left"><Upload /></el-icon>
              Upload Photo
            </el-button>
          </el-upload>
          <p class="hint-text">{{ t('student.profilePage.avatarHint') }}</p>
        </div>
      </div>
    </div>

    <!-- Personal info -->
    <div class="profile-card">
      <h2 class="section-title">{{ t('student.profilePage.personalInfo') }}</h2>

      <div v-if="profileSaved" class="success-banner">
        {{ t('student.profilePage.profileSaved') }}
      </div>

      <el-form label-position="top" class="profile-form" @submit.prevent="saveProfile">
        <div class="form-row">
          <el-form-item :label="t('student.profilePage.name')" class="form-col">
            <el-input v-model="profileForm.name" maxlength="100" />
          </el-form-item>
          <el-form-item :label="t('student.profilePage.email')" class="form-col">
            <el-input v-model="profileForm.email" type="email" disabled />
          </el-form-item>
        </div>

        <div class="form-row">
          <el-form-item :label="t('student.profilePage.phone')" class="form-col">
            <el-input v-model="profileForm.phone" maxlength="30" />
          </el-form-item>
          <el-form-item :label="t('student.profilePage.dateOfBirth')" class="form-col">
            <el-date-picker
              v-model="profileForm.date_of_birth"
              type="date"
              value-format="YYYY-MM-DD"
              style="width: 100%;"
            />
          </el-form-item>
        </div>

        <div class="form-row">
          <el-form-item :label="t('student.profilePage.nationality')" class="form-col">
            <el-select v-model="profileForm.nationality" clearable style="width: 100%;">
              <el-option v-for="n in nationalities" :key="n" :label="n" :value="n" />
            </el-select>
          </el-form-item>
          <el-form-item :label="t('student.profilePage.currentCity')" class="form-col">
            <el-input v-model="profileForm.current_city" maxlength="100" />
          </el-form-item>
        </div>

        <el-form-item :label="t('student.profilePage.preferLang')">
          <el-select v-model="profileForm.prefer_lang" style="width: 200px;">
            <el-option v-for="opt in languageOptions" :key="opt.value" :label="opt.label" :value="opt.value" />
          </el-select>
        </el-form-item>

        <el-form-item :label="t('student.profilePage.bio')">
          <el-input
            v-model="profileForm.bio"
            type="textarea"
            :rows="4"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>

        <div class="form-actions">
          <el-button type="primary" native-type="submit" :loading="profileSaving">
            {{ t('student.profilePage.saveProfile') }}
          </el-button>
        </div>
      </el-form>
    </div>

    <!-- Linked social accounts -->
    <div class="profile-card">
      <h2 class="section-title">{{ t('student.profilePage.socialAccounts') }}</h2>
      <ul class="social-list">
        <li v-for="sa in socialAccounts" :key="sa.provider" class="social-row">
          <span class="social-provider">{{ sa.label }}</span>
          <el-tag :type="sa.linked ? 'success' : 'info'" size="small">
            {{ sa.linked ? t('student.profilePage.linked') : t('student.profilePage.notLinked') }}
          </el-tag>
        </li>
      </ul>
    </div>

    <!-- Change password -->
    <div class="profile-card">
      <div class="pw-header" @click="showPwForm = !showPwForm">
        <h2 class="section-title no-margin">{{ t('student.profilePage.changePassword') }}</h2>
        <el-icon class="pw-toggle" :class="{ rotated: showPwForm }"><ArrowDown /></el-icon>
      </div>

      <div v-if="showPwForm" class="pw-form-wrap">
        <div v-if="pwPasswordChanged" class="success-banner">
          {{ t('student.profilePage.passwordChanged') }}
        </div>

        <el-form label-position="top" class="profile-form" @submit.prevent="changePassword">
          <el-form-item :label="t('student.profilePage.currentPassword')">
            <el-input v-model="pwForm.current_password" type="password" show-password />
          </el-form-item>
          <div class="form-row">
            <el-form-item :label="t('student.profilePage.newPassword')" class="form-col">
              <el-input v-model="pwForm.password" type="password" show-password minlength="8" />
            </el-form-item>
            <el-form-item :label="t('student.profilePage.confirmNewPassword')" class="form-col">
              <el-input v-model="pwForm.password_confirmation" type="password" show-password />
            </el-form-item>
          </div>
          <div class="form-actions">
            <el-button type="primary" native-type="submit" :loading="pwSaving">
              {{ t('student.profilePage.changePasswordBtn') }}
            </el-button>
          </div>
        </el-form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-page {
  padding: 8px 0;
}
.page-heading {
  font-size: 22px;
  font-weight: 700;
  color: #003366;
  margin: 0 0 20px;
}

/* Card */
.profile-card {
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
.section-title.no-margin { margin: 0; }

/* Avatar */
.avatar-section {
  display: flex;
  align-items: center;
  gap: 24px;
}
.profile-avatar {
  background: #e6f0ff;
  color: #003366;
  font-size: 36px;
  font-weight: 700;
  flex-shrink: 0;
}
.hint-text {
  margin: 6px 0 0;
  font-size: 12px;
  color: #6c757d;
}

/* Form */
.profile-form {
  display: flex;
  flex-direction: column;
  gap: 0;
}
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.form-col {
  flex: 1;
}
.form-actions {
  margin-top: 8px;
}

/* Success banner */
.success-banner {
  background: #f0fdf4;
  border: 1px solid #86efac;
  color: #166534;
  border-radius: 8px;
  padding: 10px 14px;
  margin-bottom: 16px;
  font-size: 14px;
}

/* Social */
.social-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.social-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  background: #f8f9fa;
  border-radius: 8px;
}
.social-provider {
  font-size: 14px;
  font-weight: 500;
  color: #1a1a2e;
}

/* Password accordion */
.pw-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  user-select: none;
}
.pw-toggle {
  transition: transform 0.25s;
  color: #6c757d;
}
.pw-toggle.rotated { transform: rotate(180deg); }
.pw-form-wrap {
  margin-top: 20px;
}

@media (max-width: 640px) {
  .form-row { grid-template-columns: 1fr; }
  .avatar-section { flex-direction: column; align-items: flex-start; }
}
</style>

