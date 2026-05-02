# HRINT — Hubei Horizon International Talent Service

Full-stack web platform connecting Southeast Asian students and professionals with Chinese universities and enterprises.

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2, Laravel 10, Laravel Sanctum |
| Frontend | Vue 3, Vite 5, Element Plus, Pinia, Vue i18n v9 |
| Database | MariaDB 10.11 |
| Cache / Queue | Redis 7 |
| Web Server | nginx 1.25 |
| File Storage | Aliyun OSS |
| Video Interview | TRTC (Tencent RTC) |
| Live Streaming | Tencent CSS / TRTC CDN |

---

## Prerequisites

- Docker 24+ and Docker Compose v2
- (For local dev without Docker) PHP 8.2, Composer 2, Node.js 20, MariaDB 10.11, Redis 7

---

## Quick Start (Docker)

```bash
# 1. Clone the repository
git clone <repo-url> horizon-international
cd horizon-international

# 2. Copy environment files
cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# 3. Start all services
docker compose up -d

# 4. Generate Laravel app key
docker compose exec backend php artisan key:generate

# 5. Run database migrations
docker compose exec backend php artisan migrate

# 6. (Optional) Seed initial data
docker compose exec backend php artisan db:seed
```

Once running:
- **Frontend:** http://localhost (via nginx) or http://localhost:5173 (direct Vite)
- **API:** http://localhost/api
- **MariaDB:** localhost:3306 (user: `hrint`, password: `secret`)
- **Redis:** localhost:6379

---

## Project Structure

```
horizon-international/
├── backend/                 # Laravel 10 API
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── Auth/        # Login, register, OAuth
│   │   │   ├── Admin/       # Admin-only endpoints
│   │   │   ├── Student/     # Student portal endpoints
│   │   │   ├── Enterprise/  # Enterprise portal endpoints
│   │   │   └── Public/      # Public-facing endpoints
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Jobs/
│   ├── routes/
│   │   └── api.php
│   └── ...
├── frontend/                # Vue 3 + Vite SPA
│   ├── src/
│   │   ├── api/             # Axios client
│   │   ├── i18n/            # EN / ZH_CN / TH locales
│   │   ├── router/          # Vue Router 4 routes
│   │   ├── stores/          # Pinia stores
│   │   ├── views/           # Page components (by role)
│   │   └── components/      # Shared UI components
│   └── ...
├── docker/
│   ├── nginx/nginx.conf
│   └── php/Dockerfile
├── docker-compose.yml
└── docker-compose.prod.yml
```

---

## Development Commands

```bash
# View logs
docker compose logs -f

# Run artisan commands
docker compose exec backend php artisan <command>

# Run npm scripts
docker compose exec frontend npm run <script>

# Access MariaDB shell
docker compose exec mariadb mariadb -u hrint -psecret hrint

# Access Redis CLI
docker compose exec redis redis-cli
```

---

## Environment Variables

See `backend/.env.example` and `frontend/.env.example` for all required variables.

Key secrets to configure:
- `APP_KEY` — Generate with `php artisan key:generate`
- `TRTC_SDK_APP_ID` / `TRTC_SECRET_KEY` — Tencent RTC credentials
- `TRTC_PUSH_DOMAIN` / `TRTC_PLAY_DOMAIN` — Tencent CSS live streaming
- `OSS_*` — Aliyun OSS credentials
- `GOOGLE_CLIENT_ID` / social OAuth keys

---

## i18n

The platform supports three languages: **English (EN)**, **Simplified Chinese (ZH_CN)**, and **Thai (TH)**.

Translation files are in `frontend/src/i18n/locales/`.

---

## License

Private — Hubei Horizon International Talent Service Co., Ltd.
