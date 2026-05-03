<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'

const { t } = useI18n()

/* ─── Types ──────────────────────────────────── */
interface Post {
  id: number
  slug: string
  title: string
  excerpt?: string
  content?: string
  thumbnail_url?: string
  category?: string
  published_at?: string
  author?: string
}

interface PaginationMeta {
  current_page: number
  last_page: number
  total: number
  per_page: number
}

/* ─── State ──────────────────────────────────── */
const posts = ref<Post[]>([])
const loading = ref(false)
const meta = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0, per_page: 9 })
const searchQuery = ref('')
const categoryFilter = ref('')
const currentPage = ref(1)
const PER_PAGE = 9

const categories = ref<string[]>([])

/* ─── Fetch ──────────────────────────────────── */
async function fetchPosts() {
  loading.value = true
  try {
    const res = await publicApi.getPosts({
      per_page: PER_PAGE,
      page: currentPage.value,
      category: categoryFilter.value || undefined,
    })
    const data = res.data
    posts.value = data?.data ?? []
    meta.value = data?.meta ?? { current_page: 1, last_page: 1, total: 0, per_page: PER_PAGE }

    // Collect categories for filter tabs
    const cats = new Set<string>()
    posts.value.forEach((p) => {
      if (p.category) cats.add(p.category)
    })
    if (cats.size > 0) categories.value = Array.from(cats)
  } catch {
    posts.value = []
  } finally {
    loading.value = false
  }
}

function resetAndFetch() {
  currentPage.value = 1
  fetchPosts()
}

watch([categoryFilter], resetAndFetch)

function handlePageChange(page: number) {
  currentPage.value = page
  fetchPosts()
  document.getElementById('news-grid')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function formatDate(dateStr?: string): string {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

function truncate(text: string | undefined, max = 150): string {
  if (!text) return ''
  if (text.length <= max) return text
  return text.slice(0, max).trimEnd() + '…'
}

onMounted(fetchPosts)
</script>

<template>
  <div class="news-page">

    <!-- Hero -->
    <section class="page-hero">
      <div class="container">
        <h1 class="hero-title">{{ t('news.pageTitle') }}</h1>
        <nav class="breadcrumb">
          <router-link to="/">{{ t('nav.home') }}</router-link>
          <span class="sep">/</span>
          <span>{{ t('news.pageTitle') }}</span>
        </nav>
      </div>
    </section>

    <!-- Filters -->
    <section class="filter-section">
      <div class="container">
        <div class="filter-bar">
          <el-input
            v-model="searchQuery"
            :placeholder="t('news.searchPlaceholder')"
            clearable
            class="filter-search"
            prefix-icon="Search"
            @change="resetAndFetch"
          />
          <div class="category-tabs">
            <button
              class="cat-tab"
              :class="{ active: categoryFilter === '' }"
              @click="categoryFilter = ''"
            >
              {{ t('news.allCategories') }}
            </button>
            <button
              v-for="cat in categories"
              :key="cat"
              class="cat-tab"
              :class="{ active: categoryFilter === cat }"
              @click="categoryFilter = cat"
            >
              {{ cat }}
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- News Grid -->
    <section id="news-grid" class="section section-bg">
      <div class="container">
        <template v-if="loading">
          <div class="news-grid">
            <el-skeleton v-for="i in PER_PAGE" :key="i" style="height:300px" animated />
          </div>
        </template>
        <template v-else-if="posts.length === 0">
          <el-empty :description="t('news.noResults')" />
        </template>
        <template v-else>
          <div class="news-grid">
            <article v-for="post in posts" :key="post.id" class="news-card">
              <router-link :to="'/news/' + post.slug" class="card-thumb-link">
                <div class="card-thumb-wrap">
                  <img
                    v-if="post.thumbnail_url"
                    :src="post.thumbnail_url"
                    :alt="post.title"
                    class="card-thumb"
                  />
                  <div v-else class="card-thumb-placeholder">
                    <span>&#128240;</span>
                  </div>
                  <span v-if="post.category" class="card-category">{{ post.category }}</span>
                </div>
              </router-link>
              <div class="card-body">
                <p v-if="post.published_at" class="card-date">
                  {{ t('news.publishedOn') }} {{ formatDate(post.published_at) }}
                </p>
                <h3 class="card-title">
                  <router-link :to="'/news/' + post.slug">{{ post.title }}</router-link>
                </h3>
                <p v-if="post.excerpt || post.content" class="card-excerpt">
                  {{ truncate(post.excerpt || post.content) }}
                </p>
              </div>
              <div class="card-footer">
                <router-link :to="'/news/' + post.slug" class="read-more-link">
                  {{ t('news.readMore') }} →
                </router-link>
              </div>
            </article>
          </div>

          <!-- Pagination -->
          <div v-if="meta.last_page > 1" class="pagination-wrap">
            <el-pagination
              :current-page="currentPage"
              :page-size="PER_PAGE"
              :total="meta.total"
              layout="prev, pager, next"
              @current-change="handlePageChange"
            />
          </div>
        </template>
      </div>
    </section>

  </div>
</template>

<style scoped>
.news-page {
  --c-primary: #003366;
  --c-secondary: #E6F0FF;
  --c-accent: #0066CC;
  --c-text: #1A1A2E;
  --c-muted: #6C757D;
  --c-border: #DEE2E6;
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 48px; }
.section { padding: 48px 0; }
.section-bg { background: #f8faff; }

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

/* Filters */
.filter-section {
  background: #fff;
  border-bottom: 1px solid var(--c-border);
  padding: 20px 0;
}
.filter-bar { display: flex; gap: 16px; flex-wrap: wrap; align-items: center; }
.filter-search { flex: 0 0 280px; }
.category-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
.cat-tab {
  padding: 6px 16px;
  border: 1px solid var(--c-border);
  border-radius: 20px;
  background: #fff;
  color: var(--c-muted);
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}
.cat-tab:hover { border-color: var(--c-accent); color: var(--c-accent); }
.cat-tab.active {
  background: var(--c-primary);
  border-color: var(--c-primary);
  color: #fff;
}

/* Grid */
.news-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 28px;
  margin-bottom: 40px;
}

.news-card {
  background: #fff;
  border: 1px solid var(--c-border);
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.2s, transform 0.2s;
}
.news-card:hover { box-shadow: 0 8px 24px rgba(0,51,102,0.12); transform: translateY(-2px); }

.card-thumb-link { display: block; }
.card-thumb-wrap { position: relative; }
.card-thumb {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
}
.card-thumb-placeholder {
  width: 100%;
  height: 200px;
  background: var(--c-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
}
.card-category {
  position: absolute;
  top: 12px;
  left: 12px;
  background: var(--c-primary);
  color: #fff;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.card-body { padding: 20px; flex: 1; }
.card-date { font-size: 12px; color: var(--c-muted); margin-bottom: 8px; }
.card-title { font-size: 17px; font-weight: 600; color: var(--c-text); margin-bottom: 10px; line-height: 1.4; }
.card-title a { color: inherit; text-decoration: none; }
.card-title a:hover { color: var(--c-accent); }
.card-excerpt { font-size: 14px; color: var(--c-muted); line-height: 1.6; }

.card-footer { padding: 0 20px 20px; }
.read-more-link {
  font-size: 14px;
  font-weight: 600;
  color: var(--c-accent);
  text-decoration: none;
}
.read-more-link:hover { text-decoration: underline; }

/* Pagination */
.pagination-wrap {
  display: flex;
  justify-content: center;
  padding-top: 16px;
}

/* Responsive */
@media (max-width: 900px) {
  .news-grid { grid-template-columns: repeat(2, 1fr); }
  .container { padding: 0 24px; }
  .hero-title { font-size: 28px; }
}
@media (max-width: 600px) {
  .news-grid { grid-template-columns: 1fr; }
  .filter-search { flex: 1 1 100%; }
}
</style>

