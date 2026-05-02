# TASK-021: Frontend Layouts, Router & Pinia Stores

**Phase:** 6 — Frontend Foundation  
**Status:** Pending  
**Depends On:** TASK-020  
**Priority:** HIGH  

---

## Objective

Build the complete router setup (all 40+ routes), three Pinia stores (auth, settings, language), four layout components (Default, Auth, Student, Enterprise, Admin), shared components (AppHeader with nav, AppFooter, LanguageSwitcher, NotificationBell), and bootstrap i18n translation keys for all three locales. This is the application skeleton — every future frontend task builds on this.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` — Colors, typography, brand guidelines
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 2 (Auth), Section 3.2 (Language Settings), Section 3.3 (Translations)
3. `DOCUMENTS/DESIGNS/visual-mockups/index.html` — Header/navigation structure
4. `DOCUMENTS/DESIGNS/visual-mockups/student-dashboard.html` — Student sidebar nav
5. `DOCUMENTS/DESIGNS/visual-mockups/admin-dashboard.html` — Admin sidebar nav
6. `DOCUMENTS/DESIGNS/visual-mockups/enterprise-dashboard.html` — Enterprise sidebar nav

---

## Deliverables

### 1. Vue Router — `frontend/src/router/index.js`

All routes with lazy loading. Route guards:
- `requiresAuth` — redirect to `/login` if no token
- `requiresAdmin` — redirect to `/` if not admin
- `requiresStudent` — redirect to `/` if not student
- `requiresEnterprise` — redirect to `/` if not enterprise
- `guestOnly` — redirect to dashboard if already logged in

#### Route Map

```js
// Public routes (DefaultLayout)
/                          → views/public/HomePage.vue
/about                     → views/public/AboutPage.vue
/study-in-china            → views/public/StudyInChinaPage.vue
/talent                    → views/public/TalentPage.vue
/corporate                 → views/public/CorporatePage.vue
/news                      → views/public/NewsListPage.vue
/news/:slug                → views/public/NewsDetailPage.vue
/contact                   → views/public/ContactPage.vue
/seminars                  → views/public/SeminarListPage.vue
/seminars/:id              → views/public/SeminarDetailPage.vue
/seminars/:id/watch        → views/public/SeminarWatchPage.vue

// Auth routes (AuthLayout, guestOnly)
/login                     → views/auth/LoginPage.vue
/register/student          → views/auth/RegisterStudentPage.vue
/register/enterprise       → views/auth/RegisterEnterprisePage.vue
/email/verify/:token       → views/auth/EmailVerifyPage.vue
/password/forgot           → views/auth/ForgotPasswordPage.vue
/password/reset/:token     → views/auth/ResetPasswordPage.vue
/oauth/callback            → views/auth/OAuthCallbackPage.vue

// Student routes (StudentLayout, requiresStudent)
/student/dashboard         → views/student/DashboardPage.vue
/student/profile           → views/student/ProfilePage.vue
/student/resume            → views/student/ResumePage.vue
/student/applications      → views/student/ApplicationsPage.vue
/student/interviews        → views/student/InterviewsPage.vue
/student/interviews/:id    → views/student/InterviewRoomPage.vue
/student/seminars          → views/student/SeminarsPage.vue

// Enterprise routes (EnterpriseLayout, requiresEnterprise)
/enterprise/dashboard      → views/enterprise/DashboardPage.vue
/enterprise/profile        → views/enterprise/ProfilePage.vue
/enterprise/jobs           → views/enterprise/JobsPage.vue
/enterprise/talent         → views/enterprise/TalentPage.vue
/enterprise/interviews     → views/enterprise/InterviewsPage.vue
/enterprise/interviews/:id → views/enterprise/InterviewRoomPage.vue

// Admin routes (AdminLayout, requiresAdmin)
/admin/dashboard           → views/admin/DashboardPage.vue
/admin/users               → views/admin/UsersPage.vue
/admin/resumes             → views/admin/ResumesPage.vue
/admin/interviews          → views/admin/InterviewsPage.vue
/admin/seminars            → views/admin/SeminarsPage.vue
/admin/news                → views/admin/NewsPage.vue
/admin/pages               → views/admin/PagesPage.vue
/admin/settings            → views/admin/SettingsPage.vue
/admin/languages           → views/admin/LanguagesPage.vue

// Catch-all
/:pathMatch(.*)*           → views/NotFoundPage.vue
```

### 2. Pinia Stores

**`frontend/src/stores/auth.js`**
```js
export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || null,
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
  }),
  getters: {
    isLoggedIn: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    isStudent: (state) => state.user?.role === 'student',
    isEnterprise: (state) => state.user?.role === 'enterprise',
  },
  actions: {
    setAuth(token, user) { /* persist to localStorage */ },
    clearAuth() { /* clear localStorage + state */ },
    async fetchProfile() { /* GET /api/auth/me */ },
  }
})
```

**`frontend/src/stores/settings.js`**
```js
// Fetches site settings from GET /api/settings (public)
// Stores: site_name, site_logo, site_favicon, contact_email, social links
export const useSettingsStore = defineStore('settings', {
  state: () => ({ settings: {}, loaded: false }),
  actions: {
    async fetchSettings() { /* GET /api/settings */ }
  }
})
```

**`frontend/src/stores/language.js`**
```js
// Manages active language: en | zh_cn | th
// Syncs with Vue i18n and Element Plus locale
// Fetches translations from GET /api/translations?lang=xxx
export const useLanguageStore = defineStore('language', {
  state: () => ({
    current: localStorage.getItem('lang') || 'en',
    available: [],
    translations: {},
  }),
  actions: {
    async switchLanguage(lang) { /* update i18n, Element Plus, fetch translations */ },
    async fetchAvailableLanguages() { /* GET /api/languages */ },
    async loadTranslations(lang) { /* GET /api/translations?lang=xxx */ },
  }
})
```

### 3. Layout Components

**`frontend/src/layouts/DefaultLayout.vue`**
- Full-width public layout
- Slots: `<AppHeader />`, `<router-view />`, `<AppFooter />`
- Responsive navigation from `visual-mockups/index.html`

**`frontend/src/layouts/AuthLayout.vue`**
- Centered card layout for login/register
- No header/footer — just logo + form card

**`frontend/src/layouts/StudentLayout.vue`**
- Left sidebar (collapsible on mobile) + main content area
- Sidebar nav items from `visual-mockups/student-dashboard.html`
- Show user avatar + name in sidebar header

**`frontend/src/layouts/EnterpriseLayout.vue`**
- Same structure as StudentLayout
- Enterprise-specific nav items from `visual-mockups/enterprise-dashboard.html`

**`frontend/src/layouts/AdminLayout.vue`**
- Same structure, dark sidebar color (brand primary)
- Nav items from `visual-mockups/admin-dashboard.html`

### 4. Shared Components

**`frontend/src/components/common/AppHeader.vue`**
- Logo (from `settingsStore.settings.site_logo`)
- Navigation links (Home, About, Study in China, Talent, Corporate, News, Seminars, Contact)
- Right side: LanguageSwitcher + Login/Register buttons OR user avatar dropdown
- Mobile: hamburger menu
- Reference: `visual-mockups/index.html` header section

**`frontend/src/components/common/AppFooter.vue`**
- Brand info, links, social icons, copyright
- Reference: `visual-mockups/index.html` footer section

**`frontend/src/components/common/LanguageSwitcher.vue`**
- `<el-dropdown>` with EN / 中文 / ภาษาไทย
- On select: calls `languageStore.switchLanguage(lang)`
- Shows current language flag/abbreviation

**`frontend/src/components/common/SidebarNav.vue`** (shared between portal layouts)
- Props: `navItems: [{icon, label, route}]`, `collapsed: bool`
- Highlight active route

### 5. i18n Translation Keys (Skeleton)

`frontend/src/i18n/locales/en.json` — add all UI string keys:
```json
{
  "nav": {
    "home": "Home",
    "about": "About Us",
    "studyInChina": "Study in China",
    "talent": "Talent Pool",
    "corporate": "Corporate Cooperation",
    "news": "News",
    "seminars": "Seminar Center",
    "contact": "Contact Us"
  },
  "auth": {
    "login": "Login",
    "register": "Register",
    "logout": "Logout",
    "email": "Email",
    "password": "Password",
    "forgotPassword": "Forgot Password?"
  },
  "common": {
    "loading": "Loading...",
    "save": "Save",
    "cancel": "Cancel",
    "delete": "Delete",
    "confirm": "Confirm",
    "success": "Success",
    "error": "Error",
    "search": "Search",
    "noData": "No data available"
  },
  "lang": {
    "en": "English",
    "zh_cn": "中文",
    "th": "ภาษาไทย"
  }
}
```

Mirror identical key structure in `zh_cn.json` and `th.json` with translated values.

---

## App.vue

```vue
<template>
  <component :is="layout">
    <router-view />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import DefaultLayout from './layouts/DefaultLayout.vue'
import AuthLayout from './layouts/AuthLayout.vue'
import StudentLayout from './layouts/StudentLayout.vue'
import EnterpriseLayout from './layouts/EnterpriseLayout.vue'
import AdminLayout from './layouts/AdminLayout.vue'

const route = useRoute()
const layout = computed(() => {
  const layoutMap = {
    auth: AuthLayout,
    student: StudentLayout,
    enterprise: EnterpriseLayout,
    admin: AdminLayout,
  }
  return layoutMap[route.meta.layout] || DefaultLayout
})
</script>
```

---

## App Initialization

In `App.vue` or a composable `useAppInit()`:
1. `settingsStore.fetchSettings()` — load site config
2. `languageStore.fetchAvailableLanguages()` — load enabled languages
3. `languageStore.loadTranslations(current)` — load i18n strings
4. If `authStore.isLoggedIn`, call `authStore.fetchProfile()` — verify token still valid

---

## Acceptance Criteria

- [ ] All 40+ routes defined with correct path, component, and meta (layout, auth requirements)
- [ ] Navigation guards redirect unauthenticated users from protected routes to `/login`
- [ ] Navigation guards redirect logged-in users from `/login` to their dashboard
- [ ] `useAuthStore` persists token/user to localStorage, getters return correct role booleans
- [ ] `useSettingsStore.fetchSettings()` populates site_name, site_logo in header
- [ ] `useLanguageStore.switchLanguage('zh_cn')` updates Vue i18n and Element Plus locale simultaneously
- [ ] DefaultLayout renders AppHeader + AppFooter correctly
- [ ] AuthLayout shows only centered card (no header/footer)
- [ ] AdminLayout has collapsible sidebar with correct nav items
- [ ] StudentLayout has collapsible sidebar with correct nav items
- [ ] LanguageSwitcher dropdown shows 3 options and switches language
- [ ] AppHeader shows Login/Register when logged out, avatar dropdown when logged in
- [ ] Placeholder pages (empty `<div>`) exist for all routes to prevent 404 errors during development
- [ ] App initialization runs on mount without crashing

---

## Notes

- Create placeholder `.vue` files for all views (just `<template><div /></template>`) so router doesn't break during development — actual content added in later tasks
- The `meta.layout` pattern in App.vue allows layouts to be set per-route in router config
- Element Plus locale switching: use `ElConfigProvider` wrapping the app with the active locale object
- Store token in `localStorage` (not `sessionStorage`) so it persists across browser refresh
