# TASK-043: Production Deployment

**Phase:** 13 — Finalization  
**Status:** Pending  
**Depends On:** TASK-042  
**Priority:** HIGH  

---

## Objective

Configure the project for production deployment: Docker production compose file, nginx SSL configuration, environment variable documentation, database migration strategy, health check endpoints, and backup procedures.

---

## Reference Documents

1. `DOCUMENTS/SOLUTION.md` — Section 8 (Deployment)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Section 3 (Infrastructure)
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section VIII (Non-functional: Performance, Security, Availability)

---

## Deliverables

### 1. Docker Production Configuration

**`docker-compose.prod.yml`**

Differences from `docker-compose.yml` (development):
- No Vite dev server container (use pre-built frontend assets)
- No `mailpit` container
- All containers use `:latest` pinned to specific versions (e.g., `php:8.2-fpm`)
- Add restart policies: `restart: unless-stopped`
- Add resource limits:
  ```yaml
  deploy:
    resources:
      limits:
        memory: 512M
  ```
- Separate named volumes for data persistence
- No bind mounts for code (copy into image or use volume from build)
- Environment variables from `.env` file (do NOT hardcode)

**Services:**
```yaml
services:
  nginx:
    image: nginx:1.25-alpine
    ports: ["80:80", "443:443"]
    volumes:
      - ./docker/nginx/nginx.prod.conf:/etc/nginx/nginx.conf
      - ssl_certs:/etc/ssl/certs
      - static_files:/var/www/public

  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    volumes:
      - storage_data:/var/www/storage

  queue:
    build: same as app
    command: php artisan queue:work --queue=emails,default --sleep=3 --tries=3
    
  mariadb:
    image: mariadb:10.11
    volumes:
      - db_data:/var/lib/mysql
    
  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data
```

### 2. Production Dockerfile

**`Dockerfile.prod`**:
```dockerfile
# Stage 1: Build frontend
FROM node:20-alpine AS frontend-builder
WORKDIR /app/frontend
COPY frontend/package*.json ./
RUN npm ci
COPY frontend/ .
RUN npm run build

# Stage 2: PHP app
FROM php:8.2-fpm-alpine
WORKDIR /var/www

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql redis opcache

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . .
COPY --from=frontend-builder /app/public/build ./public/build

# Install PHP dependencies (production only)
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
```

### 3. Nginx Production Config

**`docker/nginx/nginx.prod.conf`**:
```nginx
server {
    listen 80;
    server_name _;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    ssl_certificate /etc/ssl/certs/fullchain.pem;
    ssl_certificate_key /etc/ssl/certs/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    
    root /var/www/public;
    index index.php;
    
    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy strict-origin-when-cross-origin;
    
    # Gzip
    gzip on;
    gzip_types text/plain application/json text/css application/javascript;
    
    # Frontend assets (long cache)
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # PHP
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Block sensitive files
    location ~ /\.env { deny all; }
    location ~ /\.git { deny all; }
}
```

### 4. Environment Variables Documentation

**`.env.production.example`** — complete list of all required production variables:
```
# App
APP_KEY=
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_HOST=mariadb
DB_DATABASE=horizon_international
DB_USERNAME=
DB_PASSWORD=

# Redis
REDIS_HOST=redis

# OSS
OSS_ACCESS_KEY_ID=
OSS_ACCESS_KEY_SECRET=
OSS_BUCKET=
OSS_ENDPOINT=
OSS_REGION=
OSS_URL=

# TRTC
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
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
LINKEDIN_CLIENT_ID=
LINKEDIN_CLIENT_SECRET=
WECHAT_CLIENT_ID=
WECHAT_CLIENT_SECRET=
```

### 5. Health Check Endpoint

**`app/Http/Controllers/HealthController.php`**:
```php
public function check() {
    return response()->json([
        'status' => 'ok',
        'db' => DB::connection()->getPdo() ? 'ok' : 'fail',
        'redis' => Redis::ping() === '+PONG' ? 'ok' : 'fail',
        'timestamp' => now()->toISOString(),
    ]);
}
```
Route: `GET /api/health` (no auth)

Add to Docker healthcheck:
```yaml
healthcheck:
  test: ["CMD", "curl", "-f", "http://localhost/api/health"]
  interval: 30s
  timeout: 10s
  retries: 3
```

### 6. Deployment Runbook

**`DOCUMENTS/DEPLOYMENT.md`** — step-by-step deployment guide:

```markdown
## Initial Deployment
1. Clone repository to server
2. Copy .env.production.example to .env and fill all values
3. Run: docker-compose -f docker-compose.prod.yml build
4. Run: docker-compose -f docker-compose.prod.yml up -d
5. Run migrations: docker-compose exec app php artisan migrate --force
6. Run seeders (initial data): docker-compose exec app php artisan db:seed --class=LanguageSeeder
7. Generate app key: docker-compose exec app php artisan key:generate
8. Create storage link: docker-compose exec app php artisan storage:link
9. Clear and cache config: docker-compose exec app php artisan config:cache && php artisan route:cache

## Update Deployment (Zero-downtime)
1. git pull
2. docker-compose -f docker-compose.prod.yml build app
3. docker-compose -f docker-compose.prod.yml up -d --no-deps app
4. docker-compose exec app php artisan migrate --force
5. docker-compose exec app php artisan config:cache

## SSL Certificate (Let's Encrypt)
Use certbot with nginx plugin, or use Cloudflare SSL proxy.

## Backup Strategy
- Database: daily mysqldump → compressed → upload to OSS backup bucket
- Run: docker-compose exec mariadb mysqldump -u root -p$DB_ROOT_PASSWORD horizon_international | gzip > backup_$(date +%Y%m%d).sql.gz
- Redis: RDB snapshots auto-saved to redis_data volume

## Monitoring
- /api/health endpoint returns JSON status for all services
- Set up uptime monitor (UptimeRobot or similar) on /api/health
```

---

## Acceptance Criteria

- [ ] `docker-compose.prod.yml` starts all production services
- [ ] Multi-stage `Dockerfile.prod` builds frontend + PHP into single image
- [ ] Nginx config redirects HTTP to HTTPS
- [ ] Nginx security headers configured
- [ ] Nginx serves frontend build assets with 1-year cache headers
- [ ] `GET /api/health` returns `{ status: 'ok', db: 'ok', redis: 'ok' }`
- [ ] `.env.production.example` documents all required variables
- [ ] `DOCUMENTS/DEPLOYMENT.md` contains complete deployment instructions
- [ ] Queue worker runs as a separate container
- [ ] Database data persisted in named Docker volume
- [ ] Sensitive files (`.env`, `.git`) blocked by nginx

---

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_KEY` is a securely generated random key
- [ ] Database password is strong (not default)
- [ ] All API secrets stored in env variables, never committed to git
- [ ] `.gitignore` includes `.env`, `.env.production`
- [ ] SSL certificate valid and auto-renewing
- [ ] Security headers set in nginx
- [ ] Redis not exposed on public network (internal Docker network only)
- [ ] MariaDB not exposed on public network (internal Docker network only)

---

## Notes

- SSL: Easiest approach is Cloudflare SSL proxy (free) — terminates SSL at Cloudflare edge, plain HTTP between Cloudflare and nginx. Then nginx only needs to listen on 80.
- Alternative: Let's Encrypt with Certbot inside nginx container
- Queue worker: must run `php artisan queue:work` as a persistent process — Docker container keeps it running
- Storage link: `php artisan storage:link` creates `public/storage` symlink — required for local file storage fallback
- Database migrations in CI/CD: always use `--force` flag in non-interactive environments
