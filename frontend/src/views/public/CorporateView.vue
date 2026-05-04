<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'
import { useSanitize } from '@/composables/useSanitize'
import type { ContactPayload } from '@/api/public'
import { usePageMeta } from '@/composables/usePageMeta'

const { t } = useI18n()
const { sanitize } = useSanitize()

usePageMeta({
  title: t('corporate.pageTitle'),
  description: t('corporate.pageDesc'),
})

/* ─── CMS ─────────────────────────────────────────── */
interface CmsPage {
  title?: string
  content?: string
  meta?: Record<string, unknown>
}

const page = ref<CmsPage | null>(null)
const pageLoading = ref(true)

async function fetchPage() {
  try {
    const res = await publicApi.getCorporatePage()
    page.value = res.data?.data ?? null
  } catch {
    // use defaults
  } finally {
    pageLoading.value = false
  }
}

/* ─── Static Content ──────────────────────────────── */
const benefits = computed(() => [
  { icon: '&#9989;', titleKey: 'corporate.benefit1', descKey: 'corporate.benefit1Desc' },
  { icon: '&#127758;', titleKey: 'corporate.benefit2', descKey: 'corporate.benefit2Desc' },
  { icon: '&#128279;', titleKey: 'corporate.benefit3', descKey: 'corporate.benefit3Desc' },
  { icon: '&#128483;', titleKey: 'corporate.benefit4', descKey: 'corporate.benefit4Desc' },
])

const steps = computed(() => [
  { num: 1, titleKey: 'corporate.step1', descKey: 'corporate.step1Desc' },
  { num: 2, titleKey: 'corporate.step2', descKey: 'corporate.step2Desc' },
  { num: 3, titleKey: 'corporate.step3', descKey: 'corporate.step3Desc' },
  { num: 4, titleKey: 'corporate.step4', descKey: 'corporate.step4Desc' },
])

/* ─── Contact Form ────────────────────────────────── */
interface FormData {
  company_name: string
  name: string
  email: string
  phone: string
  message: string
}

const form = ref<FormData>({
  company_name: '',
  name: '',
  email: '',
  phone: '',
  message: '',
})

const formRules = {
  company_name: [{ required: true, message: 'Company name is required', trigger: 'blur' }],
  name: [{ required: true, message: 'Contact name is required', trigger: 'blur' }],
  email: [
    { required: true, message: 'Email is required', trigger: 'blur' },
    { type: 'email' as const, message: 'Please enter a valid email', trigger: 'blur' },
  ],
  message: [{ required: true, message: 'Message is required', trigger: 'blur' }],
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
    company_name: form.value.company_name,
    subject: 'Corporate Partnership Inquiry',
    message: form.value.message,
  }

  try {
    await publicApi.submitContact(payload)
    submitted.value = true
    form.value = { company_name: '', name: '', email: '', phone: '', message: '' }
  } catch (err: unknown) {
    const e = err as { response?: { data?: { message?: string } } }
    submitError.value = e?.response?.data?.message ?? t('corporate.submitFailed')
  } finally {
    submitting.value = false
  }
}

onMounted(fetchPage)
</script>

<template>
  <div class="corporate-page">

    <!-- Page Hero -->
    <section class="page-hero">
      <div class="container">
        <h1 class="hero-title">{{ t('corporate.pageTitle') }}</h1>
        <p class="hero-subtitle">{{ t('corporate.subtitle') }}</p>
        <nav class="breadcrumb">
          <router-link to="/">{{ t('nav.home') }}</router-link>
          <span class="sep">/</span>
          <span>{{ t('corporate.pageTitle') }}</span>
        </nav>
      </div>
    </section>

    <!-- CMS Content (optional) -->
    <section v-if="!pageLoading && page?.content" class="section section-white">
      <div class="container">
        <div class="rich-content cms-intro" v-html="sanitize(page.content)"></div>
      </div>
    </section>

    <!-- Benefits -->
    <section class="section section-light">
      <div class="container">
        <h2 class="section-title">{{ t('corporate.benefitsTitle') }}</h2>
        <div class="benefits-grid">
          <div v-for="b in benefits" :key="b.titleKey" class="benefit-card">
            <div class="benefit-icon" v-html="b.icon"></div>
            <h3>{{ t(b.titleKey) }}</h3>
            <p>{{ t(b.descKey) }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section class="section section-white">
      <div class="container">
        <h2 class="section-title">{{ t('corporate.howItWorksTitle') }}</h2>
        <div class="steps-row">
          <div v-for="(step, idx) in steps" :key="step.num" class="step-item">
            <div class="step-num">{{ step.num }}</div>
            <div v-if="idx < steps.length - 1" class="step-connector"></div>
            <h4 class="step-title">{{ t(step.titleKey) }}</h4>
            <p class="step-desc">{{ t(step.descKey) }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Banner -->
    <section class="cta-section">
      <div class="container cta-inner">
        <h2 class="cta-title">{{ t('home.cta.title') }}</h2>
        <div class="cta-btns">
          <router-link to="/talent">
            <el-button size="large" class="btn-accent">{{ t('nav.talent') }}</el-button>
          </router-link>
          <a href="#inquiry-form">
            <el-button size="large" class="btn-outline-white">{{ t('corporate.inquiryForm') }}</el-button>
          </a>
        </div>
      </div>
    </section>

    <!-- Inquiry Form -->
    <section id="inquiry-form" class="section section-light">
      <div class="container form-container">
        <h2 class="section-title">{{ t('corporate.inquiryForm') }}</h2>

        <template v-if="submitted">
          <el-result
            icon="success"
            :title="t('corporate.inquirySuccess')"
          >
            <template #extra>
              <el-button type="primary" @click="submitted = false">
                {{ t('corporate.submitInquiry') }} {{ t('auth.noAccount') ? '' : '' }}
              </el-button>
            </template>
          </el-result>
        </template>

        <template v-else>
          <el-alert
            v-if="submitError"
            :title="submitError"
            type="error"
            show-icon
            class="form-error"
            :closable="false"
          />
          <el-form
            ref="formRef"
            :model="form"
            :rules="formRules"
            label-position="top"
            class="inquiry-form"
          >
            <div class="form-row">
              <el-form-item :label="t('corporate.companyName')" prop="company_name">
                <el-input v-model="form.company_name" />
              </el-form-item>
              <el-form-item :label="t('corporate.contactName')" prop="name">
                <el-input v-model="form.name" />
              </el-form-item>
            </div>
            <div class="form-row">
              <el-form-item :label="t('corporate.email')" prop="email">
                <el-input v-model="form.email" type="email" />
              </el-form-item>
              <el-form-item :label="t('corporate.phone')" prop="phone">
                <el-input v-model="form.phone" />
              </el-form-item>
            </div>
            <el-form-item :label="t('corporate.message')" prop="message">
              <el-input
                v-model="form.message"
                type="textarea"
                :rows="5"
                :placeholder="t('corporate.messagePlaceholder')"
              />
            </el-form-item>
            <el-form-item>
              <el-button
                type="primary"
                size="large"
                :loading="submitting"
                @click="handleSubmit"
                class="submit-btn"
              >{{ t('corporate.submitInquiry') }}</el-button>
            </el-form-item>
          </el-form>
        </template>
      </div>
    </section>

  </div>
</template>

<style scoped>
.corporate-page {
  --c-primary: #003366;
  --c-accent: #FF6B35;
  --c-secondary: #E6F0FF;
  --c-text: #1A1A2E;
  --c-muted: #6C757D;
  --c-border: #DEE2E6;
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 48px; }
.section { padding: 64px 0; }
.section-white { background: #fff; }
.section-light { background: #F5F7FA; }

/* Hero */
.page-hero {
  background: linear-gradient(135deg, var(--c-primary) 0%, #004080 100%);
  padding: 56px 0;
  color: #fff;
}
.hero-title { font-size: 40px; font-weight: 700; margin-bottom: 8px; }
.hero-subtitle { font-size: 16px; opacity: 0.85; margin-bottom: 16px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.8; }
.breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.breadcrumb a:hover { color: #fff; }
.sep { opacity: 0.5; }

.section-title { font-size: 32px; font-weight: 600; color: var(--c-primary); text-align: center; margin-bottom: 40px; }

.rich-content { font-size: 16px; color: var(--c-text); line-height: 1.8; }
.rich-content :deep(p) { margin-bottom: 12px; }
.rich-content :deep(h2), .rich-content :deep(h3) { color: var(--c-primary); margin: 20px 0 12px; }
.cms-intro { max-width: 820px; margin: 0 auto; }

/* Benefits */
.benefits-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.benefit-card {
  background: #fff; border-radius: 8px; padding: 32px 20px;
  text-align: center; box-shadow: 0 2px 8px rgba(0,51,102,0.06);
  transition: transform 0.2s, box-shadow 0.2s;
}
.benefit-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,51,102,0.10); }
.benefit-icon { font-size: 40px; margin-bottom: 16px; }
.benefit-card h3 { font-size: 15px; font-weight: 600; color: var(--c-primary); margin-bottom: 10px; }
.benefit-card p { font-size: 14px; color: var(--c-muted); line-height: 1.6; }

/* Steps */
.steps-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; position: relative; }
.step-item { text-align: center; padding: 0 16px; position: relative; }
.step-num {
  width: 52px; height: 52px; border-radius: 50%;
  background: var(--c-primary); color: #fff;
  font-size: 22px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 16px;
  position: relative; z-index: 2;
}
.step-connector {
  position: absolute; top: 26px; left: calc(50% + 26px);
  width: calc(100% - 52px); height: 2px;
  background: var(--c-secondary);
}
.step-title { font-size: 15px; font-weight: 600; color: var(--c-primary); margin-bottom: 8px; }
.step-desc { font-size: 13px; color: var(--c-muted); line-height: 1.5; }

/* CTA */
.cta-section { background: var(--c-primary); padding: 56px 0; }
.cta-inner { text-align: center; }
.cta-title { font-size: 36px; font-weight: 600; color: #fff; margin-bottom: 28px; }
.cta-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
.btn-accent { background: var(--c-accent) !important; border-color: var(--c-accent) !important; color: #fff !important; font-size: 15px !important; padding: 12px 28px !important; height: auto !important; }
.btn-outline-white { background: transparent !important; border-color: rgba(255,255,255,0.6) !important; color: #fff !important; font-size: 15px !important; padding: 12px 28px !important; height: auto !important; }
.btn-outline-white:hover { background: rgba(255,255,255,0.1) !important; }

/* Form */
.form-container { max-width: 760px; }
.form-error { margin-bottom: 24px; }
.inquiry-form { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,51,102,0.06); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }
.submit-btn { width: 100%; height: 46px; font-size: 15px; }

/* Responsive */
@media (max-width: 1024px) {
  .benefits-grid { grid-template-columns: repeat(2, 1fr); }
  .steps-row { grid-template-columns: repeat(2, 1fr); gap: 32px; }
  .step-connector { display: none; }
}
@media (max-width: 768px) {
  .container { padding: 0 24px; }
  .section { padding: 40px 0; }
  .hero-title { font-size: 28px; }
  .cta-title { font-size: 24px; }
  .benefits-grid { grid-template-columns: 1fr; }
  .steps-row { grid-template-columns: 1fr; gap: 24px; }
  .form-row { grid-template-columns: 1fr; }
}
</style>
