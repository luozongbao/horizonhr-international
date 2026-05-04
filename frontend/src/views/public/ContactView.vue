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
    const res = await publicApi.getContactPage()
    const data = res.data?.data
    // CMS page: look in meta or directly on data
    contactInfo.value = data?.meta ?? data ?? null
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
                <a v-if="contactInfo?.wechat" :href="contactInfo.wechat" target="_blank" rel="noopener noreferrer" class="social-chip">WeChat</a>
                <a v-if="contactInfo?.whatsapp" :href="contactInfo.whatsapp" target="_blank" rel="noopener noreferrer" class="social-chip">WhatsApp</a>
                <a v-if="contactInfo?.line" :href="contactInfo.line" target="_blank" rel="noopener noreferrer" class="social-chip">LINE</a>
                <a v-if="contactInfo?.facebook" :href="contactInfo.facebook" target="_blank" rel="noopener noreferrer" class="social-chip">Facebook</a>
                <a v-if="contactInfo?.linkedin" :href="contactInfo.linkedin" target="_blank" rel="noopener noreferrer" class="social-chip">LinkedIn</a>
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
.social-links { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.social-chip {
  display: inline-block;
  padding: 5px 14px;
  border: 1px solid var(--c-border);
  border-radius: 16px;
  font-size: 13px;
  color: var(--c-text);
  text-decoration: none;
  background: #fff;
  transition: all 0.2s;
}
.social-chip:hover { border-color: var(--c-accent); color: var(--c-accent); }

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

