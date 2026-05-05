<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'
import type { ContactPayload } from '@/api/public'
import { usePageMeta } from '@/composables/usePageMeta'

const { t } = useI18n()

usePageMeta({
  title: t('contact.pageTitle'),
  description: t('contact.pageDesc'),
})

/* ─── CMS contact info ───────────────────────── */
interface ContactMeta {
  phone?: string | string[]
  email?: string | string[]
  address?: string
  wechat?: string
  whatsapp?: string
  line?: string
  facebook?: string
  linkedin?: string
  office_hours?: string
}

const contactInfo = ref<ContactMeta | null>(null)

async function fetchContactPage() {
  try {
    const [pageRes, settingsRes] = await Promise.all([
      publicApi.getContactPage(),
      publicApi.getPublicSettings(),
    ])
    const pageData = pageRes.data?.data
    const settings = settingsRes.data?.data ?? {}

    // Merge CMS page meta (phone, email, address) with settings (social links)
    const pageMeta = pageData?.meta ?? pageData ?? {}
    contactInfo.value = {
      phone:        pageMeta.phone        ?? settings.contact_phone   ?? null,
      email:        pageMeta.email        ?? settings.contact_email   ?? null,
      address:      pageMeta.address      ?? settings.contact_address ?? null,
      office_hours: pageMeta.office_hours ?? null,
      wechat:       settings.social_wechat    || null,
      whatsapp:     settings.social_whatsapp  || null,
      line:         settings.social_line      || null,
      facebook:     settings.social_facebook  || null,
      linkedin:     settings.social_linkedin  || null,
    }
  } catch {
    // use defaults / hide info section
  }
}

/* ─── Form ───────────────────────────────────── */
interface FormData {
  name: string
  email: string
  phone: string
  subject: string
  message: string
}

const form = ref<FormData>({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})

const formRules = {
  name: [{ required: true, message: () => t('validation.nameRequired'), trigger: 'blur' }],
  email: [
    { required: true, message: () => t('validation.emailRequired'), trigger: 'blur' },
    { type: 'email' as const, message: () => t('validation.emailInvalid'), trigger: 'blur' },
  ],
  subject: [{ required: true, message: 'Subject is required', trigger: 'blur' }],
  message: [
    { required: true, message: 'Message is required', trigger: 'blur' },
    { max: 2000, message: t('contact.messageMaxLength'), trigger: 'blur' },
  ],
}

const formRef = ref()
const submitting = ref(false)
const submitted = ref(false)
const submitError = ref('')

async function handleSubmit() {
  try {
    await formRef.value.validate()
  } catch {
    return
  }

  submitting.value = true
  submitError.value = ''

  const payload: ContactPayload = {
    name: form.value.name,
    email: form.value.email,
    phone: form.value.phone || undefined,
    subject: form.value.subject,
    message: form.value.message,
  }

  try {
    await publicApi.submitContact(payload)
    submitted.value = true
    form.value = { name: '', email: '', phone: '', subject: '', message: '' }
  } catch (err: unknown) {
    const status = (err as { response?: { status?: number } })?.response?.status
    if (status === 429) {
      submitError.value = t('contact.tooManyRequests')
    } else {
      submitError.value = t('common.error')
    }
  } finally {
    submitting.value = false
  }
}

/* ─── Helpers ────────────────────────────────── */
function asArray(val: string | string[] | undefined): string[] {
  if (!val) return []
  return Array.isArray(val) ? val : [val]
}

onMounted(fetchContactPage)
</script>

<template>
  <div class="contact-page">

    <!-- Hero -->
    <section class="page-hero">
      <div class="container">
        <h1 class="hero-title">{{ t('contact.pageTitle') }}</h1>
        <nav class="breadcrumb">
          <router-link to="/">{{ t('nav.home') }}</router-link>
          <span class="sep">/</span>
          <span>{{ t('contact.pageTitle') }}</span>
        </nav>
      </div>
    </section>

    <!-- Main Content -->
    <section class="section">
      <div class="container wide">
        <div class="contact-layout">

          <!-- Left: Info Cards -->
          <aside class="contact-info">
            <h2 class="info-heading">{{ t('contact.getInTouch') }}</h2>

            <!-- Phone -->
            <div v-if="asArray(contactInfo?.phone).length" class="info-card">
              <div class="info-icon">&#128222;</div>
              <div class="info-body">
                <h3 class="info-label">{{ t('contact.infoPhone') }}</h3>
                <p v-for="ph in asArray(contactInfo?.phone)" :key="ph" class="info-value">
                  <a :href="`tel:${ph}`">{{ ph }}</a>
                </p>
              </div>
            </div>
            <div v-else class="info-card">
              <div class="info-icon">&#128222;</div>
              <div class="info-body">
                <h3 class="info-label">{{ t('contact.infoPhone') }}</h3>
                <p class="info-value">+86 xxx xxxx xxxx</p>
              </div>
            </div>

            <!-- Email -->
            <div v-if="asArray(contactInfo?.email).length" class="info-card">
              <div class="info-icon">&#128140;</div>
              <div class="info-body">
                <h3 class="info-label">{{ t('contact.infoEmail') }}</h3>
                <p v-for="em in asArray(contactInfo?.email)" :key="em" class="info-value">
                  <a :href="`mailto:${em}`">{{ em }}</a>
                </p>
              </div>
            </div>
            <div v-else class="info-card">
              <div class="info-icon">&#128140;</div>
              <div class="info-body">
                <h3 class="info-label">{{ t('contact.infoEmail') }}</h3>
                <p class="info-value">info@horizon-international.com</p>
              </div>
            </div>

            <!-- Address -->
            <div class="info-card">
              <div class="info-icon">&#128205;</div>
              <div class="info-body">
                <h3 class="info-label">{{ t('contact.infoAddress') }}</h3>
                <p class="info-value">{{ contactInfo?.address ?? 'Hubei, China' }}</p>
              </div>
            </div>

            <!-- Office Hours -->
            <div v-if="contactInfo?.office_hours" class="info-card">
              <div class="info-icon">&#128336;</div>
              <div class="info-body">
                <h3 class="info-label">{{ t('contact.infoOfficeHours') }}</h3>
                <p class="info-value">{{ contactInfo.office_hours }}</p>
              </div>
            </div>

            <!-- Social -->
            <div v-if="contactInfo?.wechat || contactInfo?.whatsapp || contactInfo?.line || contactInfo?.facebook || contactInfo?.linkedin" class="social-row">
              <h3 class="info-label">{{ t('contact.followUs') }}</h3>
              <div class="social-links">
                <a v-if="contactInfo?.wechat" :href="contactInfo.wechat" target="_blank" rel="noopener noreferrer" class="social-chip social-wechat">
                  <svg class="social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M8.69 11.22a1.14 1.14 0 1 1 0-2.28 1.14 1.14 0 0 1 0 2.28zm6.42 0a1.14 1.14 0 1 1 0-2.28 1.14 1.14 0 0 1 0 2.28zM2 10.07C2 5.61 6.48 2 12 2s10 3.61 10 8.07c0 4.45-4.48 8.07-10 8.07-.96 0-1.9-.12-2.77-.35L4.5 21l1.4-4.17C3.66 15.41 2 12.85 2 10.07zm12.56 5.46a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-5.33 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                  </svg>
                  WeChat
                </a>
                <a v-if="contactInfo?.whatsapp" :href="contactInfo.whatsapp" target="_blank" rel="noopener noreferrer" class="social-chip social-whatsapp">
                  <svg class="social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M17.47 14.38c-.27-.14-1.62-.8-1.87-.89-.25-.09-.44-.14-.62.14-.18.27-.7.89-.86 1.07-.16.18-.32.2-.59.07-.27-.14-1.13-.42-2.15-1.33a8.1 8.1 0 0 1-1.49-1.85c-.16-.27-.02-.42.12-.56.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.62-1.5-.85-2.05-.22-.54-.45-.46-.62-.47H9.1c-.18 0-.46.07-.7.34-.25.27-.95.93-.95 2.27s.97 2.63 1.1 2.81c.14.18 1.9 2.9 4.61 4.06.64.28 1.14.44 1.53.56.64.2 1.22.17 1.68.1.51-.08 1.58-.65 1.8-1.27.22-.62.22-1.16.16-1.27-.07-.11-.25-.18-.52-.32zM12.05 2.1a9.93 9.93 0 0 0-8.4 15.27L2 22l4.77-1.62A9.93 9.93 0 1 0 12.05 2.1z"/>
                  </svg>
                  WhatsApp
                </a>
                <a v-if="contactInfo?.line" :href="contactInfo.line" target="_blank" rel="noopener noreferrer" class="social-chip social-line">
                  <svg class="social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386a.632.632 0 0 1-.629-.629V8.108c0-.345.281-.63.63-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016a.63.63 0 0 1-.63.63.624.624 0 0 1-.503-.254l-2.414-3.299v2.921a.63.63 0 0 1-.63.63.631.631 0 0 1-.63-.63V8.108a.63.63 0 0 1 1.133-.387l2.414 3.298V8.108a.63.63 0 0 1 1.26 0v4.771zm-5.741 0a.631.631 0 0 1-.63.63.631.631 0 0 1-.629-.63V8.108a.631.631 0 0 1 1.259 0v4.771zm-2.466.629H4.917a.63.63 0 0 1-.63-.629V8.108a.631.631 0 0 1 1.259 0v4.141h1.763c.348 0 .63.283.63.63 0 .344-.282.629-.63.629zM24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314z"/>
                  </svg>
                  LINE
                </a>
                <a v-if="contactInfo?.facebook" :href="contactInfo.facebook" target="_blank" rel="noopener noreferrer" class="social-chip social-facebook">
                  <svg class="social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                  </svg>
                  Facebook
                </a>
                <a v-if="contactInfo?.linkedin" :href="contactInfo.linkedin" target="_blank" rel="noopener noreferrer" class="social-chip social-linkedin">
                  <svg class="social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
                    <rect x="2" y="9" width="4" height="12"/>
                    <circle cx="4" cy="4" r="2"/>
                  </svg>
                  LinkedIn
                </a>
              </div>
            </div>
          </aside>

          <!-- Right: Form -->
          <div class="contact-form-wrap">
            <h2 class="form-heading">{{ t('contact.send') }}</h2>

            <!-- Success state -->
            <div v-if="submitted" class="success-banner">
              <span class="success-icon">&#10003;</span>
              {{ t('contact.success') }}
            </div>

            <el-form
              v-else
              ref="formRef"
              :model="form"
              :rules="formRules"
              label-position="top"
              @submit.prevent="handleSubmit"
            >
              <div class="form-row">
                <el-form-item :label="t('contact.name')" prop="name">
                  <el-input v-model="form.name" :placeholder="t('contact.name')" />
                </el-form-item>
                <el-form-item :label="t('contact.email')" prop="email">
                  <el-input v-model="form.email" type="email" :placeholder="t('contact.email')" />
                </el-form-item>
              </div>

              <div class="form-row">
                <el-form-item :label="t('contact.phone')">
                  <el-input v-model="form.phone" :placeholder="t('contact.phone')" />
                </el-form-item>
                <el-form-item :label="t('contact.subject')" prop="subject">
                  <el-input v-model="form.subject" :placeholder="t('contact.subject')" />
                </el-form-item>
              </div>

              <el-form-item :label="t('contact.message')" prop="message">
                <el-input
                  v-model="form.message"
                  type="textarea"
                  :rows="5"
                  :placeholder="t('contact.messagePlaceholder')"
                  :maxlength="2000"
                  show-word-limit
                />
              </el-form-item>

              <div v-if="submitError" class="error-banner">{{ submitError }}</div>

              <el-button
                type="primary"
                native-type="submit"
                :loading="submitting"
                class="submit-btn"
                @click="handleSubmit"
              >
                {{ t('contact.send') }}
              </el-button>
            </el-form>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<style scoped>
.contact-page {
  --c-primary: #003366;
  --c-secondary: #E6F0FF;
  --c-accent: #0066CC;
  --c-text: #1A1A2E;
  --c-muted: #6C757D;
  --c-border: #DEE2E6;
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 48px; }
.container.wide { max-width: 1200px; }
.section { padding: 64px 0; }

/* Hero */
.page-hero {
  background: linear-gradient(135deg, var(--c-primary) 0%, #004080 100%);
  padding: 48px 0;
  color: #fff;
}
.hero-title { font-size: 40px; font-weight: 700; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.8; }
.breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.breadcrumb a:hover { color: #fff; }
.sep { opacity: 0.5; }

/* Layout */
.contact-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 60px;
  align-items: start;
}

/* Info */
.contact-info { }
.info-heading {
  font-size: 22px;
  font-weight: 700;
  color: var(--c-primary);
  margin-bottom: 28px;
}
.info-card {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  margin-bottom: 20px;
  padding: 16px;
  background: var(--c-secondary);
  border-radius: 8px;
}
.info-icon { font-size: 22px; flex-shrink: 0; line-height: 1; }
.info-label { font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin-bottom: 4px; letter-spacing: 0.5px; }
.info-value { font-size: 15px; color: var(--c-text); margin: 0; }
.info-value a { color: var(--c-accent); text-decoration: none; }
.info-value a:hover { text-decoration: underline; }

/* Social */
.social-row { margin-top: 24px; }
.social-links { display: flex; flex-direction: column; gap: 8px; margin-top: 10px; }
.social-chip {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 9px 16px;
  border: 1px solid var(--c-border);
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: var(--c-text);
  text-decoration: none;
  background: #fff;
  transition: all 0.2s;
}
.social-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}
.social-chip:hover { background: var(--c-secondary); border-color: var(--c-accent); }
.social-wechat   .social-icon { color: #07C160; }
.social-whatsapp .social-icon { color: #25D366; }
.social-line     .social-icon { color: #00C300; }
.social-facebook .social-icon { color: #1877F2; }
.social-linkedin .social-icon { color: #0A66C2; }
.social-wechat:hover   { border-color: #07C160; }
.social-whatsapp:hover { border-color: #25D366; }
.social-line:hover     { border-color: #00C300; }
.social-facebook:hover { border-color: #1877F2; }
.social-linkedin:hover { border-color: #0A66C2; }

/* Form */
.contact-form-wrap {
  background: #fff;
  border: 1px solid var(--c-border);
  border-radius: 12px;
  padding: 40px;
}
.form-heading {
  font-size: 22px;
  font-weight: 700;
  color: var(--c-primary);
  margin-bottom: 28px;
}
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.submit-btn {
  width: 100%;
  height: 48px;
  font-size: 16px;
  font-weight: 600;
  margin-top: 8px;
  background: var(--c-primary);
  border-color: var(--c-primary);
}
.submit-btn:hover { background: var(--c-accent); border-color: var(--c-accent); }

/* Success / Error */
.success-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 24px;
  background: #f0fff4;
  border: 1px solid #b2dfdb;
  border-radius: 8px;
  color: #1b5e20;
  font-size: 16px;
  font-weight: 500;
}
.success-icon { font-size: 20px; color: #4caf50; }
.error-banner {
  margin-bottom: 16px;
  padding: 12px 16px;
  background: #fff3f3;
  border: 1px solid #ffcccc;
  border-radius: 6px;
  color: #c62828;
  font-size: 14px;
}

/* Responsive */
@media (max-width: 900px) {
  .contact-layout { grid-template-columns: 1fr; gap: 40px; }
  .container { padding: 0 24px; }
  .hero-title { font-size: 28px; }
  .contact-form-wrap { padding: 24px; }
}
@media (max-width: 600px) {
  .form-row { grid-template-columns: 1fr; }
}
</style>

