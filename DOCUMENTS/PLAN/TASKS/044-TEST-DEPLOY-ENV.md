# TASK-044 — Test: Deployment & Environment Verification

**Type:** AI Test Task  
**Phase:** Test Phase 0 — Foundation  
**Priority:** CRITICAL  
**Prerequisites:** All TASK-001 to TASK-043 completed; Docker containers running  
**Estimated Effort:** 30 min  

---

## Description

Verify the entire deployment environment is correctly set up, all services are reachable, database is seeded, and the application stack is production-ready for functional testing. This is the mandatory first step before any other test task can begin.

---

## Reference Documents

| Document | Path | Read Purpose |
|----------|------|--------------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section VI Technical Standards |
| System Design | `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` | DB Schema, Architecture |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Base URL, health endpoints |
| README | `README.md` | Setup instructions |

---

## Environment Details

| Item | Value |
|------|-------|
| App URL | `http://10.11.12.30` |
| API Base | `http://10.11.12.30/api` |
| Mailpit UI | `http://10.11.12.30:8025` |
| Admin Email | `admin@horizonhr.com` |
| Admin Password | `Admin@12345` |
| Docker Project Dir | `/home/zongbao/Projects/horizon-international-claude` |

---

## Test Steps

### Step 1 — Verify Docker Services

```bash
cd /home/zongbao/Projects/horizon-international-claude
docker compose ps
```

**Expected:** All 6 services running (`nginx`, `backend`, `frontend`, `mariadb`, `redis`, `mailpit`)  
**Check:** Status column = `Up` or `running` for all services

---

### Step 2 — Verify Backend Health Endpoint

```bash
curl -s http://10.11.12.30/api/health | python3 -m json.tool
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "status": "ok",
    "database": "ok",
    "cache": "ok"
  }
}
```

---

### Step 3 — Verify Frontend Is Served

```bash
curl -s http://10.11.12.30/ | head -5
```

**Expected:** HTML with `<!doctype html>` and Vue app mount point

---

### Step 4 — Verify Database Tables

```bash
docker compose exec backend php artisan tinker --execute="echo DB::table('users')->count().' users, '.DB::table('settings')->count().' settings, '.DB::table('languages')->count().' languages';"
```

**Expected:** Output shows counts > 0 (seeded data exists)

---

### Step 5 — Verify Admin Seed User

```bash
curl -s -X POST http://10.11.12.30/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@horizonhr.com","password":"Admin@12345"}' | python3 -m json.tool
```

**Expected:**
```json
{
  "success": true,
  "data": {
    "token": "...",
    "user": {
      "role": "admin",
      "email": "admin@horizonhr.com"
    }
  }
}
```

---

### Step 6 — Verify Redis Cache

```bash
docker compose exec backend php artisan tinker --execute="Cache::put('test', 'ok', 10); echo Cache::get('test');"
```

**Expected:** `ok`

---

### Step 7 — Verify Mail Service (Mailpit)

```bash
curl -s http://10.11.12.30:8025/api/v1/messages | python3 -m json.tool | head -10
```

**Expected:** JSON response from Mailpit API (even if empty inbox: `{"total":0,...}`)

---

### Step 8 — Verify Storage Link

```bash
docker compose exec backend ls -la public/ | grep storage
```

**Expected:** `storage -> /var/www/html/storage/app/public` symlink

---

### Step 9 — Verify Route List

```bash
docker compose exec backend php artisan route:list --path=api | wc -l
```

**Expected:** > 80 lines (all API routes loaded)

---

### Step 10 — Check Laravel Log for Critical Errors

```bash
docker compose exec backend tail -30 storage/logs/laravel.log | grep -i "ERROR\|exception" | head -10
```

**Expected:** No NEW critical errors (only old boot-time errors from setup)

---

## Acceptance Criteria

- [ ] All 6 Docker services are running
- [ ] `GET /api/health` returns `{"success":true,"data":{"status":"ok"}}`
- [ ] Frontend HTML is served at `http://10.11.12.30`
- [ ] Admin login returns valid token
- [ ] Database has seeded data (users, settings, languages)
- [ ] Redis cache is functional
- [ ] Mailpit UI is accessible at port 8025
- [ ] Storage symlink exists
- [ ] 80+ API routes are registered
- [ ] No critical errors in Laravel log

---

## On Failure

If any check fails, run these diagnostics before proceeding:

```bash
# Restart containers
docker compose down && docker compose up -d

# Re-cache config
docker compose exec backend php artisan config:clear && php artisan config:cache

# Check logs
docker compose logs backend --tail=50
docker compose logs nginx --tail=20
```

---

## Next Task

After all checks pass → Proceed to **TASK-045** (Backend Auth API Tests)
