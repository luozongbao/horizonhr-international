# TASK-057 — Test: i18n & Multi-language Completeness

**Type:** AI Test Task  
**Phase:** Test Phase 4 — Integrations  
**Priority:** HIGH  
**Prerequisites:** TASK-056 completed; backend running  
**Estimated Effort:** 30 min  

---

## Description

Verify multi-language support works correctly across the system: API responses localize correctly for EN/ZH_CN/TH, translations API returns all expected keys, frontend i18n files are complete (no missing keys), and trilingual CMS content saves and retrieves correctly.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.D (Multilingual Support) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Languages & Translations endpoints |

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

### Group A — Languages API

#### A1. List Available Languages

```bash
curl -s "$BASE_URL/languages" | python3 -m json.tool
```

**Expected:** Array containing at minimum:
```json
[
  {"code": "en", "name": "English", "is_active": true},
  {"code": "zh_CN", "name": "中文 (简体)", "is_active": true},
  {"code": "th", "name": "ภาษาไทย", "is_active": true}
]
```

#### A2. Get All Translations for English

```bash
curl -s "$BASE_URL/translations?lang=en" | python3 -c "
import sys, json
data = json.load(sys.stdin)
keys = data.get('data', {})
print('Total EN keys:', len(keys) if isinstance(keys, dict) else 'N/A')
# Print first 10 keys
if isinstance(keys, dict):
    for k, v in list(keys.items())[:10]:
        print(f'  {k}: {v}')
"
```

**Expected:** Substantial number of translation keys, no errors

#### A3. Get All Translations for Chinese

```bash
curl -s "$BASE_URL/translations?lang=zh_CN" | python3 -c "
import sys, json
data = json.load(sys.stdin)
keys = data.get('data', {})
print('Total ZH keys:', len(keys) if isinstance(keys, dict) else 'N/A')
"
```

**Expected:** Same number of keys as EN (all keys have Chinese translations)

#### A4. Get All Translations for Thai

```bash
curl -s "$BASE_URL/translations?lang=th" | python3 -c "
import sys, json
data = json.load(sys.stdin)
keys = data.get('data', {})
print('Total TH keys:', len(keys) if isinstance(keys, dict) else 'N/A')
"
```

**Expected:** Same number of keys as EN

---

### Group B — Frontend i18n Files

#### B1. Check i18n Files Exist

```bash
ls /home/zongbao/Projects/horizon-international-claude/frontend/src/i18n/
```

**Expected:** At minimum: `en.json` (or `en.ts`), `zh_CN.json`, `th.json`

#### B2. Count Keys in Each Language File

```bash
# For JSON files - count top-level keys
for f in /home/zongbao/Projects/horizon-international-claude/frontend/src/i18n/*.json; do
  echo "$f: $(python3 -c "import json; d=json.load(open('$f')); print(len(d))" 2>/dev/null || echo 'N/A') keys"
done

# For TS files
for f in /home/zongbao/Projects/horizon-international-claude/frontend/src/i18n/*.ts; do
  echo "$f: $(grep -c "':$\|\":" "$f" 2>/dev/null || echo 'N/A') entries"
done
```

**Expected:** Roughly equal key counts across EN, ZH_CN, TH

#### B3. Check for Missing Translations (Empty Values)

```bash
# Find empty string values in Chinese translation file
find /home/zongbao/Projects/horizon-international-claude/frontend/src/i18n/ -name "*.json" | while read f; do
  count=$(python3 -c "
import json, sys
data = json.load(open('$f'))
empty = sum(1 for v in data.values() if isinstance(v, str) and not v.strip())
print(empty)
" 2>/dev/null || echo "0")
  echo "$f: $count empty values"
done
```

**Expected:** 0 or very few empty values

---

### Group C — API Localized Responses

#### C1. Verify Settings with Language Variation

```bash
# Get settings in English
echo "=== English ==="
curl -s "$BASE_URL/settings?lang=en" | python3 -c "
import sys, json
d = json.load(sys.stdin)
settings = d.get('data', [])
for s in settings[:3]:
    print(s.get('key'), ':', s.get('value'))
" 2>/dev/null

# Get settings in Chinese
echo "=== Chinese ==="
curl -s "$BASE_URL/settings?lang=zh_CN" | python3 -c "
import sys, json
d = json.load(sys.stdin)
settings = d.get('data', [])
for s in settings[:3]:
    print(s.get('key'), ':', s.get('value'))
" 2>/dev/null
```

**Expected:** Multilingual settings return correct values per language

#### C2. Get Seminar in Each Language

```bash
for lang in en zh_CN th; do
  echo "=== $lang ==="
  curl -s "$BASE_URL/seminars?lang=$lang&per_page=1" | python3 -c "
import sys, json
d = json.load(sys.stdin)
items = d.get('data', {}).get('data', d.get('data', []))
if isinstance(items, list) and len(items) > 0:
    print('title:', items[0].get('title', 'N/A'))
" 2>/dev/null
done
```

**Expected:** Seminar title changes according to language parameter

#### C3. Get Pages in Each Language

```bash
for lang in en zh_CN th; do
  echo "=== $lang ==="
  curl -s "$BASE_URL/pages/about?lang=$lang" | python3 -c "
import sys, json
d = json.load(sys.stdin)
print('title:', d.get('data', {}).get('title', 'N/A'))
" 2>/dev/null
done
```

**Expected:** About Us page title changes in each language

---

### Group D — No 500 Errors on Language Param

#### D1. Test Invalid Language Falls Back Gracefully

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/languages?lang=invalid_lang"
```

**Expected:** HTTP 200 with default language content (not 500 error)

#### D2. Test Missing Lang Param Falls Back to Default

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/seminars"
```

**Expected:** HTTP 200 with English content (default language)

---

### Group E — Admin Translation Management API

#### E1. Update a Translation Value

```bash
curl -s -X PUT "$BASE_URL/admin/translations" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "key": "nav.home",
    "lang": "th",
    "value": "หน้าหลัก"
  }' | python3 -m json.tool
```

**Expected:** `success: true`

#### E2. Verify Translation Was Saved

```bash
curl -s "$BASE_URL/translations?lang=th" | python3 -c "
import sys, json
d = json.load(sys.stdin)
keys = d.get('data', {})
print('nav.home (TH):', keys.get('nav.home', 'NOT FOUND'))
"
```

**Expected:** `nav.home (TH): หน้าหลัก`

---

## Acceptance Criteria

- [ ] Languages API returns EN, ZH_CN, TH
- [ ] Translations API works for all 3 languages
- [ ] Frontend i18n files exist for EN, ZH_CN, TH
- [ ] No significant empty values in translation files
- [ ] API responds correctly to `?lang=` parameter for seminar/page content
- [ ] Invalid or missing `lang` param falls back gracefully (no 500 error)
- [ ] Admin can update translation values via API
- [ ] Updated translations retrievable immediately

---

## Next Task

Proceed to **TASK-058** (AI Test — Email & Notifications)
