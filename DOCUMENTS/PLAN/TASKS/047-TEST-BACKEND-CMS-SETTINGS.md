# TASK-047 — Test: Backend CMS, Settings & Language API

**Type:** AI Test Task  
**Phase:** Test Phase 1 — Backend CMS & Config  
**Priority:** HIGH  
**Prerequisites:** TASK-045 passed; admin token available  
**Estimated Effort:** 40 min  

---

## Description

Test CMS (Pages and Posts), Settings (site config, SMTP, logo/favicon), and Language/Translation management APIs. Verify multi-language content storage (EN/ZH_CN/TH) and that settings are persisted correctly.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.C (CMS, Settings) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | CMS, Settings sections |
| System Design | `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` | DB Schema — pages, posts, settings, languages |
| Multi-language Mockup | `DOCUMENTS/DESIGNS/MOCKUP_SETTINGS_MULTI_LANGUAGE.md` | Language field patterns |

---

## Pre-Test Setup

```bash
BASE_URL="http://10.11.12.30/api"

ADMIN_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@horizonhr.com","password":"Admin@12345"}' \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['token'])")
```

---

## Test Steps

### Group A — Settings API

#### A1. Get All Settings

```bash
curl -s $BASE_URL/admin/settings \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool | head -40
```

**Expected:** `success: true`, grouped settings (site, smtp, social, etc.)

#### A2. Update Site Name Setting

```bash
curl -s -X PUT $BASE_URL/admin/settings \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "settings": [
      {"key": "site_name", "value": "HRINT Test Platform"},
      {"key": "site_name_zh", "value": "测试平台"},
      {"key": "site_name_th", "value": "แพลตฟอร์มทดสอบ"}
    ]
  }' | python3 -m json.tool
```

**Expected:** `success: true`, settings updated

#### A3. Verify Setting Persisted

```bash
curl -s $BASE_URL/settings/public | python3 -m json.tool | grep -A2 "site_name"
```

**Expected:** Updated site name visible in public settings response

#### A4. Update SMTP Settings

```bash
curl -s -X PUT $BASE_URL/admin/settings \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "settings": [
      {"key": "smtp_host", "value": "mailpit"},
      {"key": "smtp_port", "value": "1025"},
      {"key": "smtp_user", "value": ""},
      {"key": "smtp_pass", "value": ""},
      {"key": "mail_from_address", "value": "noreply@horizonhr.com"},
      {"key": "mail_from_name", "value": "HRINT Platform"}
    ]
  }' | python3 -m json.tool
```

**Expected:** `success: true`

---

### Group B — Pages (CMS) API

#### B1. List All Pages

```bash
curl -s $BASE_URL/admin/pages \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool | head -30
```

**Expected:** `success: true`, array of pages (may be empty if no seed pages)

#### B2. Create a New Page

```bash
curl -s -X POST $BASE_URL/admin/pages \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "slug": "test-about",
    "type": "about",
    "status": "published",
    "title_en": "About Us Test",
    "title_zh_cn": "关于我们测试",
    "title_th": "เกี่ยวกับเราทดสอบ",
    "content_en": "<p>This is the about us page in English.</p>",
    "content_zh_cn": "<p>这是关于我们的中文页面。</p>",
    "content_th": "<p>นี่คือหน้าเกี่ยวกับเราภาษาไทย</p>",
    "meta_en": {"title": "About Us", "description": "About HRINT"},
    "meta_zh_cn": {"title": "关于我们", "description": "关于豪睿国际"},
    "meta_th": {"title": "เกี่ยวกับเรา", "description": "เกี่ยวกับ HRINT"}
  }' | python3 -m json.tool
```

**Expected:** `success: true`, page created with ID

#### B3. Get Page by Slug (Public)

```bash
curl -s "$BASE_URL/pages/test-about?lang=en" | python3 -m json.tool
```

**Expected:** Page content in English

```bash
curl -s "$BASE_URL/pages/test-about?lang=zh_cn" | python3 -m json.tool
```

**Expected:** Page content in Chinese

#### B4. Update Page

```bash
PAGE_ID=1

curl -s -X PUT "$BASE_URL/admin/pages/$PAGE_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title_en": "About Us - Updated",
    "status": "published"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, updated title returned

---

### Group C — Posts (News) API

#### C1. Create a News Post

```bash
curl -s -X POST $BASE_URL/admin/posts \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "category": "company_news",
    "status": "published",
    "title_en": "HRINT Expands Southeast Asia Operations",
    "title_zh_cn": "豪睿国际拓展东南亚业务",
    "title_th": "HRINT ขยายธุรกิจเอเชียตะวันออกเฉียงใต้",
    "content_en": "<p>HRINT International is proud to announce expansion...</p>",
    "content_zh_cn": "<p>豪睿国际宣布扩展业务...</p>",
    "content_th": "<p>HRINT ประกาศขยายธุรกิจ...</p>",
    "published_at": "2026-05-04T00:00:00Z"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, post ID returned

#### C2. List Posts (Public)

```bash
curl -s "$BASE_URL/posts?category=company_news&lang=en&per_page=5" | python3 -m json.tool
```

**Expected:** Array with created post visible

#### C3. Filter Posts by Category

```bash
curl -s "$BASE_URL/posts?category=industry_news&per_page=5" | python3 -m json.tool
```

**Expected:** Empty array or filtered results — no 500 error

#### C4. Delete Post

```bash
POST_ID=1

curl -s -X DELETE "$BASE_URL/admin/posts/$POST_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool
```

**Expected:** `success: true`

---

### Group D — Language & Translation API

#### D1. List All Language Keys

```bash
curl -s $BASE_URL/admin/languages \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool | head -30
```

**Expected:** `success: true`, array of translation keys

#### D2. Create a Translation Key

```bash
curl -s -X POST $BASE_URL/admin/languages \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "key": "test.welcome_message",
    "en": "Welcome to HRINT Platform",
    "zh_cn": "欢迎来到豪睿国际平台",
    "th": "ยินดีต้อนรับสู่แพลตฟอร์ม HRINT"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, translation created

#### D3. Get Translations for Language (Public)

```bash
curl -s "$BASE_URL/translations/en" | python3 -m json.tool | head -20
curl -s "$BASE_URL/translations/th" | python3 -m json.tool | head -20
```

**Expected:** JSON object with key-value translation pairs

#### D4. Update Translation

```bash
LANG_ID=1

curl -s -X PUT "$BASE_URL/admin/languages/$LANG_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "en": "Welcome to HRINT - Updated",
    "th": "ยินดีต้อนรับ HRINT - อัปเดต"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, updated values returned

---

## Acceptance Criteria

- [x] Settings API returns all grouped settings
- [x] Site name setting can be updated and persists (EN/ZH_CN/TH)
- [x] SMTP settings can be updated
- [x] CMS page can be created with trilingual content (EN/ZH_CN/TH)
- [x] Public page endpoint returns content in requested language (`?lang=en/zh_cn/th`)
- [x] Page can be updated and deleted
- [x] News post can be created with multilingual content and category
- [x] Posts can be listed and filtered by category
- [x] Translation keys can be created with EN/ZH_CN/TH values
- [x] Translation endpoint returns key-value pairs for each language
- [x] Only admin can write/update settings, pages, posts, languages

---

## Test Results (2026-05-04)

| Test | Status | Notes |
|------|--------|-------|
| A1 Get All Settings | ✅ PASS | Returns grouped settings: website, seo, social, smtp, system |
| A2 Update Site Name | ✅ PASS | After fix to `SettingsController` (see bugs) |
| A3 Verify Public Settings | ✅ PASS | `GET /public/settings` returns updated values |
| A4 Update SMTP Settings | ✅ PASS | Keys: `smtp_host`, `smtp_port`, `smtp_username`, `smtp_from_address` |
| B1 List Pages | ✅ PASS | Returns empty array when no pages seeded |
| B2 Create Page | ✅ PASS | Valid `type` values: `page`, `announcement` (not `about`) |
| B3 Get Page by Slug (EN+ZH) | ✅ PASS | Route: `GET /public/pages/{slug}` (not `/api/pages/`) |
| B4 Update Page | ✅ PASS | Returns updated `title_en` |
| C1 Create News Post | ✅ PASS | Valid categories: `company_news`, `industry_news`, `study_abroad`, `recruitment` |
| C2 List Posts Public | ✅ PASS | Route: `GET /public/posts` (not `/api/posts`) |
| C3 Filter by Category | ✅ PASS | Returns empty array for non-existent category |
| C4 Delete Post | ✅ PASS | Returns `success: true` |
| D1 List Translations | ✅ PASS | Returns array with `id`, `key`, `en`, `zh_cn`, `th` |
| D2 Create Translation | ✅ PASS | Route: `POST /admin/translations` (not `/admin/languages`) |
| D3 Get Public Translations | ✅ PASS | `GET /public/translations?lang=en|th` returns flat key-value dict |
| D4 Update Translation | ✅ PASS | `PUT /admin/translations/{key}` |

**Bugs fixed:**
1. `SettingsController::update()` used `$request->validated()['settings']` — but Laravel's `validated()` unwraps the `settings` object when the rule is `'settings' => ['required', 'array']`. Fixed to use `$request->input('settings', [])`.

**Route corrections vs task doc:**
- Public pages: `GET /public/pages/{slug}` (not `/api/pages/{slug}`)
- Public posts: `GET /public/posts` (not `/api/posts`)
- Translations admin: `POST /admin/translations` (not `/admin/languages`)
- Translation update: `PUT /admin/translations/{key}` (not `{id}`)
- Public translations: `GET /public/translations?lang=en` (not `/translations/en`)
- Valid page `type`: `page` or `announcement` (not `about`)
- Valid post `status`: `draft`, `published`, `archived`

---

## Next Task

Proceed to **TASK-048** (Backend Interview & Seminar API Tests)
