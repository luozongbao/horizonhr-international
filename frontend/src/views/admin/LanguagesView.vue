<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import adminApi from '@/api/admin'

const { t } = useI18n()

// ─── Types ────────────────────────────────────────────────────────────────────
interface Language {
  id: number
  name: string
  code: string
  is_enabled: boolean
  is_default: boolean
  updated_at?: string
}

interface TranslationItem {
  key: string
  value: string
  updated_at?: string
  editing?: boolean
  editValue?: string
  saving?: boolean
}

// ─── Languages section ────────────────────────────────────────────────────────
const langLoading = ref(false)
const languages = ref<Language[]>([])

async function fetchLanguages() {
  langLoading.value = true
  try {
    const { data } = await adminApi.getLanguages()
    languages.value = data.data ?? data
  } finally {
    langLoading.value = false
  }
}

async function toggleEnabled(lang: Language) {
  const newVal = !lang.is_enabled
  await adminApi.updateLanguage(lang.id, { is_enabled: newVal })
  lang.is_enabled = newVal
  ElMessage.success(t('adminLanguages.updateSuccess'))
}

async function setDefault(lang: Language) {
  if (lang.is_default) return
  await ElMessageBox.confirm(
    `Set "${lang.name}" as the default language?`,
    t('adminLanguages.setDefault'),
    { type: 'warning' },
  )
  await adminApi.updateLanguage(lang.id, { is_default: true })
  // Only one can be default — update local state
  languages.value.forEach((l) => { l.is_default = l.id === lang.id })
  ElMessage.success(t('adminLanguages.updateSuccess'))
}

// ─── Translations section ─────────────────────────────────────────────────────
const selectedLang = ref('en')
const searchKey = ref('')
const transLoading = ref(false)
const allTranslations = ref<TranslationItem[]>([])
const importInputRef = ref<HTMLInputElement | null>(null)
const importing = ref(false)
const exporting = ref(false)

const filteredTranslations = computed(() => {
  const q = searchKey.value.trim().toLowerCase()
  if (!q) return allTranslations.value
  return allTranslations.value.filter(
    (item) => item.key.toLowerCase().includes(q) || item.value.toLowerCase().includes(q),
  )
})

async function fetchTranslations() {
  transLoading.value = true
  try {
    const { data } = await adminApi.getTranslations(selectedLang.value)
    const raw: Record<string, string> = data.data ?? data
    allTranslations.value = Object.entries(raw).map(([key, value]) => ({
      key,
      value,
      editing: false,
      editValue: value,
      saving: false,
    }))
  } finally {
    transLoading.value = false
  }
}

watch(selectedLang, () => { searchKey.value = ''; fetchTranslations() })

// Inline edit
function startEdit(item: TranslationItem) {
  item.editValue = item.value
  item.editing = true
}

function cancelEdit(item: TranslationItem) {
  item.editing = false
}

async function saveKey(item: TranslationItem) {
  item.saving = true
  try {
    await adminApi.updateTranslations({
      lang: selectedLang.value,
      translations: { [item.key]: item.editValue ?? '' },
    })
    item.value = item.editValue ?? ''
    item.editing = false
    ElMessage.success(t('adminLanguages.saveKeySuccess'))
  } finally {
    item.saving = false
  }
}

// Export
async function exportJson() {
  exporting.value = true
  try {
    const { data } = await adminApi.exportTranslations(selectedLang.value)
    const blob = new Blob([data], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `translations_${selectedLang.value}.json`
    a.click()
    URL.revokeObjectURL(url)
  } finally {
    exporting.value = false
  }
}

// Import
function triggerImport() {
  importInputRef.value?.click()
}

async function handleImport(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  importing.value = true
  try {
    const fd = new FormData()
    fd.append('file', file)
    await adminApi.importTranslations(selectedLang.value, fd)
    ElMessage.success(t('adminLanguages.importSuccess'))
    fetchTranslations()
  } finally {
    importing.value = false
    if (importInputRef.value) importInputRef.value.value = ''
  }
}

// ─── Init ─────────────────────────────────────────────────────────────────────
onMounted(() => {
  fetchLanguages()
  fetchTranslations()
})

function formatDate(d?: string) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString()
}
</script>

<template>
  <div class="languages-page">
    <h1 class="page-title">{{ t('adminLanguages.pageTitle') }}</h1>

    <!-- ─── Section 1: Language list ─────────────────────────────────────── -->
    <div class="card">
      <h2 class="section-title">{{ t('adminLanguages.languages') }}</h2>
      <el-table v-loading="langLoading" :data="languages" style="width: 100%">
        <el-table-column :label="t('common.name')" prop="name" min-width="140" />
        <el-table-column label="Code" prop="code" width="90">
          <template #default="{ row }">
            <code class="code-badge">{{ row.code }}</code>
          </template>
        </el-table-column>
        <el-table-column :label="t('common.status')" width="110">
          <template #default="{ row }">
            <el-tag :type="row.is_enabled ? 'success' : 'info'" size="small">
              {{ row.is_enabled ? t('adminLanguages.enable') : t('adminLanguages.disable') }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="t('adminLanguages.isDefault')" width="110">
          <template #default="{ row }">
            <el-tag v-if="row.is_default" type="warning" size="small">{{ t('adminLanguages.isDefault') }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="t('adminResumes.submissionDate')" width="130">
          <template #default="{ row }">{{ formatDate(row.updated_at) }}</template>
        </el-table-column>
        <el-table-column :label="t('common.actions')" width="200" fixed="right">
          <template #default="{ row }">
            <div class="action-row">
              <el-button
                size="small"
                :type="row.is_enabled ? 'warning' : 'success'"
                link
                @click="toggleEnabled(row)"
              >
                {{ row.is_enabled ? t('adminLanguages.disable') : t('adminLanguages.enable') }}
              </el-button>
              <el-button
                v-if="!row.is_default"
                size="small"
                link
                @click="setDefault(row)"
              >
                {{ t('adminLanguages.setDefault') }}
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- ─── Section 2: Translation key editor ────────────────────────────── -->
    <div class="card">
      <div class="section-header">
        <h2 class="section-title">{{ t('adminLanguages.translations') }}</h2>
        <div class="section-actions">
          <el-select v-model="selectedLang" style="width: 150px">
            <el-option label="English (EN)" value="en" />
            <el-option label="中文 (ZH_CN)" value="zh_cn" />
            <el-option label="ภาษาไทย (TH)" value="th" />
          </el-select>
          <el-input
            v-model="searchKey"
            :placeholder="t('adminLanguages.searchKey')"
            clearable
            style="width: 220px"
            prefix-icon="Search"
          />
          <el-button :loading="exporting" @click="exportJson">
            {{ t('adminLanguages.exportJson') }}
          </el-button>
          <el-button :loading="importing" @click="triggerImport">
            {{ t('adminLanguages.importJson') }}
          </el-button>
          <input
            ref="importInputRef"
            type="file"
            accept="application/json"
            style="display:none"
            @change="handleImport"
          />
        </div>
      </div>

      <el-table v-loading="transLoading" :data="filteredTranslations" style="width: 100%">
        <el-table-column :label="t('adminLanguages.key')" min-width="260">
          <template #default="{ row }">
            <code class="key-cell">{{ row.key }}</code>
          </template>
        </el-table-column>
        <el-table-column :label="t('adminLanguages.value')" min-width="300">
          <template #default="{ row }">
            <el-input
              v-if="row.editing"
              v-model="row.editValue"
              size="small"
              style="width: 100%"
              @keydown.enter="saveKey(row)"
              @keydown.escape="cancelEdit(row)"
            />
            <span v-else class="value-cell">{{ row.value }}</span>
          </template>
        </el-table-column>
        <el-table-column :label="t('common.actions')" width="160" fixed="right">
          <template #default="{ row }">
            <div class="action-row">
              <template v-if="row.editing">
                <el-button size="small" type="primary" link :loading="row.saving" @click="saveKey(row)">
                  {{ t('common.save') }}
                </el-button>
                <el-button size="small" link @click="cancelEdit(row)">
                  {{ t('common.cancel') }}
                </el-button>
              </template>
              <el-button v-else size="small" link @click="startEdit(row)">
                {{ t('common.edit') }}
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <div v-if="!transLoading && filteredTranslations.length === 0" class="empty-state">
        <el-empty :description="t('adminLanguages.noKeys')" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.languages-page { padding: 24px; }
.page-title { font-size: 24px; font-weight: 600; color: #003366; margin-bottom: 24px; }

.card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,51,102,0.06);
  padding: 24px;
  margin-bottom: 24px;
}

.section-title { font-size: 18px; font-weight: 600; color: #003366; margin: 0 0 16px; }

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 16px;
}

.section-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}

.action-row { display: flex; gap: 4px; align-items: center; }

.code-badge {
  background: #f0f5ff;
  color: #003366;
  border-radius: 4px;
  padding: 2px 6px;
  font-size: 12px;
  font-family: monospace;
}

.key-cell {
  font-family: monospace;
  font-size: 12px;
  color: #555;
}

.value-cell { font-size: 13px; color: #333; }
.empty-state { padding: 32px; }
</style>
