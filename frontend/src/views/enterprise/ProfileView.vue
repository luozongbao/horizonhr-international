<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
import type { UploadRequestOptions } from 'element-plus'
import enterpriseApi, { type EnterpriseProfileData } from '@/api/enterprise'

const { t } = useI18n()

// ─── Form state ──────────────────────────────────────────────────────────────
const loading = ref(true)
const saving = ref(false)
const logoUploading = ref(false)
const logoPreview = ref<string>('')
const descTab = ref('en')

const form = reactive<{
  company_name: string
  industry: string
  company_size: string
  website: string
  contact_email: string
  contact_phone: string
  founded_year: string
  description_en: string
  description_zh_cn: string
  description_th: string
  office_locations: string
  contact_name: string
  contact_position: string
}>({
  company_name: '',
  industry: '',
  company_size: '',
  website: '',
  contact_email: '',
  contact_phone: '',
  founded_year: '',
  description_en: '',
  description_zh_cn: '',
  description_th: '',
  office_locations: '',
  contact_name: '',
  contact_position: '',
})

const industryOptions = [
  'Technology', 'Finance', 'Healthcare', 'Education', 'Manufacturing',
  'Retail', 'Hospitality', 'Construction', 'Media', 'Consulting', 'Other',
]

const companySizeOptions = [
  { label: '1–50', value: '1-50' },
  { label: '51–200', value: '51-200' },
  { label: '201–500', value: '201-500' },
  { label: '500+', value: '500+' },
]

// ─── Load profile ────────────────────────────────────────────────────────────
onMounted(async () => {
  try {
    const { data } = await enterpriseApi.getProfile()
    const p: EnterpriseProfileData = data.data ?? data
    if (p.logo_url) logoPreview.value = p.logo_url
    Object.assign(form, {
      company_name:    p.company_name ?? '',
      industry:        p.industry ?? '',
      company_size:    p.company_size ?? '',
      website:         p.website ?? '',
      contact_email:   p.contact_email ?? '',
      contact_phone:   p.contact_phone ?? '',
      founded_year:    p.founded_year ? String(p.founded_year) : '',
      description_en:  p.description_en ?? '',
      description_zh_cn: p.description_zh_cn ?? '',
      description_th:  p.description_th ?? '',
      office_locations: p.office_locations ?? '',
      contact_name:    p.contact_name ?? '',
      contact_position: p.contact_position ?? '',
    })
  } finally {
    loading.value = false
  }
})

// ─── Logo upload ─────────────────────────────────────────────────────────────
async function handleLogoUpload(options: UploadRequestOptions) {
  const file = options.file
  if (!['image/jpeg', 'image/png'].includes(file.type)) {
    ElMessage.error('Only JPG/PNG accepted.')
    return
  }
  if (file.size > 5 * 1024 * 1024) {
    ElMessage.error('Max file size is 5 MB.')
    return
  }
  logoUploading.value = true
  try {
    const fd = new FormData()
    fd.append('logo', file)
    const { data } = await enterpriseApi.uploadLogo(fd)
    logoPreview.value = data.data?.logo_url ?? data.logo_url ?? logoPreview.value
    ElMessage.success('Logo uploaded.')
  } catch {
    ElMessage.error('Logo upload failed.')
  } finally {
    logoUploading.value = false
  }
}

// ─── Save ────────────────────────────────────────────────────────────────────
async function save() {
  if (!form.company_name.trim()) {
    ElMessage.error('Company name is required.')
    return
  }
  saving.value = true
  try {
    await enterpriseApi.updateProfile({
      ...form,
      founded_year: form.founded_year ? Number(form.founded_year) : null,
    })
    ElMessage.success(t('enterprise.profile.saveSuccess'))
  } catch {
    ElMessage.error('Failed to save profile.')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="profile-page">
    <h1 class="page-heading">{{ t('enterprise.profile.title') }}</h1>

    <div v-if="loading" class="skeleton-wrap">
      <el-skeleton animated :rows="8" />
    </div>

    <div v-else class="form-body">

      <!-- Logo Section -->
      <div class="form-section">
        <h2 class="section-title">{{ t('enterprise.profile.uploadLogo') }}</h2>
        <div class="logo-row">
          <div class="logo-preview">
            <img v-if="logoPreview" :src="logoPreview" alt="Company Logo" />
            <el-icon v-else class="logo-placeholder-icon"><Picture /></el-icon>
          </div>
          <div class="logo-actions">
            <el-upload
              :show-file-list="false"
              accept="image/jpeg,image/png"
              :http-request="handleLogoUpload"
            >
              <el-button :loading="logoUploading">
                <el-icon class="el-icon--left"><Upload /></el-icon>
                {{ t('enterprise.profile.uploadLogo') }}
              </el-button>
            </el-upload>
            <p class="logo-hint">JPG or PNG, max 5 MB</p>
          </div>
        </div>
      </div>

      <!-- Company Information -->
      <div class="form-section">
        <h2 class="section-title">{{ t('enterprise.profile.companyInfo') }}</h2>
        <div class="field-grid">
          <div class="field-wrap full">
            <label class="field-label">{{ t('enterprise.profile.companyName') }} <span class="req">*</span></label>
            <el-input v-model="form.company_name" />
          </div>
          <div class="field-wrap">
            <label class="field-label">{{ t('enterprise.profile.industry') }}</label>
            <el-select v-model="form.industry" clearable style="width:100%">
              <el-option v-for="opt in industryOptions" :key="opt" :label="opt" :value="opt" />
            </el-select>
          </div>
          <div class="field-wrap">
            <label class="field-label">{{ t('enterprise.profile.companySize') }}</label>
            <el-select v-model="form.company_size" clearable style="width:100%">
              <el-option v-for="opt in companySizeOptions" :key="opt.value" :label="opt.label" :value="opt.value" />
            </el-select>
          </div>
          <div class="field-wrap">
            <label class="field-label">{{ t('enterprise.profile.website') }}</label>
            <el-input v-model="form.website" placeholder="https://example.com" />
          </div>
          <div class="field-wrap">
            <label class="field-label">{{ t('enterprise.profile.foundedYear') }}</label>
            <el-input v-model="form.founded_year" type="number" placeholder="e.g. 2010" />
          </div>
          <div class="field-wrap">
            <label class="field-label">{{ t('enterprise.profile.contactEmail') }}</label>
            <el-input v-model="form.contact_email" type="email" />
          </div>
          <div class="field-wrap">
            <label class="field-label">{{ t('enterprise.profile.contactPhone') }}</label>
            <el-input v-model="form.contact_phone" />
          </div>
          <div class="field-wrap full">
            <label class="field-label">{{ t('enterprise.profile.officeLocations') }}</label>
            <el-input v-model="form.office_locations" type="textarea" :rows="2" />
          </div>
        </div>

        <!-- Multi-language description -->
        <div class="field-wrap full mt-16">
          <label class="field-label">{{ t('enterprise.profile.description') }}</label>
          <el-tabs v-model="descTab" class="desc-tabs">
            <el-tab-pane :label="t('enterprise.profile.descriptionTabs.en')" name="en">
              <el-input v-model="form.description_en" type="textarea" :rows="5" placeholder="English description..." />
            </el-tab-pane>
            <el-tab-pane :label="t('enterprise.profile.descriptionTabs.zh_cn')" name="zh_cn">
              <el-input v-model="form.description_zh_cn" type="textarea" :rows="5" placeholder="中文简介..." />
            </el-tab-pane>
            <el-tab-pane :label="t('enterprise.profile.descriptionTabs.th')" name="th">
              <el-input v-model="form.description_th" type="textarea" :rows="5" placeholder="คำอธิบาย..." />
            </el-tab-pane>
          </el-tabs>
        </div>
      </div>

      <!-- Contact Person -->
      <div class="form-section">
        <h2 class="section-title">{{ t('enterprise.profile.contactPerson') }}</h2>
        <div class="field-grid">
          <div class="field-wrap">
            <label class="field-label">{{ t('enterprise.profile.contactName') }}</label>
            <el-input v-model="form.contact_name" />
          </div>
          <div class="field-wrap">
            <label class="field-label">{{ t('enterprise.profile.contactPosition') }}</label>
            <el-input v-model="form.contact_position" />
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="form-actions">
        <el-button type="primary" size="large" :loading="saving" @click="save">
          Save Changes
        </el-button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-page { padding: 8px 0; }
.page-heading { font-size: 22px; font-weight: 700; color: #003366; margin: 0 0 24px; }
.skeleton-wrap { background: #fff; border-radius: 12px; padding: 24px; border: 1px solid #dee2e6; }

.form-body { display: flex; flex-direction: column; gap: 20px; }

.form-section {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 24px;
}
.section-title { font-size: 16px; font-weight: 600; color: #003366; margin: 0 0 20px; }

/* Logo */
.logo-row { display: flex; align-items: center; gap: 20px; }
.logo-preview {
  width: 96px; height: 96px;
  border-radius: 10px;
  background: #f0f4f8;
  border: 1px solid #dee2e6;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden; flex-shrink: 0;
}
.logo-preview img { width: 100%; height: 100%; object-fit: cover; }
.logo-placeholder-icon { font-size: 36px; color: #ccc; }
.logo-actions { display: flex; flex-direction: column; gap: 8px; }
.logo-hint { font-size: 12px; color: #6c757d; margin: 0; }

/* Fields */
.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field-wrap { display: flex; flex-direction: column; gap: 6px; }
.field-wrap.full { grid-column: span 2; }
.mt-16 { margin-top: 16px; }
.field-label { font-size: 13px; font-weight: 500; color: #1a1a2e; }
.req { color: #dc3545; }

.desc-tabs :deep(.el-tabs__nav-wrap) { margin-bottom: 10px; }

.form-actions { display: flex; justify-content: flex-end; padding: 4px 0; }

@media (max-width: 640px) {
  .field-grid { grid-template-columns: 1fr; }
  .field-wrap.full { grid-column: span 1; }
}
</style>

