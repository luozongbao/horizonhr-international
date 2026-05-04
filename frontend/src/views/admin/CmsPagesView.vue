<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage } from 'element-plus'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import adminApi from '@/api/admin'

const { t } = useI18n()

// ─── Types ────────────────────────────────────────────────────────────────────
interface CmsPage {
  id: number
  slug: string
  name: string
  updated_at: string
  content_en?: string
  content_zh_cn?: string
  content_th?: string
}

// ─── Pages list ───────────────────────────────────────────────────────────────
const loading = ref(false)
const pages = ref<CmsPage[]>([])

async function fetchPages() {
  loading.value = true
  try {
    const { data } = await adminApi.getPages()
    pages.value = (data.data ?? data) as CmsPage[]
  } finally {
    loading.value = false
  }
}

onMounted(fetchPages)

// ─── Editor dialog ────────────────────────────────────────────────────────────
const editorDialog = ref(false)
const editorPage = ref<CmsPage | null>(null)
const editorLoading = ref(false)
const saveLoading = ref(false)
const langTab = ref('en')

const editorContent = ref({
  en: '',
  zh_cn: '',
  th: '',
})

async function openEditor(page: CmsPage) {
  editorDialog.value = true
  editorLoading.value = true
  langTab.value = 'en'
  try {
    const { data } = await adminApi.getPage(page.id)
    const p = (data.data ?? data) as CmsPage
    editorPage.value = p
    editorContent.value = {
      en: p.content_en ?? '',
      zh_cn: p.content_zh_cn ?? '',
      th: p.content_th ?? '',
    }
  } finally {
    editorLoading.value = false
  }
}

async function savePage() {
  if (!editorPage.value) return
  saveLoading.value = true
  try {
    await adminApi.updatePage(editorPage.value.id, {
      content_en: editorContent.value.en,
      content_zh_cn: editorContent.value.zh_cn,
      content_th: editorContent.value.th,
    })
    ElMessage.success(t('adminCms.pages.saveSuccess'))
    editorDialog.value = false
    // update local list timestamp
    const idx = pages.value.findIndex((p) => p.id === editorPage.value!.id)
    if (idx !== -1) pages.value[idx] = { ...pages.value[idx], updated_at: new Date().toISOString() }
  } finally {
    saveLoading.value = false
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function formatDate(d: string): string {
  if (!d) return '—'
  return new Date(d).toLocaleString()
}

function pageName(page: CmsPage): string {
  return page.name || page.slug.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
}

// Quill toolbar config
const quillToolbar = [
  [{ header: [1, 2, 3, false] }],
  ['bold', 'italic', 'underline', 'strike'],
  [{ list: 'ordered' }, { list: 'bullet' }],
  ['link'],
  ['clean'],
]
</script>

<template>
  <div class="cms-pages">
    <div class="page-header">
      <h1 class="page-title">{{ t('adminCms.pages.pageTitle') }}</h1>
    </div>

    <div class="table-card">
      <el-table v-loading="loading" :data="pages" style="width: 100%" row-key="id">
        <el-table-column :label="t('common.name')" min-width="200">
          <template #default="{ row }">
            <div class="page-name">{{ pageName(row) }}</div>
            <div class="page-slug">{{ row.slug }}</div>
          </template>
        </el-table-column>
        <el-table-column :label="t('adminCms.pages.lastUpdated')" width="200">
          <template #default="{ row }">{{ formatDate(row.updated_at) }}</template>
        </el-table-column>
        <el-table-column :label="t('common.actions')" width="120" fixed="right">
          <template #default="{ row }">
            <el-button size="small" type="primary" link @click="openEditor(row)">
              {{ t('adminCms.pages.editPage') }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div v-if="!loading && pages.length === 0" class="empty-state">
        <el-empty />
      </div>
    </div>

    <!-- Editor Dialog -->
    <el-dialog
      v-model="editorDialog"
      :title="editorPage ? pageName(editorPage) : t('adminCms.pages.editPage')"
      width="820px"
      :close-on-click-modal="false"
    >
      <div v-if="editorLoading" class="editor-loading">
        <el-skeleton :rows="6" animated />
      </div>
      <div v-else>
        <div class="slug-badge">
          <span class="slug-label">Slug:</span>
          <code>{{ editorPage?.slug }}</code>
        </div>

        <el-tabs v-model="langTab" class="lang-tabs">
          <!-- English -->
          <el-tab-pane label="English" name="en">
            <div class="editor-wrap">
              <QuillEditor
                v-model:content="editorContent.en"
                content-type="html"
                :toolbar="quillToolbar"
                theme="snow"
                style="min-height: 320px"
              />
            </div>
          </el-tab-pane>

          <!-- 中文 -->
          <el-tab-pane label="中文" name="zh_cn">
            <div class="editor-wrap">
              <QuillEditor
                v-model:content="editorContent.zh_cn"
                content-type="html"
                :toolbar="quillToolbar"
                theme="snow"
                style="min-height: 320px"
              />
            </div>
          </el-tab-pane>

          <!-- ภาษาไทย -->
          <el-tab-pane label="ภาษาไทย" name="th">
            <div class="editor-wrap">
              <QuillEditor
                v-model:content="editorContent.th"
                content-type="html"
                :toolbar="quillToolbar"
                theme="snow"
                style="min-height: 320px"
              />
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>

      <template #footer>
        <el-button @click="editorDialog = false">{{ t('common.cancel') }}</el-button>
        <el-button type="primary" :loading="saveLoading" @click="savePage">
          {{ t('common.save') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.cms-pages { padding: 24px; }
.page-header { display: flex; align-items: center; margin-bottom: 20px; }
.page-title { font-size: 24px; font-weight: 600; color: #003366; }

.table-card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,51,102,0.06);
  overflow: hidden;
}
.page-name { font-weight: 500; font-size: 14px; color: #1a1a2e; }
.page-slug { font-size: 12px; color: #6c757d; font-family: monospace; margin-top: 2px; }
.empty-state { padding: 40px; }

/* Editor */
.editor-loading { padding: 8px; }
.slug-badge { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; font-size: 13px; color: #6c757d; }
.slug-badge code { background: #f8f9fa; padding: 2px 8px; border-radius: 4px; font-size: 13px; }
.lang-tabs { margin-bottom: 8px; }
.editor-wrap { padding-top: 8px; }
</style>
