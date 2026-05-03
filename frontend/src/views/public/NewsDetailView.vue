<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { publicApi } from '@/api/public'
import { useSanitize } from '@/composables/useSanitize'

const { t } = useI18n()
const route = useRoute()
const { sanitize } = useSanitize()

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

/* ─── State ──────────────────────────────────── */
const post = ref<Post | null>(null)
const related = ref<Post[]>([])
const loading = ref(true)
const error = ref(false)

/* ─── Fetch ──────────────────────────────────── */
async function fetchPost(slug: string) {
  loading.value = true
  error.value = false
  post.value = null
  related.value = []

  try {
    const res = await publicApi.getPost(slug)
    post.value = res.data?.data ?? null

    // Fetch related posts from same category
    if (post.value?.category) {
      try {
        const rel = await publicApi.getPosts({
          category: post.value.category,
          per_page: 3,
        })
        related.value = (rel.data?.data ?? []).filter((p: Post) => p.slug !== slug).slice(0, 3)
      } catch {
        // related posts are optional
      }
    }
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}

function formatDate(dateStr?: string): string {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

function truncate(text: string | undefined, max = 150): string {
  if (!text) return ''
  if (text.length <= max) return text
  return text.slice(0, max).trimEnd() + '…'
}

// Re-fetch when slug changes (navigating between articles)
watch(() => route.params.slug, (slug) => {
  if (slug) fetchPost(slug as string)
})

onMounted(() => {
  const slug = route.params.slug as string
  if (slug) fetchPost(slug)
})
</script>

<template>
  <div class="news-detail-page">

    <!-- Loading -->
    <section v-if="loading" class="section">
      <div class="container">
        <el-skeleton :rows="10" animated />
      </div>
    </section>

    <!-- Error -->
    <section v-else-if="error || !post" class="section">
      <div class="container">
        <el-empty :description="t('news.noResults')" />
        <div class="back-link-wrap">
          <router-link to="/news" class="back-link">← {{ t('news.backToNews') }}</router-link>
        </div>
      </div>
    </section>

    <!-- Article -->
    <template v-else>

      <!-- Hero / Thumbnail -->
      <section class="article-hero" :style="post.thumbnail_url ? `background-image:url('${post.thumbnail_url}')` : ''">
        <div class="hero-overlay">
          <div class="container">
            <nav class="breadcrumb">
              <router-link to="/">{{ t('nav.home') }}</router-link>
              <span class="sep">/</span>
              <router-link to="/news">{{ t('news.pageTitle') }}</router-link>
              <span class="sep">/</span>
              <span class="breadcrumb-title">{{ post.title }}</span>
            </nav>
            <span v-if="post.category" class="article-category">{{ post.category }}</span>
            <h1 class="article-title">{{ post.title }}</h1>
            <div class="article-meta">
              <span v-if="post.published_at">{{ t('news.publishedOn') }} {{ formatDate(post.published_at) }}</span>
              <span v-if="post.author" class="meta-sep">·</span>
              <span v-if="post.author">{{ post.author }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Body -->
      <section class="section">
        <div class="container article-layout">
          <article class="article-body">
            <!-- Excerpt -->
            <p v-if="post.excerpt" class="article-excerpt">{{ post.excerpt }}</p>
            <!-- Content (sanitized rich HTML) -->
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div
              v-if="post.content"
              class="article-content rich-text"
              v-html="sanitize(post.content)"
            />
          </article>

          <!-- Back link -->
          <div class="back-link-wrap">
            <router-link to="/news" class="back-link">← {{ t('news.backToNews') }}</router-link>
          </div>
        </div>
      </section>

      <!-- Related Posts -->
      <section v-if="related.length > 0" class="section section-bg">
        <div class="container">
          <h2 class="section-heading">{{ t('news.relatedPosts') }}</h2>
          <div class="related-grid">
            <article v-for="r in related" :key="r.id" class="related-card">
              <router-link :to="'/news/' + r.slug" class="related-thumb-link">
                <div class="related-thumb-wrap">
                  <img
                    v-if="r.thumbnail_url"
                    :src="r.thumbnail_url"
                    :alt="r.title"
                    class="related-thumb"
                  />
                  <div v-else class="related-thumb-placeholder">&#128240;</div>
                </div>
              </router-link>
              <div class="related-body">
                <p v-if="r.published_at" class="related-date">{{ formatDate(r.published_at) }}</p>
                <h3 class="related-title">
                  <router-link :to="'/news/' + r.slug">{{ r.title }}</router-link>
                </h3>
                <p class="related-excerpt">{{ truncate(r.excerpt || r.content) }}</p>
                <router-link :to="'/news/' + r.slug" class="read-more-link">
                  {{ t('news.readMore') }} →
                </router-link>
              </div>
            </article>
          </div>
        </div>
      </section>

    </template>

  </div>
</template>

<style scoped>
.news-detail-page {
  --c-primary: #003366;
  --c-secondary: #E6F0FF;
  --c-accent: #0066CC;
  --c-text: #1A1A2E;
  --c-muted: #6C757D;
  --c-border: #DEE2E6;
}

.container { max-width: 900px; margin: 0 auto; padding: 0 48px; }
.section { padding: 48px 0; }
.section-bg { background: #f8faff; }

/* Hero */
.article-hero {
  min-height: 360px;
  background: linear-gradient(135deg, var(--c-primary) 0%, #004080 100%);
  background-size: cover;
  background-position: center;
  position: relative;
}
.hero-overlay {
  min-height: 360px;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: flex-end;
  padding-bottom: 48px;
  padding-top: 48px;
}
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  opacity: 0.8;
  color: #fff;
  margin-bottom: 16px;
  flex-wrap: wrap;
}
.breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.breadcrumb a:hover { color: #fff; }
.breadcrumb-title { opacity: 0.7; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 300px; }
.sep { opacity: 0.5; }
.article-category {
  display: inline-block;
  background: var(--c-accent);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 12px;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 12px;
}
.article-title { font-size: 36px; font-weight: 700; color: #fff; line-height: 1.3; margin-bottom: 12px; }
.article-meta { font-size: 14px; color: rgba(255,255,255,0.75); }
.meta-sep { margin: 0 6px; }

/* Body */
.article-layout { max-width: 760px; }
.article-excerpt {
  font-size: 18px;
  color: var(--c-muted);
  font-style: italic;
  line-height: 1.6;
  border-left: 4px solid var(--c-accent);
  padding-left: 16px;
  margin-bottom: 32px;
}
.article-content.rich-text :deep(h1),
.article-content.rich-text :deep(h2),
.article-content.rich-text :deep(h3) {
  color: var(--c-primary);
  margin: 28px 0 12px;
}
.article-content.rich-text :deep(p) {
  line-height: 1.8;
  margin-bottom: 16px;
  color: var(--c-text);
}
.article-content.rich-text :deep(img) {
  max-width: 100%;
  border-radius: 8px;
  margin: 16px 0;
}
.article-content.rich-text :deep(a) { color: var(--c-accent); }
.article-content.rich-text :deep(ul),
.article-content.rich-text :deep(ol) { padding-left: 24px; margin-bottom: 16px; }
.article-content.rich-text :deep(li) { margin-bottom: 6px; line-height: 1.7; }
.article-content.rich-text :deep(blockquote) {
  border-left: 4px solid var(--c-secondary);
  padding: 12px 20px;
  background: #f8faff;
  margin: 20px 0;
  font-style: italic;
  color: var(--c-muted);
}

/* Back link */
.back-link-wrap { margin-top: 40px; }
.back-link {
  font-size: 14px;
  font-weight: 600;
  color: var(--c-accent);
  text-decoration: none;
}
.back-link:hover { text-decoration: underline; }

/* Related */
.section-heading { font-size: 24px; font-weight: 700; color: var(--c-primary); margin-bottom: 28px; }
.related-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.related-card {
  background: #fff;
  border: 1px solid var(--c-border);
  border-radius: 8px;
  overflow: hidden;
}
.related-thumb-link { display: block; }
.related-thumb { width: 100%; height: 150px; object-fit: cover; display: block; }
.related-thumb-placeholder {
  width: 100%;
  height: 150px;
  background: var(--c-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 36px;
}
.related-body { padding: 16px; }
.related-date { font-size: 11px; color: var(--c-muted); margin-bottom: 6px; }
.related-title { font-size: 15px; font-weight: 600; margin-bottom: 8px; line-height: 1.4; }
.related-title a { color: var(--c-text); text-decoration: none; }
.related-title a:hover { color: var(--c-accent); }
.related-excerpt { font-size: 13px; color: var(--c-muted); line-height: 1.5; margin-bottom: 10px; }
.read-more-link { font-size: 13px; font-weight: 600; color: var(--c-accent); text-decoration: none; }
.read-more-link:hover { text-decoration: underline; }

/* Responsive */
@media (max-width: 900px) {
  .container { padding: 0 24px; }
  .article-title { font-size: 26px; }
  .related-grid { grid-template-columns: 1fr; }
}
</style>

