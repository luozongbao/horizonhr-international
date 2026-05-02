# TASK-042: SEO, i18n Completeness & Performance Optimization

**Phase:** 13 — Finalization  
**Status:** Pending  
**Depends On:** TASK-023, TASK-024, TASK-025, TASK-026, TASK-027  
**Priority:** MEDIUM  

---

## Objective

Finalize the frontend for production quality: complete all i18n translation keys across all three locales, add SEO meta tags (TDK + Open Graph) to public pages, audit and fix any missing translations, implement route-level code splitting, optimize images and bundle size.

---

## Reference Documents

1. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV (all user-facing features), Section IX (multi-language requirements)
2. `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` — Site name, brand description for meta tags

---

## Deliverables

### 1. i18n Audit & Completion

Run through every Vue component and verify all user-visible strings use `$t()`. Fix any hardcoded English strings.

Ensure all keys exist in all three locale files:
- `frontend/src/i18n/locales/en.json` — Master reference (English)
- `frontend/src/i18n/locales/zh_cn.json` — Chinese Simplified
- `frontend/src/i18n/locales/th.json` — Thai

**Audit script** (run with `node`):
```js
// Check that all keys in en.json exist in zh_cn.json and th.json
// Print missing keys
```

Missing keys should be marked with `[TODO: translate]` placeholder in zh_cn and th until translation is provided.

### 2. SEO: Dynamic Meta Tags

Install `@vueuse/head` or use `@unhead/vue` for managing `<head>` tags:
```bash
npm install @unhead/vue
```

Create `usePageMeta` composable:
**`frontend/src/composables/usePageMeta.js`**
```js
export function usePageMeta({ title, description, image }) {
  useHead({
    title: () => `${title} | ${settingsStore.settings.site_name}`,
    meta: [
      { name: 'description', content: description },
      { property: 'og:title', content: title },
      { property: 'og:description', content: description },
      { property: 'og:image', content: image || settingsStore.settings.site_logo },
      { property: 'og:type', content: 'website' },
      { name: 'twitter:card', content: 'summary_large_image' },
    ]
  })
}
```

Apply `usePageMeta()` in these pages:
| Page | Title | Description |
|------|-------|-------------|
| HomePage | Site name | From CMS home page meta |
| AboutPage | About Us | From CMS about page meta |
| StudyInChinaPage | Study in China | From CMS |
| NewsListPage | News & Updates | From CMS |
| NewsDetailPage | Post title | Post excerpt |
| SeminarListPage | Seminar Center | General description |
| SeminarDetailPage | Seminar title | Seminar description |
| ContactPage | Contact Us | From CMS |

### 3. Route-Level Code Splitting

All routes are already lazy-loaded via `() => import(...)` pattern. Verify this is implemented for all 40+ routes in `router/index.js`. 

Group related chunks with Vite magic comments:
```js
{ path: '/admin/*', component: () => import(/* webpackChunkName: "admin" */ '...') }
```

### 4. Image Optimization

- Add `loading="lazy"` to all `<img>` tags that are below the fold
- Add explicit `width` and `height` attributes to prevent layout shift (CLS)
- Hero images and above-fold images: use `loading="eager"` (default)
- Compress static assets in `frontend/src/assets/images/` before committing

### 5. Bundle Analysis

Run Vite bundle analyzer:
```bash
npm install --save-dev rollup-plugin-visualizer
```

Add to `vite.config.js`:
```js
import { visualizer } from 'rollup-plugin-visualizer'
plugins: [
  // ... existing plugins
  visualizer({ open: true, filename: 'bundle-stats.html' })
]
```

Run `npm run build` and review `bundle-stats.html`.

Target: main bundle < 200KB gzipped.

Common optimizations if bundle is large:
- Element Plus: ensure only used components imported (via unplugin-vue-components auto-import)
- Quill: lazy-import only in admin CMS routes
- hls.js: lazy-import only in seminar watch page
- chart.js: lazy-import only in admin dashboard

### 6. Web Vitals Targets

| Metric | Target |
|--------|--------|
| LCP (Largest Contentful Paint) | < 2.5s |
| FID (First Input Delay) | < 100ms |
| CLS (Cumulative Layout Shift) | < 0.1 |
| TTFB (Time to First Byte) | < 600ms |

Test using Chrome DevTools Lighthouse after deployment.

---

## Acceptance Criteria

- [ ] All user-visible text in all Vue components uses `$t()` (no hardcoded strings)
- [ ] All i18n keys present in en.json, zh_cn.json, th.json
- [ ] Missing translations have `[TODO: translate]` placeholder
- [ ] `usePageMeta()` applied to all public pages
- [ ] `<title>` tag changes correctly on route navigation
- [ ] Open Graph tags set on news article pages
- [ ] All routes use lazy loading (`() => import(...)`)
- [ ] Bundle size analyzed; major optimizations applied if > 500KB
- [ ] All below-fold images have `loading="lazy"`
- [ ] Lighthouse score > 80 on Performance for home page

---

## Notes

- i18n Thai translations: if no professional translator available, use machine translation as placeholder (clearly mark as `[AUTO]` in value)
- Lighthouse test: run on production build served by nginx, not dev server
- `@unhead/vue` is the recommended replacement for `vue-meta` in Vue 3
- Quill and hls.js are the largest dependencies — dynamic import them to reduce initial bundle
