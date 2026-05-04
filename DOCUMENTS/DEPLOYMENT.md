# HRINT — Deployment Guide

This document covers initial deployment, updates, SSL setup, backup, and monitoring for the HRINT platform.

---

## Prerequisites

- A Linux server (Ubuntu 22.04 LTS recommended) with Docker 24+ and Docker Compose v2
- A domain name pointed at the server's IP
- (Optional but recommended) Cloudflare in front for DDoS protection and free SSL

---

## 1. Initial Deployment

```bash
# 1. Clone the repository
git clone <repo-url> /opt/hrint
cd /opt/hrint

# 2. Configure environment variables
cp .env.production.example .env
nano .env   # Fill in ALL values — see comments in the file

# 3. Generate a secure APP_KEY
#    Run this BEFORE building the image so it's baked in, OR set APP_KEY= in .env
#    then run `php artisan key:generate --show` inside the container post-deploy.

# 4. Build the production image (multi-stage: builds frontend then packages PHP)
docker compose -f docker-compose.prod.yml build

# 5. Start all services
docker compose -f docker-compose.prod.yml up -d

# 6. Wait for MariaDB to be healthy (~15s), then run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# 7. Seed required initial data (languages, default settings)
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --class=LanguageSeeder
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --class=SettingsSeeder

# 8. Create storage symlink (public/storage → storage/app/public)
docker compose -f docker-compose.prod.yml exec app php artisan storage:link

# 9. Cache routes and config for performance
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache

# 10. Verify health endpoint
curl https://your-domain.com/api/health
# Expected: {"status":"ok","db":"ok","redis":"ok","timestamp":"..."}
```

---

## 2. Update Deployment (Zero-Downtime)

```bash
cd /opt/hrint

# 1. Pull latest code
git pull origin main

# 2. Rebuild the app image (only rebuilds changed layers)
docker compose -f docker-compose.prod.yml build app

# 3. Replace the running app container without touching other services
docker compose -f docker-compose.prod.yml up -d --no-deps app queue

# 4. Run any new migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# 5. Refresh caches
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
```

---

## 3. SSL Certificate Setup

### Option A: Cloudflare SSL Proxy (Recommended — simplest)

1. Add the domain to Cloudflare (free plan is fine).
2. Set DNS A record to your server IP.
3. Enable "Full (strict)" SSL mode in Cloudflare.
4. Cloudflare terminates TLS at the edge; traffic between Cloudflare and your server can use a self-signed or Cloudflare Origin Certificate.
5. Download a **Cloudflare Origin Certificate** (15-year validity) from Cloudflare dashboard → SSL/TLS → Origin Server.
6. Place the certificate files in the `ssl_certs` Docker volume:
   ```bash
   # Copy cert files into the named volume
   docker run --rm -v hrint_ssl_certs:/certs -v $(pwd)/docker/ssl:/src alpine \
     sh -c "cp /src/fullchain.pem /certs/ && cp /src/privkey.pem /certs/"
   ```

### Option B: Let's Encrypt (Certbot)

```bash
# Install certbot on the host
apt install certbot

# Stop nginx temporarily to free port 80
docker compose -f docker-compose.prod.yml stop nginx

# Obtain certificate
certbot certonly --standalone -d your-domain.com

# Copy certificates into the ssl_certs Docker volume
docker run --rm \
  -v hrint_ssl_certs:/certs \
  -v /etc/letsencrypt/live/your-domain.com:/src:ro \
  alpine sh -c "cp /src/fullchain.pem /certs/ && cp /src/privkey.pem /certs/"

# Restart nginx
docker compose -f docker-compose.prod.yml up -d nginx

# Auto-renewal cron (add to root crontab)
0 3 * * * certbot renew --quiet --deploy-hook "docker compose -f /opt/hrint/docker-compose.prod.yml restart nginx"
```

---

## 4. Database Backup

### Manual backup

```bash
# Create a timestamped compressed dump
docker compose -f docker-compose.prod.yml exec mariadb \
  mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
  | gzip > /opt/backups/hrint_$(date +%Y%m%d_%H%M%S).sql.gz
```

### Automated daily backup (cron)

Add to `/etc/cron.d/hrint-backup`:
```
0 2 * * * root cd /opt/hrint && \
  docker compose -f docker-compose.prod.yml exec -T mariadb \
  mysqldump -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" \
  | gzip > /opt/backups/hrint_$(date +\%Y\%m\%d).sql.gz && \
  find /opt/backups -name 'hrint_*.sql.gz' -mtime +30 -delete
```

### Restore from backup

```bash
gunzip -c /opt/backups/hrint_20260101.sql.gz | \
  docker compose -f docker-compose.prod.yml exec -T mariadb \
  mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
```

---

## 5. Monitoring

- **Health endpoint:** `GET https://your-domain.com/api/health`
  - Returns `{"status":"ok","db":"ok","redis":"ok"}` when all services are up
  - Returns HTTP 503 if any service is degraded
- **Uptime monitor:** Configure UptimeRobot, Better Stack, or similar to poll `/api/health` every 5 minutes.
- **Docker logs:**
  ```bash
  docker compose -f docker-compose.prod.yml logs -f app
  docker compose -f docker-compose.prod.yml logs -f nginx
  docker compose -f docker-compose.prod.yml logs -f queue
  ```
- **Laravel logs** are stored in the `storage_data` volume at `storage/logs/laravel.log`.

---

## 6. Useful Commands

```bash
# Run artisan commands
docker compose -f docker-compose.prod.yml exec app php artisan <command>

# Open MariaDB shell
docker compose -f docker-compose.prod.yml exec mariadb mariadb \
  -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"

# Redis CLI
docker compose -f docker-compose.prod.yml exec redis redis-cli

# View all container statuses
docker compose -f docker-compose.prod.yml ps

# Restart a single service
docker compose -f docker-compose.prod.yml restart nginx

# Scale queue workers (run 2 in parallel)
docker compose -f docker-compose.prod.yml up -d --scale queue=2
```

---

## 7. Security Checklist

- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_KEY` is set and at least 32 random characters
- [ ] `DB_PASSWORD` and `DB_ROOT_PASSWORD` are strong (≥20 chars)
- [ ] `.env` file is NOT committed to git (verify: `git status`)
- [ ] SSL certificate is valid and HSTS is enabled (nginx.prod.conf includes `Strict-Transport-Security`)
- [ ] MariaDB port (3306) is not exposed in `docker-compose.prod.yml` ✓
- [ ] Redis port (6379) is not exposed in `docker-compose.prod.yml` ✓
- [ ] `/api/health` accessible but returns no sensitive data ✓
- [ ] `/.env` and `/.git` paths blocked by nginx ✓
- [ ] All OSS / TRTC / OAuth keys stored in `.env`, never in code
