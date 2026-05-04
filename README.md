# HRINT — Hubei Horizon International Talent Service

Full-stack recruitment and education platform connecting Southeast Asian students with Chinese universities and enterprises. Supports student applications, live video interviews, seminar broadcasts, enterprise talent sourcing, and multilingual CMS.

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2, Laravel 10, Laravel Sanctum |
| Frontend | Vue 3.4, Vite 5, TypeScript, Element Plus, Pinia, Vue Router 4, Vue i18n v9 |
| Database | MariaDB 10.11 |
| Cache / Queue | Redis 7 |
| Web Server | nginx 1.25 |
| File Storage | Aliyun OSS (S3-compatible) |
| Video Interview | TRTC (Tencent RTC) — 1:1 WebRTC |
| Live Streaming | Tencent CSS / TRTC CDN — seminar broadcasts with danmu |
| SEO | @unhead/vue — dynamic `<title>` + Open Graph tags |
| Bundle Analysis | rollup-plugin-visualizer — generates `bundle-stats.html` |

---

## Features

| Module | Description |
|--------|-------------|
| **Student Portal** | Registration, profile, resume upload/management, job search & apply, interview room (TRTC), seminar watch |
| **Enterprise Portal** | Company profile, job posting, talent pool browse, interview scheduling |
| **Admin Panel** | User/enterprise approval, resume review, seminar management, CMS (pages/posts/announcements), settings, multi-language management |
| **Public Pages** | Home, About, Study in China, Talent Pool, Corporate, Jobs, Seminars, News, Contact |
| **Social OAuth** | Google, Facebook, LinkedIn, WeChat — login & account linking from profile |
| **i18n** | English, Simplified Chinese, Thai (807 keys, all 3 locales in sync) |
| **Email Notifications** | Application received, status change, interview invitation, result, seminar reminder (queue-based) |

---

## Prerequisites

- Docker 24+ and Docker Compose v2
- (For local dev without Docker) PHP 8.2, Composer 2, Node.js 20, MariaDB 10.11, Redis 7

---

## Quick Start (Development)

```bash
# 1. Clone the repository
git clone <repo-url> horizon-international
cd horizon-international

# 2. Copy environment file
cp backend/.env.example backend/.env

# 3. Start all services (nginx + PHP-FPM + Vite dev server + MariaDB + Redis + Mailpit)
docker compose up -d

# 4. Generate Laravel app key
docker compose exec backend php artisan key:generate

# 5. Run database migrations
docker compose exec backend php artisan migrate

# 6. (Optional) Seed initial data
docker compose exec backend php artisan db:seed
```

Once running:
- **App:** http://localhost (via nginx)
- **Vite dev server:** http://localhost:5173 (HMR)
- **API:** http://localhost/api
- **Mail UI (Mailpit):** http://localhost:8025
- **MariaDB:** localhost:3306 (user: `hrint`, password: `secret`)
- **Redis:** localhost:6379

---

## Production Deployment

See [DOCUMENTS/DEPLOYMENT.md](DOCUMENTS/DEPLOYMENT.md) for the complete production deployment guide covering:
- Docker multi-stage build (`Dockerfile.prod`)
- Full production compose (`docker-compose.prod.yml`) with nginx, app, queue worker, MariaDB, Redis
- SSL certificate setup (Cloudflare origin cert or Let's Encrypt)
- Zero-downtime update procedure
- Database backup / restore
- Health monitoring via `GET /api/health`

```bash
# Quick production start
cp .env.production.example .env
# Fill in .env values, then:
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

---

## Project Structure

```
horizon-international/
├── backend/                     # Laravel 10 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Auth/        # Login, register, email verify, social OAuth
│   │   │   │   ├── Admin/       # Full admin management (18 controllers)
│   │   │   │   ├── Student/     # Student portal (profile, resume, jobs, interviews, seminars)
│   │   │   │   ├── Enterprise/  # Enterprise portal (jobs, talent, interviews)
│   │   │   │   ├── Public/      # Public endpoints (CMS, jobs, seminars, news)
│   │   │   │   ├── Webhook/     # TRTC live callback
│   │   │   │   └── HealthController.php
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/              # 20 Eloquent models
│   │   ├── Services/            # Business logic (OSS, TRTC, etc.)
│   │   └── Jobs/                # Queued email jobs (9 job classes)
│   ├── routes/api.php
│   └── database/migrations/
├── frontend/                    # Vue 3 + Vite SPA (TypeScript)
│   └── src/
│       ├── api/                 # Axios API clients (auth, student, enterprise, admin, public, jobs)
│       ├── composables/         # usePageMeta, useSanitize
│       ├── components/          # Shared UI (auth, common, forms, interview, seminar, layout)
│       ├── i18n/locales/        # en.json, zh_cn.json, th.json (807 keys each)
│       ├── router/              # Vue Router 4 (lazy-loaded, chunk-grouped)
│       ├── stores/              # Pinia stores (auth, settings, language)
│       └── views/               # Page components (admin/, enterprise/, student/, public/, auth/)
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf           # Development nginx config
│   │   └── nginx.prod.conf      # Production nginx (SSL, security headers, SPA routing)
│   └── php/Dockerfile           # Development PHP-FPM image
├── Dockerfile.prod              # Multi-stage production build (Node → PHP)
├── docker-compose.yml           # Development compose
├── docker-compose.prod.yml      # Production compose (standalone)
├── .env.production.example      # All required production env vars documented
└── DOCUMENTS/
    ├── DEPLOYMENT.md            # Production deployment guide
    └── PLAN/TASKS/              # Implementation task history (TASK-001 through TASK-043)
```

---

## Development Commands

```bash
# View logs
docker compose logs -f

# Run artisan commands
docker compose exec backend php artisan <command>

# Run frontend npm scripts
docker compose exec frontend npm run <script>

# Access MariaDB shell
docker compose exec mariadb mariadb -u hrint -psecret hrint

# Access Redis CLI
docker compose exec redis redis-cli

# TypeScript check (frontend)
cd frontend && npx tsc --noEmit

# Bundle analysis — generates bundle-stats.html
cd frontend && npm run build
```

---

## Environment Variables

| File | Purpose |
|------|---------|
| `backend/.env.example` | Development environment template |
| `.env.production.example` | Production environment template (all vars documented) |

Key secrets to configure for production:
- `APP_KEY` — Generate with `php artisan key:generate`
- `DB_PASSWORD` / `DB_ROOT_PASSWORD` — Strong database passwords
- `OSS_*` — Aliyun OSS credentials for file storage
- `TRTC_SDK_APP_ID` / `TRTC_SECRET_KEY` — Tencent RTC (video interviews)
- `TRTC_PUSH_DOMAIN` / `TRTC_PLAY_DOMAIN` — Tencent CSS (seminar live streaming)
- `GOOGLE_CLIENT_ID` / `FACEBOOK_APP_ID` / `LINKEDIN_CLIENT_ID` / `WECHAT_APP_ID` — Social OAuth

---

## i18n

The platform supports three languages: **English (EN)**, **Simplified Chinese (ZH_CN)**, and **Thai (TH)**.

- Translation files: `frontend/src/i18n/locales/`
- All 807 keys are present in all 3 locale files
- Thai translations marked `[AUTO]` are machine-translated placeholders pending professional review
- Language switcher available in the navigation bar

---

## Health Check

```bash
curl https://your-domain.com/api/health
# {"status":"ok","db":"ok","redis":"ok","timestamp":"2026-05-04T..."}
```

---

## License

Private — Hubei Horizon International Talent Service Co., Ltd.

