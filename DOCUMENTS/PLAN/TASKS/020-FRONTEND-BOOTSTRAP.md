# TASK-020: Frontend Project Bootstrap

**Phase:** 6 — Frontend Foundation  
**Status:** Pending  
**Depends On:** TASK-002  
**Priority:** HIGH  

---

## Objective

Initialize the Vue 3 frontend application with all foundational tooling configured: Vite 5 build system, Tailwind CSS, Element Plus with auto-import, Vue i18n v9, Axios instance, and the full folder structure. At the end of this task, `npm run dev` should serve a blank page without errors and all tooling integrations should be verified working.

---

## Reference Documents

1. `DOCUMENTS/SOLUTION.md` — Section 7.1 (Frontend Tech Stack)
2. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IX.A (Full tech stack table)
3. `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` — Brand colors, typography

---

## Tech Stack Versions

| Package | Version |
|---------|---------|
| Vue | 3.x (Composition API, `<script setup>`) |
| Vite | 5.x |
| Vue Router | 4.x |
| Pinia | 2.x |
| Axios | 1.x |
| Element Plus | 2.x |
| @element-plus/icons-vue | 2.x |
| Vue i18n | 9.x |
| Tailwind CSS | 3.x |
| unplugin-vue-components | latest |
| unplugin-auto-import | latest |

---

## Deliverables

### 1. Vue Project Initialization

Location: `frontend/` inside the project root (created in TASK-002).

`package.json` scripts:
```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview",
    "lint": "eslint . --ext .vue,.js,.jsx,.cjs,.mjs"
  }
}
```

### 2. Vite Configuration
- `frontend/vite.config.js`:
  - `@vitejs/plugin-vue`
  - `unplugin-auto-import/vite` — auto-import Vue Composition API, Vue Router, Pinia
  - `unplugin-vue-components/vite` with ElementPlusResolver — auto-import Element Plus components
  - Proxy: `/api` → `http://app:8000` (PHP backend in Docker)
  - Build output: `../public/build/` (served by Laravel)
  - Alias: `@` → `./src`

### 3. Tailwind CSS Setup
- `frontend/tailwind.config.js`:
  ```js
  module.exports = {
    content: ['./index.html', './src/**/*.{vue,js}'],
    theme: {
      extend: {
        colors: {
          brand: {
            primary: '#003366',
            secondary: '#E6F0FF',
          }
        }
      }
    }
  }
  ```
- `frontend/src/assets/main.css` — import Tailwind directives:
  ```css
  @tailwind base;
  @tailwind components;
  @tailwind utilities;
  ```

### 4. Element Plus Config
- Element Plus auto-imported via `unplugin-vue-components` (no manual imports needed)
- Element Plus CSS imported in `main.js`
- Default locale: `en` (switchable at runtime — see TASK-021)

### 5. Vue i18n Setup
- `frontend/src/i18n/index.js` — create i18n instance
- Locale files (empty/skeleton):
  - `frontend/src/i18n/locales/en.json`
  - `frontend/src/i18n/locales/zh_cn.json`
  - `frontend/src/i18n/locales/th.json`
- Default language: `en` (read from localStorage on init if available)
- Fallback locale: `en`

### 6. Axios Instance
- `frontend/src/api/axios.js`:
  ```js
  const api = axios.create({
    baseURL: '/api',
    timeout: 30000,
    headers: { 'Content-Type': 'application/json' }
  });
  
  // Request interceptor: attach Bearer token from Pinia auth store
  api.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
  });
  
  // Response interceptor: handle 401 (redirect to login), 422 (validation errors)
  api.interceptors.response.use(
    response => response,
    error => {
      if (error.response?.status === 401) {
        // Clear token, redirect to login
      }
      return Promise.reject(error);
    }
  );
  ```

### 7. Folder Structure
```
frontend/
  src/
    api/
      axios.js          # Axios instance
      auth.js           # Auth API calls
      (other modules added in later tasks)
    assets/
      main.css          # Tailwind imports
      images/           # Static images
    components/
      common/           # Shared: AppHeader, AppFooter, LanguageSwitcher, etc. (TASK-021)
      forms/            # Reusable form components
    i18n/
      index.js
      locales/
        en.json
        zh_cn.json
        th.json
    layouts/
      DefaultLayout.vue   (TASK-021)
      AuthLayout.vue      (TASK-021)
      AdminLayout.vue     (TASK-021)
    router/
      index.js          (TASK-021)
    stores/
      auth.js           (TASK-021)
      settings.js       (TASK-021)
      language.js       (TASK-021)
    views/
      public/
      student/
      enterprise/
      admin/
      auth/
    App.vue
    main.js
  index.html
  vite.config.js
  tailwind.config.js
  postcss.config.js
  package.json
  .eslintrc.cjs
```

### 8. main.js
```js
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import { i18n } from './i18n'
import router from './router'
import './assets/main.css'
import App from './App.vue'

const app = createApp(App)
app.use(createPinia())
app.use(ElementPlus)
app.use(i18n)
app.use(router)
app.mount('#app')
```

---

## Acceptance Criteria

- [ ] `npm install` completes without errors
- [ ] `npm run dev` starts Vite dev server and serves the app at `http://localhost:5173`
- [ ] Element Plus `<el-button>` renders correctly with brand colors
- [ ] Tailwind utility classes (`bg-brand-primary`, `text-white`) apply correctly
- [ ] Vue i18n `$t('hello')` works (no console errors)
- [ ] Axios instance exported from `src/api/axios.js` and usable in any Vue component
- [ ] `npm run build` produces output in `../public/build/` without errors
- [ ] `/api` proxy routes requests to backend
- [ ] Auto-import works: can use `ref`, `computed` without explicit import in `<script setup>`
- [ ] Element Plus icons importable: `import { Search } from '@element-plus/icons-vue'`
- [ ] Folder structure matches specification above

---

## Notes

- Use `<script setup>` syntax in all Vue SFCs throughout the project
- Tailwind and Element Plus have some CSS conflicts — use Tailwind's `important: true` or CSS layer ordering to resolve
- The Docker service for frontend (from TASK-002) mounts `frontend/` and runs `npm run dev` on port 5173
- Nginx (TASK-002) proxies to Vite dev server for development; uses built files for production
