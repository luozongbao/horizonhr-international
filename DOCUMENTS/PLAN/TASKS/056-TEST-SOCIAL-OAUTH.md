# TASK-056 — Test: Social OAuth Configuration

**Type:** AI Test Task (OAuth redirect — requires human for actual login flow)  
**Phase:** Test Phase 4 — Integrations  
**Priority:** MEDIUM  
**Prerequisites:** TASK-055 completed or skipped; backend running  
**Estimated Effort:** 20 min  

---

## Description

Verify all OAuth provider redirect endpoints are correctly configured and return proper redirects (not 404/500 errors). The actual OAuth login flow (entering Google/Facebook credentials) is a human test, but we can verify the backend routes respond correctly and the frontend shows OAuth buttons.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.B.1 (Social Login) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Auth — Social Login |

---

## Pre-Test Setup

```bash
BASE_URL="http://10.11.12.30/api"
```

---

## Test Steps

### Group A — Backend OAuth Redirect Endpoints

#### A1. Google OAuth Redirect

```bash
curl -s -o /dev/null -w "%{http_code} %{redirect_url}" \
  "$BASE_URL/auth/google/redirect"
```

**Expected:** HTTP 302 redirect to `https://accounts.google.com/...`

#### A2. Facebook OAuth Redirect

```bash
curl -s -o /dev/null -w "%{http_code} %{redirect_url}" \
  "$BASE_URL/auth/facebook/redirect"
```

**Expected:** HTTP 302 redirect to `https://www.facebook.com/dialog/oauth...`

#### A3. LinkedIn OAuth Redirect

```bash
curl -s -o /dev/null -w "%{http_code} %{redirect_url}" \
  "$BASE_URL/auth/linkedin/redirect"
```

**Expected:** HTTP 302 redirect to `https://www.linkedin.com/oauth/v2/...`

#### A4. WeChat (Weixin) OAuth Redirect

```bash
curl -s -o /dev/null -w "%{http_code} %{redirect_url}" \
  "$BASE_URL/auth/weixin/redirect"
```

**Expected:** HTTP 302 redirect to WeChat OAuth URL, OR HTTP 422/500 with an error about missing WeChat config if credentials not set  
**Acceptable:** Error if WeChat credentials are not configured in `.env`

---

### Group B — Backend OAuth Callback Routes (Existence Check)

#### B1. Verify Callback Routes Exist

```bash
# Check route list for oauth callbacks
docker compose -f /home/zongbao/Projects/horizon-international-claude/docker-compose.yml \
  exec backend php artisan route:list --path=auth | grep -E "callback|redirect"
```

**Expected:** Four callback routes listed:
- `GET auth/google/callback`
- `GET auth/facebook/callback`
- `GET auth/linkedin/callback`
- `GET auth/weixin/callback`

---

### Group C — Frontend OAuth Buttons

#### C1. Verify OAuth Buttons on Login Page

```bash
curl -s http://10.11.12.30 | grep -i "google\|facebook\|linkedin\|wechat\|weixin" | head -5
```

**Expected:** References to OAuth provider icons or buttons in the HTML  
**Note:** If SPA, may not be in initial HTML. Test manually in browser.

**Manual check:**
1. Open `http://10.11.12.30/login` in browser
2. Check for: Google, Facebook, LinkedIn, WeChat login buttons
3. Click each button → should redirect to the provider OAuth page (if credentials set)

**Check:**
- [ ] Google button visible on login page
- [ ] Facebook button visible on login page
- [ ] LinkedIn button visible on login page
- [ ] WeChat button visible on login page

---

### Group D — OAuth Config Verification

#### D1. Check OAuth Environment Variables

```bash
docker compose -f /home/zongbao/Projects/horizon-international-claude/docker-compose.yml \
  exec backend php artisan tinker --execute="
    echo 'GOOGLE_ID: ' . (config('services.google.client_id') ? 'SET' : 'NOT SET') . PHP_EOL;
    echo 'FACEBOOK_ID: ' . (config('services.facebook.client_id') ? 'SET' : 'NOT SET') . PHP_EOL;
    echo 'LINKEDIN_ID: ' . (config('services.linkedin.client_id') ? 'SET' : 'NOT SET') . PHP_EOL;
    echo 'WEIXIN_ID: ' . (config('services.weixin.client_id') ? 'SET' : 'NOT SET') . PHP_EOL;
  "
```

**Expected:** Shows which OAuth providers have credentials configured  
**Acceptable:** Some may be "NOT SET" if credentials not yet obtained

---

## Acceptance Criteria

- [ ] All 4 OAuth redirect endpoints exist and respond (302 or proper error)
- [ ] OAuth callback routes exist in route list
- [ ] Frontend displays OAuth login buttons for all 4 providers
- [ ] Configured providers redirect correctly to OAuth provider pages
- [ ] Unconfigured providers return a useful error (not 500)

---

## Notes for Human Follow-Up

The following require human verification with real OAuth credentials:
1. Complete Google OAuth login flow
2. Complete Facebook OAuth login flow
3. Complete LinkedIn OAuth login flow
4. Complete WeChat OAuth login flow (requires WeChat app and China account)

---

## Next Task

Proceed to **TASK-057** (AI Test — i18n & Multi-language)
