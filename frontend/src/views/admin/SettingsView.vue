<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
import adminApi from '@/api/admin'

const { t } = useI18n()

// ─── Settings state ───────────────────────────────────────────────────────────
const loading = ref(true)
const activeTab = ref('general')

// All settings loaded from API
const s = reactive<Record<string, any>>({})

// Image previews
const logoPreview = ref<string | null>(null)
const faviconPreview = ref<string | null>(null)
const wechatQrPreview = ref<string | null>(null)

// Password visibility
const showSmtpPass = ref(false)
const showGoogleSecret = ref(false)
const showFbSecret = ref(false)
const showLinkedinSecret = ref(false)
const showWechatSecret = ref(false)

// Saving flags per tab
const saving = reactive({ general: false, smtp: false, oauth: false, uploads: false })

// SMTP testing
const testingSmtp = ref(false)

async function fetchSettings() {
  loading.value = true
  try {
    const { data } = await adminApi.getSettings()
    const raw: Record<string, any> = data.data ?? data
    Object.assign(s, raw)
    logoPreview.value = s.logo_url ?? null
    faviconPreview.value = s.favicon_url ?? null
    wechatQrPreview.value = s.wechat_qr_url ?? null
  } finally {
    loading.value = false
  }
}

onMounted(fetchSettings)

// ─── Helpers ──────────────────────────────────────────────────────────────────
function pick(keys: string[]): Record<string, any> {
  const obj: Record<string, any> = {}
  keys.forEach((k) => { obj[k] = s[k] ?? null })
  return obj
}

async function saveTab(tab: keyof typeof saving, keys: string[]) {
  saving[tab] = true
  try {
    await adminApi.updateSettings(pick(keys))
    ElMessage.success(t('adminSettings.saveSuccess'))
  } finally {
    saving[tab] = false
  }
}

// ─── File upload helpers ──────────────────────────────────────────────────────
async function uploadFile(
  e: Event,
  uploader: (fd: FormData) => Promise<any>,
  previewRef: { value: string | null },
  settingKey: string,
) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  const fd = new FormData()
  fd.append('file', file)
  const { data } = await uploader(fd)
  const url = data.url ?? data.data?.url
  if (url) {
    previewRef.value = url
    s[settingKey] = url
    ElMessage.success(t('adminSettings.saveSuccess'))
  }
}

// ─── SMTP test ────────────────────────────────────────────────────────────────
async function testSmtp() {
  testingSmtp.value = true
  try {
    await adminApi.testSmtp()
    ElMessage.success(t('adminSettings.smtpTestSuccess'))
  } catch (err: any) {
    const msg = err?.response?.data?.message ?? ''
    ElMessage.error(`${t('adminSettings.smtpTestFailed')}: ${msg}`)
  } finally {
    testingSmtp.value = false
  }
}

// ─── Upload limits helpers ────────────────────────────────────────────────────
const resumeTypeOptions = ['pdf', 'doc', 'docx', 'jpg', 'png']
const avatarTypeOptions = ['jpg', 'png', 'webp']
</script>

<template>
  <div class="settings-page">
    <h1 class="page-title">{{ t('adminSettings.pageTitle') }}</h1>

    <div v-if="loading" class="loading-wrap">
      <el-skeleton :rows="8" animated />
    </div>

    <el-tabs v-else v-model="activeTab" tab-position="left" class="settings-tabs">
      <!-- ─── General ─────────────────────────────────────────────────────── -->
      <el-tab-pane :label="t('adminSettings.tabs.general')" name="general">
        <div class="tab-content">
          <h2 class="section-title">{{ t('adminSettings.tabs.general') }}</h2>

          <!-- Site Name multi-lang -->
          <el-form-item :label="t('adminSettings.siteName')">
            <el-tabs type="border-card" class="lang-inner-tabs">
              <el-tab-pane label="EN">
                <el-input v-model="s.site_name_en" />
              </el-tab-pane>
              <el-tab-pane label="中文">
                <el-input v-model="s.site_name_zh_cn" />
              </el-tab-pane>
              <el-tab-pane label="ภาษาไทย">
                <el-input v-model="s.site_name_th" />
              </el-tab-pane>
            </el-tabs>
          </el-form-item>

          <!-- Logo -->
          <el-form-item :label="t('adminSettings.logo')">
            <div class="upload-row">
              <img v-if="logoPreview" :src="logoPreview" class="logo-preview" alt="logo" />
              <label class="upload-btn">
                {{ t('adminSettings.uploadLogo') }}
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/svg+xml,image/webp"
                  style="display:none"
                  @change="uploadFile($event, adminApi.uploadLogo, logoPreview as any, 'logo_url')"
                />
              </label>
            </div>
          </el-form-item>

          <!-- Favicon -->
          <el-form-item :label="t('adminSettings.favicon')">
            <div class="upload-row">
              <img v-if="faviconPreview" :src="faviconPreview" class="favicon-preview" alt="favicon" />
              <label class="upload-btn">
                {{ t('adminSettings.uploadFavicon') }}
                <input
                  type="file"
                  accept="image/x-icon,image/png,image/vnd.microsoft.icon"
                  style="display:none"
                  @change="uploadFile($event, adminApi.uploadFavicon, faviconPreview as any, 'favicon_url')"
                />
              </label>
            </div>
          </el-form-item>

          <!-- Contact info -->
          <el-form-item :label="t('adminSettings.contactEmail')">
            <el-input v-model="s.contact_email" type="email" style="max-width: 360px" />
          </el-form-item>

          <el-form-item :label="t('adminSettings.contactPhone')">
            <el-input v-model="s.contact_phone" style="max-width: 280px" />
          </el-form-item>

          <!-- Address multi-lang -->
          <el-form-item :label="t('adminSettings.address')">
            <el-tabs type="border-card" class="lang-inner-tabs">
              <el-tab-pane label="EN">
                <el-input v-model="s.address_en" type="textarea" :rows="2" />
              </el-tab-pane>
              <el-tab-pane label="中文">
                <el-input v-model="s.address_zh_cn" type="textarea" :rows="2" />
              </el-tab-pane>
              <el-tab-pane label="ภาษาไทย">
                <el-input v-model="s.address_th" type="textarea" :rows="2" />
              </el-tab-pane>
            </el-tabs>
          </el-form-item>

          <!-- Social links -->
          <h3 class="subsection-title">{{ t('adminSettings.socialLinks') }}</h3>
          <el-form-item label="Facebook URL">
            <el-input v-model="s.facebook_url" style="max-width: 400px" />
          </el-form-item>
          <el-form-item label="Instagram URL">
            <el-input v-model="s.instagram_url" style="max-width: 400px" />
          </el-form-item>
          <el-form-item label="LinkedIn URL">
            <el-input v-model="s.linkedin_url" style="max-width: 400px" />
          </el-form-item>
          <el-form-item :label="t('adminSettings.wechatQr')">
            <div class="upload-row">
              <img v-if="wechatQrPreview" :src="wechatQrPreview" class="qr-preview" alt="wechat qr" />
              <label class="upload-btn">
                Upload QR
                <input
                  type="file"
                  accept="image/jpeg,image/png"
                  style="display:none"
                  @change="uploadFile($event, (fd) => adminApi.uploadLogo(fd), wechatQrPreview as any, 'wechat_qr_url')"
                />
              </label>
            </div>
          </el-form-item>

          <div class="tab-footer">
            <el-button
              type="primary"
              :loading="saving.general"
              @click="saveTab('general', ['site_name_en','site_name_zh_cn','site_name_th','contact_email','contact_phone','address_en','address_zh_cn','address_th','facebook_url','instagram_url','linkedin_url'])"
            >
              {{ t('common.save') }}
            </el-button>
          </div>
        </div>
      </el-tab-pane>

      <!-- ─── SMTP ───────────────────────────────────────────────────────── -->
      <el-tab-pane :label="t('adminSettings.tabs.smtp')" name="smtp">
        <div class="tab-content">
          <h2 class="section-title">{{ t('adminSettings.tabs.smtp') }}</h2>

          <el-form label-position="top" style="max-width: 480px">
            <el-form-item :label="t('adminSettings.smtpHost')">
              <el-input v-model="s.smtp_host" />
            </el-form-item>
            <el-form-item :label="t('adminSettings.smtpPort')">
              <el-input-number v-model="s.smtp_port" :min="1" :max="65535" style="width: 160px" />
            </el-form-item>
            <el-form-item :label="t('adminSettings.encryption')">
              <el-select v-model="s.smtp_encryption" style="width: 200px">
                <el-option label="None" value="none" />
                <el-option label="TLS" value="tls" />
                <el-option label="SSL" value="ssl" />
              </el-select>
            </el-form-item>
            <el-form-item :label="t('adminSettings.smtpUsername')">
              <el-input v-model="s.smtp_username" autocomplete="off" />
            </el-form-item>
            <el-form-item :label="t('adminSettings.smtpPassword')">
              <el-input
                v-model="s.smtp_password"
                :type="showSmtpPass ? 'text' : 'password'"
                autocomplete="new-password"
              >
                <template #suffix>
                  <span class="reveal-btn" @click="showSmtpPass = !showSmtpPass">
                    {{ showSmtpPass ? '🙈' : '👁' }}
                  </span>
                </template>
              </el-input>
            </el-form-item>
            <el-form-item :label="t('adminSettings.fromAddress')">
              <el-input v-model="s.mail_from_address" type="email" />
            </el-form-item>
            <el-form-item :label="t('adminSettings.fromName')">
              <el-input v-model="s.mail_from_name" />
            </el-form-item>
          </el-form>

          <div class="tab-footer">
            <el-button :loading="testingSmtp.value" @click="testSmtp">
              {{ t('adminSettings.testSmtp') }}
            </el-button>
            <el-button
              type="primary"
              :loading="saving.smtp"
              @click="saveTab('smtp', ['smtp_host','smtp_port','smtp_encryption','smtp_username','smtp_password','mail_from_address','mail_from_name'])"
            >
              {{ t('common.save') }}
            </el-button>
          </div>
        </div>
      </el-tab-pane>

      <!-- ─── OAuth ──────────────────────────────────────────────────────── -->
      <el-tab-pane :label="t('adminSettings.tabs.oauth')" name="oauth">
        <div class="tab-content">
          <h2 class="section-title">{{ t('adminSettings.tabs.oauth') }}</h2>

          <el-form label-position="top" style="max-width: 480px">
            <!-- Google -->
            <h3 class="subsection-title">Google</h3>
            <el-form-item label="Client ID">
              <el-input v-model="s.google_client_id" />
            </el-form-item>
            <el-form-item label="Client Secret">
              <el-input v-model="s.google_client_secret" :type="showGoogleSecret ? 'text' : 'password'" autocomplete="off">
                <template #suffix><span class="reveal-btn" @click="showGoogleSecret = !showGoogleSecret">{{ showGoogleSecret ? '🙈' : '👁' }}</span></template>
              </el-input>
            </el-form-item>

            <!-- Facebook -->
            <h3 class="subsection-title">Facebook</h3>
            <el-form-item label="App ID">
              <el-input v-model="s.facebook_app_id" />
            </el-form-item>
            <el-form-item label="App Secret">
              <el-input v-model="s.facebook_app_secret" :type="showFbSecret ? 'text' : 'password'" autocomplete="off">
                <template #suffix><span class="reveal-btn" @click="showFbSecret = !showFbSecret">{{ showFbSecret ? '🙈' : '👁' }}</span></template>
              </el-input>
            </el-form-item>

            <!-- LinkedIn -->
            <h3 class="subsection-title">LinkedIn</h3>
            <el-form-item label="Client ID">
              <el-input v-model="s.linkedin_client_id" />
            </el-form-item>
            <el-form-item label="Client Secret">
              <el-input v-model="s.linkedin_client_secret" :type="showLinkedinSecret ? 'text' : 'password'" autocomplete="off">
                <template #suffix><span class="reveal-btn" @click="showLinkedinSecret = !showLinkedinSecret">{{ showLinkedinSecret ? '🙈' : '👁' }}</span></template>
              </el-input>
            </el-form-item>

            <!-- WeChat -->
            <h3 class="subsection-title">WeChat</h3>
            <el-form-item label="App ID">
              <el-input v-model="s.wechat_app_id" />
            </el-form-item>
            <el-form-item label="App Secret">
              <el-input v-model="s.wechat_app_secret" :type="showWechatSecret ? 'text' : 'password'" autocomplete="off">
                <template #suffix><span class="reveal-btn" @click="showWechatSecret = !showWechatSecret">{{ showWechatSecret ? '🙈' : '👁' }}</span></template>
              </el-input>
            </el-form-item>
          </el-form>

          <div class="tab-footer">
            <el-button
              type="primary"
              :loading="saving.oauth"
              @click="saveTab('oauth', ['google_client_id','google_client_secret','facebook_app_id','facebook_app_secret','linkedin_client_id','linkedin_client_secret','wechat_app_id','wechat_app_secret'])"
            >
              {{ t('common.save') }}
            </el-button>
          </div>
        </div>
      </el-tab-pane>

      <!-- ─── Upload Limits ──────────────────────────────────────────────── -->
      <el-tab-pane :label="t('adminSettings.tabs.uploads')" name="uploads">
        <div class="tab-content">
          <h2 class="section-title">{{ t('adminSettings.tabs.uploads') }}</h2>

          <el-form label-position="top" style="max-width: 480px">
            <el-form-item :label="t('adminSettings.maxResumeSize')">
              <el-input-number v-model="s.max_resume_size_mb" :min="1" :max="200" />
            </el-form-item>
            <el-form-item :label="t('adminSettings.maxAvatarSize')">
              <el-input-number v-model="s.max_avatar_size_mb" :min="1" :max="50" />
            </el-form-item>
            <el-form-item :label="t('adminSettings.allowedResumeTypes')">
              <el-checkbox-group v-model="s.allowed_resume_types">
                <el-checkbox v-for="ext in resumeTypeOptions" :key="ext" :value="ext">{{ ext }}</el-checkbox>
              </el-checkbox-group>
            </el-form-item>
            <el-form-item :label="t('adminSettings.allowedAvatarTypes')">
              <el-checkbox-group v-model="s.allowed_avatar_types">
                <el-checkbox v-for="ext in avatarTypeOptions" :key="ext" :value="ext">{{ ext }}</el-checkbox>
              </el-checkbox-group>
            </el-form-item>
          </el-form>

          <div class="tab-footer">
            <el-button
              type="primary"
              :loading="saving.uploads"
              @click="saveTab('uploads', ['max_resume_size_mb','max_avatar_size_mb','allowed_resume_types','allowed_avatar_types'])"
            >
              {{ t('common.save') }}
            </el-button>
          </div>
        </div>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<style scoped>
.settings-page { padding: 24px; }
.page-title { font-size: 24px; font-weight: 600; color: #003366; margin-bottom: 24px; }
.loading-wrap { max-width: 700px; }

.settings-tabs { min-height: 500px; }
:deep(.el-tabs__content) { padding: 0 0 0 24px; }

.tab-content { max-width: 640px; }
.section-title { font-size: 18px; font-weight: 600; color: #003366; margin: 0 0 20px; }
.subsection-title { font-size: 14px; font-weight: 600; color: #003366; margin: 16px 0 8px; }

.lang-inner-tabs { margin-bottom: 4px; }

.upload-row { display: flex; align-items: center; gap: 12px; }
.logo-preview { max-height: 60px; max-width: 180px; border-radius: 4px; border: 1px solid #dee2e6; }
.favicon-preview { width: 32px; height: 32px; border-radius: 4px; border: 1px solid #dee2e6; }
.qr-preview { width: 80px; height: 80px; border-radius: 4px; border: 1px solid #dee2e6; object-fit: contain; }

.upload-btn {
  display: inline-flex; align-items: center; padding: 7px 14px;
  border: 1px solid #dee2e6; border-radius: 6px; cursor: pointer;
  font-size: 13px; background: #fff; transition: all 0.2s;
}
.upload-btn:hover { border-color: #003366; color: #003366; }

.reveal-btn { cursor: pointer; user-select: none; font-size: 16px; }

.tab-footer {
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f0f0f0;
  display: flex;
  gap: 12px;
}
</style>
