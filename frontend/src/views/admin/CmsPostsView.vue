<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import adminApi, { type PostParams, type PostFormData } from '@/api/admin'

const { t } = useI18n()

// ─── Types ────────────────────────────────────────────────────────────────────
interface PostItem {
  id: number
  title: string
  title_en?: string
  slug?: string
  category?: string
  author?: string
  status: 'published' | 'draft'
  published_at?: string
  thumbnail_url?: string
  content_en?: string
  content_zh_cn?: string
  content_th?: string
}

interface Pagination {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// ─── Data ─────────────────────────────────────────────────────────────────────
const activeTab = ref('')
const search = ref('')
const currentPage = ref(1)
const perPage = 20

const loading = ref(false)
const posts = ref<PostItem[]>([])
const pagination = ref<Pagination | null>(null)

async function fetchPosts() {
  loading.value = true
  try {
    const params: PostParams = {
      search: search.value || undefined,
      status: activeTab.value || undefined,
      per_page: perPage,
      page: currentPage.value,
    }
    const { data } = await adminApi.getPosts(params)
    const res = data.data ?? data
    posts.value = res.data ?? res
    pagination.value = res.meta ?? null
  } finally {
    loading.value = false
  }
}

onMounted(fetchPosts)
watch([activeTab, search, currentPage], fetchPosts)
watch([activeTab, search], () => { currentPage.value = 1 })

// ─── Form dialog ──────────────────────────────────────────────────────────────
const formDialog = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const formLoading = ref(false)
const langTab = ref('en')
const thumbnailFile = ref<File | null>(null)
const thumbnailPreview = ref<string | null>(null)
const imageUploadRef = ref<HTMLInputElement | null>(null)
// quill editor refs per lang
const quillEnRef = ref<any>(null)
const quillZhRef = ref<any>(null)
const quillThRef = ref<any>(null)

const emptyForm = (): PostFormData => ({
  title_en: '',
  title_zh_cn: '',
  title_th: '',
  slug: '',
  category: '',
  content_en: '',
  content_zh_cn: '',
  content_th: '',
  published_at: '',
  status: 'draft',
})

const form = ref<PostFormData>(emptyForm())
const content = ref({ en: '', zh_cn: '', th: '' })

// Slug auto-gen
function generateSlug(title: string): string {
  return title.toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
}

watch(() => form.value.title_en, (val) => {
  if (!isEditing.value || !form.value.slug) {
    form.value.slug = generateSlug(val)
  }
})

const formRules = computed(() => ({
  title_en: [{ required: true, message: t('validation.required'), trigger: 'blur' }],
  slug: [{ required: true, message: t('validation.required'), trigger: 'blur' }],
}))

const formRef = ref<any>(null)

function openCreate() {
  isEditing.value = false
  editingId.value = null
  form.value = emptyForm()
  content.value = { en: '', zh_cn: '', th: '' }
  thumbnailFile.value = null
  thumbnailPreview.value = null
  langTab.value = 'en'
  formDialog.value = true
}

function openEdit(post: PostItem) {
  isEditing.value = true
  editingId.value = post.id
  form.value = {
    title_en: post.title_en ?? post.title ?? '',
    title_zh_cn: '',
    title_th: '',
    slug: post.slug ?? '',
    category: post.category ?? '',
    content_en: post.content_en ?? '',
    content_zh_cn: post.content_zh_cn ?? '',
    content_th: post.content_th ?? '',
    published_at: post.published_at ?? '',
    status: post.status,
  }
  content.value = {
    en: post.content_en ?? '',
    zh_cn: post.content_zh_cn ?? '',
    th: post.content_th ?? '',
  }
  thumbnailFile.value = null
  thumbnailPreview.value = post.thumbnail_url ?? null
  langTab.value = 'en'
  formDialog.value = true
}

function handleThumbnail(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  thumbnailFile.value = file
  thumbnailPreview.value = URL.createObjectURL(file)
}

// Quill toolbar
const quillToolbar = [
  [{ header: [1, 2, 3, false] }],
  ['bold', 'italic', 'underline', 'strike'],
  [{ list: 'ordered' }, { list: 'bullet' }],
  ['link', 'image'],
  ['clean'],
]

// Custom image upload handler for quill
function makeImageHandler(quillRef: any) {
  return function () {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = 'image/*'
    input.onchange = async () => {
      const file = input.files?.[0]
      if (!file) return
      const fd = new FormData()
      fd.append('file', file)
      try {
        const { data } = await adminApi.uploadMedia(fd)
        const url = data.url ?? data.data?.url
        if (url && quillRef.value) {
          const quill = quillRef.value.getQuill()
          const range = quill.getSelection(true)
          quill.insertEmbed(range.index, 'image', url)
        }
      } catch {
        ElMessage.error('Image upload failed')
      }
    }
    input.click()
  }
}

function getModules(quillRef: any) {
  return {
    toolbar: {
      container: quillToolbar,
      handlers: { image: makeImageHandler(quillRef) },
    },
  }
}

async function submitForm(saveStatus: 'draft' | 'published') {
  if (!formRef.value) return
  await formRef.value.validate()
  formLoading.value = true
  try {
    // sync content from editors
    const payload: PostFormData = {
      ...form.value,
      content_en: content.value.en,
      content_zh_cn: content.value.zh_cn,
      content_th: content.value.th,
      status: saveStatus,
    }

    let submitData: PostFormData | FormData = payload
    if (thumbnailFile.value) {
      const fd = new FormData()
      Object.entries(payload).forEach(([k, v]) => {
        if (v !== null && v !== undefined) fd.append(k, String(v))
      })
      fd.append('thumbnail', thumbnailFile.value)
      submitData = fd
    }

    if (isEditing.value && editingId.value) {
      await adminApi.updatePost(editingId.value, submitData)
      ElMessage.success(t('adminCms.news.updateSuccess'))
    } else {
      await adminApi.createPost(submitData)
      ElMessage.success(saveStatus === 'published'
        ? t('adminCms.news.publishSuccess')
        : t('adminCms.news.createSuccess'))
    }
    formDialog.value = false
    fetchPosts()
  } finally {
    formLoading.value = false
  }
}

// ─── Toggle publish ───────────────────────────────────────────────────────────
async function togglePublish(post: PostItem) {
  if (post.status === 'published') {
    await adminApi.unpublishPost(post.id)
    ElMessage.success(t('adminCms.news.unpublishSuccess'))
    updateLocalStatus(post.id, 'draft')
  } else {
    await adminApi.publishPost(post.id)
    ElMessage.success(t('adminCms.news.publishSuccess'))
    updateLocalStatus(post.id, 'published')
  }
}

function updateLocalStatus(id: number, status: 'published' | 'draft') {
  const idx = posts.value.findIndex((p) => p.id === id)
  if (idx !== -1) posts.value[idx] = { ...posts.value[idx], status }
}

// ─── Delete ───────────────────────────────────────────────────────────────────
async function deletePost(post: PostItem) {
  await ElMessageBox.confirm(
    t('adminCms.news.deleteConfirm'),
    t('adminCms.news.pageTitle'),
    { type: 'error', confirmButtonClass: 'el-button--danger' },
  )
  await adminApi.deletePost(post.id)
  ElMessage.success(t('adminCms.news.deleteSuccess'))
  fetchPosts()
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
const statusTagType = (s: string) => s === 'published' ? 'success' : 'info'

function formatDate(d: string): string {
  if (!d) return '—'
  return new Date(d).toLocaleDateString()
}
</script>

<template>
  <div class="posts-page">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">{{ t('adminCms.news.pageTitle') }}</h1>
      <el-button type="primary" @click="openCreate">
        + {{ t('adminCms.news.createPost') }}
      </el-button>
    </div>

    <!-- Search + tabs -->
    <div class="filter-bar">
      <el-input
        v-model="search"
        :placeholder="t('adminUsers.searchPlaceholder')"
        clearable
        style="width: 260px;"
        prefix-icon="Search"
      />
    </div>
    <el-tabs v-model="activeTab" class="status-tabs">
      <el-tab-pane :label="t('seminar.allStatuses')" name="" />
      <el-tab-pane :label="t('adminCms.news.status.published')" name="published" />
      <el-tab-pane :label="t('adminCms.news.status.draft')" name="draft" />
    </el-tabs>

    <!-- Table -->
    <div class="table-card">
      <el-table v-loading="loading" :data="posts" style="width: 100%" row-key="id">
        <!-- Thumbnail -->
        <el-table-column width="90">
          <template #default="{ row }">
            <img v-if="row.thumbnail_url" :src="row.thumbnail_url" class="post-thumb" :alt="row.title" />
            <div v-else class="post-thumb-placeholder">📰</div>
          </template>
        </el-table-column>

        <!-- Title + category -->
        <el-table-column :label="t('adminCms.news.slug')" min-width="240">
          <template #default="{ row }">
            <div class="post-title">{{ row.title_en || row.title }}</div>
            <div class="post-meta">
              <span v-if="row.category" class="post-category">{{ row.category }}</span>
              <span v-if="row.slug" class="post-slug">{{ row.slug }}</span>
            </div>
          </template>
        </el-table-column>

        <!-- Author -->
        <el-table-column :label="t('adminCms.news.author')" width="140">
          <template #default="{ row }">{{ row.author || '—' }}</template>
        </el-table-column>

        <!-- Published At -->
        <el-table-column :label="t('adminCms.news.publishedAt')" width="130">
          <template #default="{ row }">{{ formatDate(row.published_at) }}</template>
        </el-table-column>

        <!-- Status -->
        <el-table-column :label="t('common.status')" width="110">
          <template #default="{ row }">
            <el-tag :type="statusTagType(row.status)" size="small">
              {{ t(`adminCms.news.status.${row.status}`) }}
            </el-tag>
          </template>
        </el-table-column>

        <!-- Actions -->
        <el-table-column :label="t('common.actions')" width="200" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button size="small" link @click="openEdit(row)">{{ t('common.edit') }}</el-button>
              <el-button
                size="small"
                :type="row.status === 'published' ? 'warning' : 'success'"
                link
                @click="togglePublish(row)"
              >
                {{ row.status === 'published' ? t('adminCms.news.unpublish') : t('adminCms.news.publish') }}
              </el-button>
              <el-button size="small" type="danger" link @click="deletePost(row)">
                {{ t('common.delete') }}
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <div v-if="!loading && posts.length === 0" class="empty-state">
        <el-empty :description="t('adminCms.news.noPosts')" />
      </div>

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

    <!-- Create / Edit Dialog -->
    <el-dialog
      v-model="formDialog"
      :title="isEditing ? t('adminCms.news.editPost') : t('adminCms.news.createPost')"
      width="820px"
      :close-on-click-modal="false"
    >
      <el-form ref="formRef" :model="form" :rules="formRules" label-position="top">
        <!-- Slug row -->
        <div class="form-row">
          <el-form-item :label="t('adminCms.news.slug')" prop="slug" style="flex: 1">
            <el-input v-model="form.slug" />
          </el-form-item>
          <el-form-item :label="t('adminCms.news.category')" style="flex: 1">
            <el-input v-model="form.category" />
          </el-form-item>
        </div>

        <!-- Thumbnail -->
        <el-form-item :label="t('adminCms.news.thumbnail')">
          <div class="upload-row">
            <div v-if="thumbnailPreview" class="thumb-preview">
              <img :src="thumbnailPreview" alt="thumbnail" />
            </div>
            <label class="upload-btn">
              {{ t('common.uploadPhoto') }}
              <input type="file" accept="image/jpeg,image/png,image/webp" style="display:none" @change="handleThumbnail" />
            </label>
          </div>
        </el-form-item>

        <!-- Published At -->
        <el-form-item :label="t('adminCms.news.publishedAt')">
          <el-date-picker
            v-model="form.published_at"
            type="datetime"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 280px"
          />
        </el-form-item>

        <!-- Multi-language title + content -->
        <el-tabs v-model="langTab" class="lang-tabs">
          <!-- English -->
          <el-tab-pane label="English" name="en">
            <el-form-item :label="'Title (EN)'" prop="title_en">
              <el-input v-model="form.title_en" />
            </el-form-item>
            <el-form-item :label="t('adminCms.news.content')">
              <div class="editor-wrap">
                <QuillEditor
                  ref="quillEnRef"
                  v-model:content="content.en"
                  content-type="html"
                  :modules="getModules(quillEnRef)"
                  theme="snow"
                  style="min-height: 280px"
                />
              </div>
            </el-form-item>
          </el-tab-pane>

          <!-- 中文 -->
          <el-tab-pane label="中文" name="zh_cn">
            <el-form-item label="Title (中文)">
              <el-input v-model="form.title_zh_cn" />
            </el-form-item>
            <el-form-item :label="t('adminCms.news.content')">
              <div class="editor-wrap">
                <QuillEditor
                  ref="quillZhRef"
                  v-model:content="content.zh_cn"
                  content-type="html"
                  :modules="getModules(quillZhRef)"
                  theme="snow"
                  style="min-height: 280px"
                />
              </div>
            </el-form-item>
          </el-tab-pane>

          <!-- ภาษาไทย -->
          <el-tab-pane label="ภาษาไทย" name="th">
            <el-form-item label="Title (ภาษาไทย)">
              <el-input v-model="form.title_th" />
            </el-form-item>
            <el-form-item :label="t('adminCms.news.content')">
              <div class="editor-wrap">
                <QuillEditor
                  ref="quillThRef"
                  v-model:content="content.th"
                  content-type="html"
                  :modules="getModules(quillThRef)"
                  theme="snow"
                  style="min-height: 280px"
                />
              </div>
            </el-form-item>
          </el-tab-pane>
        </el-tabs>
      </el-form>

      <template #footer>
        <el-button @click="formDialog = false">{{ t('common.cancel') }}</el-button>
        <el-button :loading="formLoading" @click="submitForm('draft')">
          {{ t('adminCms.news.saveAsDraft') }}
        </el-button>
        <el-button type="primary" :loading="formLoading" @click="submitForm('published')">
          {{ t('adminCms.news.publish') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.posts-page { padding: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-title { font-size: 24px; font-weight: 600; color: #003366; }
.filter-bar { display: flex; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; align-items: center; }
.status-tabs { margin-bottom: 8px; }

.table-card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,51,102,0.06);
  overflow: hidden;
}

.post-thumb { width: 70px; height: 44px; object-fit: cover; border-radius: 4px; display: block; }
.post-thumb-placeholder {
  width: 70px; height: 44px; background: #f0f5ff; border-radius: 4px;
  display: flex; align-items: center; justify-content: center; font-size: 20px;
}
.post-title { font-weight: 500; font-size: 14px; color: #1a1a2e; }
.post-meta { display: flex; gap: 8px; margin-top: 2px; }
.post-category { font-size: 11px; background: #E6F0FF; color: #003366; padding: 1px 6px; border-radius: 10px; }
.post-slug { font-size: 11px; color: #6c757d; font-family: monospace; }

.action-buttons { display: flex; flex-wrap: wrap; gap: 4px; }
.empty-state { padding: 40px; }
.pagination { padding: 16px; display: flex; justify-content: center; }

/* Form */
.form-row { display: flex; gap: 16px; }
.form-row .el-form-item { min-width: 0; }
.lang-tabs { margin-top: 8px; }
.editor-wrap { padding-top: 4px; }

.upload-row { display: flex; align-items: center; gap: 12px; }
.thumb-preview { width: 112px; height: 63px; border-radius: 4px; overflow: hidden; border: 2px solid #dee2e6; }
.thumb-preview img { width: 100%; height: 100%; object-fit: cover; }
.upload-btn {
  display: inline-flex; align-items: center; padding: 8px 16px;
  border: 1px solid #dee2e6; border-radius: 6px; cursor: pointer;
  font-size: 13px; background: #fff; transition: all 0.2s;
}
.upload-btn:hover { border-color: #003366; color: #003366; }
</style>
