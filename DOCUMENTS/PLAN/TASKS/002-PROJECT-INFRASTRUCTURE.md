# TASK-002: Project Structure & Docker Infrastructure

**Phase:** 1 — Infrastructure  
**Status:** Pending  
**Depends On:** TASK-001  
**Priority:** HIGH  

---

## Objective

Initialize the full project repository structure, configure Docker Compose for local development, scaffold the Laravel 10 backend and Vue 3 + Vite frontend, and establish base configuration files. After this task, `docker compose up` should bring up a running environment with both backend API and frontend accessible.

---

## Reference Documents

1. `DOCUMENTS/REQUIREMENTS-EN.md` — Section VI (Technical Standards)
2. `DOCUMENTS/SOLUTION.md` — Section 3 (Architecture), Section 7 (Tech Stack)
3. `DOCUMENTS/PLAN/PLAN.md` — Repository structure

---

## Tech Stack Versions

| Component | Version |
|-----------|---------|
| PHP | 8.2 |
| Laravel | 10.x |
| Node.js | 20 LTS |
| MariaDB | 10.11 |
| Redis | 7.x |
| nginx | 1.25 |
| Vue | 3.x |
| Vite | 5.x |

---

## Deliverables

### Root Level
- `docker-compose.yml` — Development environment
- `docker-compose.prod.yml` — Production overrides
- `.env.example` — Root environment template
- `README.md` — Project setup instructions

### Docker Services (`docker/`)
- `docker/nginx/nginx.conf` — nginx reverse proxy config
  - `/` → frontend (port 5173 in dev, static in prod)
  - `/api` → backend Laravel (PHP-FPM port 9000)
- `docker/php/Dockerfile` — PHP 8.2 + extensions
- `docker/php/php.ini` — PHP config (upload_max_filesize=25M)

### Backend (`backend/`)
- Fresh Laravel 10 installation
- `backend/.env.example` with all required keys (see below)
- `backend/routes/api.php` — Skeleton with grouped route structure
- `backend/app/Http/Controllers/` — Empty directory structure:
  ```
  Controllers/
  ├── Auth/
  ├── Admin/
  ├── Student/
  ├── Enterprise/
  └── Public/
  ```
- `backend/app/Services/` — Empty service directory
- `backend/app/Jobs/` — Empty jobs directory
- Composer packages to install:
  - `laravel/sanctum` — API token auth
  - `intervention/image` — Image processing for avatar/logo uploads
  - `league/flysystem-aws-s3-v3` — OSS/S3-compatible storage

### Frontend (`frontend/`)
- Fresh Vite + Vue 3 project (`npm create vue@latest`)
- npm packages to install:
  - `vue-router@4`
  - `pinia`
  - `axios`
  - `element-plus`
  - `@element-plus/icons-vue`
  - `vue-i18n@9`
  - `tailwindcss` + `postcss` + `autoprefixer`
  - `@vueuse/core`
- Configuration files:
  - `frontend/vite.config.ts` — Proxy `/api` to `http://backend:8000`
  - `frontend/tailwind.config.js` — Custom colors + Element Plus prefix config
  - `frontend/src/main.ts` — Bootstrap: Vue app + router + pinia + i18n + Element Plus
  - `frontend/src/router/index.ts` — Skeleton route definitions (placeholders)
  - `frontend/src/stores/auth.ts` — Skeleton auth store
  - `frontend/src/stores/settings.ts` — Skeleton settings store
  - `frontend/src/i18n/index.ts` — i18n setup with en/zh_cn/th
  - `frontend/src/i18n/locales/en.json` — Skeleton English translations
  - `frontend/src/i18n/locales/zh_cn.json` — Skeleton Chinese translations
  - `frontend/src/i18n/locales/th.json` — Skeleton Thai translations
  - `frontend/src/api/index.ts` — Axios instance with base URL + interceptors

---

## Backend `.env.example` Keys

```env
APP_NAME=HRINT
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=hrint
DB_USERNAME=hrint
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# OSS (Aliyun)
OSS_ACCESS_KEY_ID=
OSS_ACCESS_KEY_SECRET=
OSS_BUCKET=
OSS_ENDPOINT=
OSS_URL=

# TRTC (Tencent RTC)
TRTC_SDK_APP_ID=
TRTC_SECRET_KEY=

# Tencent CSS Live Streaming (Seminar)
TRTC_PUSH_DOMAIN=
TRTC_PLAY_DOMAIN=
TRTC_PUSH_KEY=
TRTC_PLAY_KEY=
TRTC_APP_NAME=live

# Social OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
FACEBOOK_APP_ID=
FACEBOOK_APP_SECRET=
LINKEDIN_CLIENT_ID=
LINKEDIN_CLIENT_SECRET=
WECHAT_APP_ID=
WECHAT_APP_SECRET=

# Mail (default, overridden by DB settings)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@horizonhr.com
MAIL_FROM_NAME=HorizonHR
```

---

## Docker Compose Services

```yaml
services:
  nginx:       # port 80 (proxy to frontend:5173 and backend:9000)
  backend:     # PHP-FPM 8.2, mounts ./backend
  frontend:    # Node 20, runs vite dev server, mounts ./frontend
  mariadb:     # MariaDB 10.11, port 3306
  redis:       # Redis 7, port 6379
```

---

## Tailwind CSS Brand Colors

Configure in `tailwind.config.js`:
```js
theme: {
  extend: {
    colors: {
      primary: '#003366',
      'primary-light': '#E6F0FF',
      'primary-dark': '#002244',
    }
  }
}
```

---

## Acceptance Criteria

- [ ] `docker compose up` starts all 5 services without errors
- [ ] `http://localhost` returns the Vue 3 app (Vite dev server)
- [ ] `http://localhost/api` returns Laravel JSON response (e.g., 404 from API)
- [ ] MariaDB accessible on port 3306 with `hrint` database created
- [ ] Redis accessible on port 6379
- [ ] `php artisan migrate:status` runs without errors (even with no migrations yet)
- [ ] `npm run dev` inside frontend container starts Vite
- [ ] Element Plus and Vue i18n imported and rendering in a test `<HelloWorld>` component
- [ ] Tailwind CSS working (test class applies color)
- [ ] `.env.example` files present for both backend and frontend
- [ ] All required npm packages installed (check `package.json`)
- [ ] All required Composer packages installed (check `composer.json`)

---

## Notes

- Use `utf8mb4` charset and `utf8mb4_unicode_ci` collation for MariaDB
- PHP `upload_max_filesize` and `post_max_size` must be at least 25MB (for resume uploads ≤20MB)
- Configure nginx to pass `X-Real-IP` and `X-Forwarded-For` headers (needed for IP logging in contacts table)
- The frontend Vite proxy should forward `/api/*` requests to the backend to avoid CORS issues in development
- Element Plus should be configured with auto-import using `unplugin-vue-components` for tree-shaking
