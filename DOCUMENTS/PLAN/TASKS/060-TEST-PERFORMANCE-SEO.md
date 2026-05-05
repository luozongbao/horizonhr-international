# TASK-060 — Test: Performance & SEO

**Type:** AI Test Task  
**Phase:** Test Phase 5 — Performance  
**Priority:** MEDIUM  
**Prerequisites:** TASK-059 passed; app running at `http://10.11.12.30`  
**Estimated Effort:** 25 min  

---

## Description

Verify system performance meets requirements (page load ≤3 seconds, API response time reasonable), check SEO meta tags on public pages, validate page titles (TDK), and verify robots.txt / sitemap if implemented. Also checks for mobile-responsive breakpoints via API.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section VII (Performance Requirements) |
| Design System | `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` | SEO, Meta tags |

---

## Pre-Test Setup

```bash
BASE_URL="http://10.11.12.30"
API_URL="http://10.11.12.30/api"
```

---

## Test Steps

### Group A — API Response Times

#### A1. Health Endpoint Timing

```bash
time curl -s "$API_URL/health" > /dev/null
```

**Expected:** Response time < 500ms

#### A2. Public Seminar Listing Timing

```bash
time curl -s "$API_URL/seminars?per_page=10" > /dev/null
```

**Expected:** < 1 second

#### A3. Multiple Concurrent Requests (Basic Load)

```bash
# Send 10 parallel requests to health endpoint
echo "Testing 10 concurrent requests..."
for i in {1..10}; do
  curl -s -o /dev/null -w "Request $i: %{time_total}s\n" "$API_URL/health" &
done
wait
echo "All requests complete"
```

**Expected:** All requests complete within 3 seconds; no failures

#### A4. Database-Heavy Endpoint Timing

```bash
time curl -s "$API_URL/seminars?per_page=20" > /dev/null
time curl -s "$API_URL/languages" > /dev/null
```

**Expected:** Both < 2 seconds

---

### Group B — Frontend Page Load (Approximate)

#### B1. Initial HTML Load Time

```bash
time curl -s "$BASE_URL/" > /dev/null
```

**Expected:** < 1 second for initial HTML

#### B2. Check HTML Size (Vite Dev Server)

```bash
curl -s "$BASE_URL/" | wc -c
```

**Expected:** Not excessively large (< 100KB for initial HTML)

#### B3. Asset Inventory

```bash
# Check if CSS/JS assets are referenced correctly
curl -s "$BASE_URL/" | grep -E 'src=|href=' | grep -E '\.js|\.css' | head -10
```

**Expected:** Assets referenced with proper paths

---

### Group C — SEO Meta Tags

#### C1. Check Meta Tags in Root HTML

```bash
curl -s "$BASE_URL/" | python3 -c "
import sys, re
html = sys.stdin.read()
# Check meta tags
patterns = {
    'title': r'<title[^>]*>(.*?)</title>',
    'description': r'<meta[^>]*name=[\"'\'']description[\"'\''][^>]*content=[\"'\'']([^\"'\'']+)[\"'\'']',
    'viewport': r'<meta[^>]*name=[\"'\'']viewport[\"'\'']',
    'og:title': r'<meta[^>]*property=[\"'\'']og:title[\"'\'']',
    'og:description': r'<meta[^>]*property=[\"'\'']og:description[\"'\'']',
}
for name, pattern in patterns.items():
    match = re.search(pattern, html, re.IGNORECASE | re.DOTALL)
    if match:
        value = match.group(1) if match.lastindex else 'present'
        print(f'FOUND {name}: {value[:80]}')
    else:
        print(f'MISSING: {name}')
"
```

**Expected:**
- `title` present and contains the company name
- `description` meta tag present
- `viewport` meta tag present (for mobile)
- `og:title` and `og:description` for social sharing (nice-to-have)

#### C2. Check Title Tag Content

```bash
curl -s "$BASE_URL/" | grep -i "<title" | head -2
```

**Expected:** Title contains "Horizon" or "International" — not empty

#### C3. Check Favicon

```bash
curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/favicon.ico"
```

**Expected:** HTTP 200 — favicon exists

---

### Group D — Robots.txt & Sitemap

#### D1. Check robots.txt

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/robots.txt"
```

**Expected:** HTTP 200 with robots.txt content OR 404 (acceptable if not yet implemented)

#### D2. Check Sitemap

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/sitemap.xml"
```

**Expected:** HTTP 200 with sitemap.xml OR 404 (acceptable if not implemented in MVP)

---

### Group E — Frontend Build Assets

#### E1. Check Static Asset Caching Headers

```bash
# Find a JS/CSS asset filename first
ASSET=$(curl -s "$BASE_URL/" | grep -oP '(?<=src=")[^"]+\.js' | head -1)
if [ -n "$ASSET" ]; then
  echo "Checking asset: $BASE_URL$ASSET"
  curl -s -D - -o /dev/null "$BASE_URL$ASSET" | grep -i "cache-control\|etag\|last-modified"
fi
```

**Expected:** Assets have `Cache-Control` or `ETag` headers for browser caching

#### E2. Gzip/Compression Check

```bash
curl -s -H "Accept-Encoding: gzip,deflate" -D - -o /dev/null "$BASE_URL/" \
  | grep -i "content-encoding"
```

**Expected:** `Content-Encoding: gzip` if Nginx gzip is enabled

---

### Group F — API Pagination

#### F1. Verify Pagination Works for Large Datasets

```bash
# Test pagination returns correct structure
curl -s "$API_URL/seminars?per_page=2&page=1" | python3 -c "
import sys, json
d = json.load(sys.stdin)
pagination_data = d.get('data', {})
if isinstance(pagination_data, dict):
    print('total:', pagination_data.get('total'))
    print('per_page:', pagination_data.get('per_page'))
    print('current_page:', pagination_data.get('current_page'))
    print('last_page:', pagination_data.get('last_page'))
    print('items_count:', len(pagination_data.get('data', [])))
else:
    print('data type:', type(pagination_data).__name__)
"
```

**Expected:** Pagination metadata present; `items_count` ≤ 2

---

### Group G — Error Handling Performance

#### G1. 404 Not Found Response

```bash
time curl -s -w "\nHTTP: %{http_code}" "$API_URL/nonexistent-endpoint-xyz"
```

**Expected:** HTTP 404, response < 500ms

#### G2. 404 Frontend Route (SPA Fallback)

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/this-route-does-not-exist-xyz"
```

**Expected:** HTTP 200 (SPA returns index.html for all routes) — Vue Router handles 404

---

## Acceptance Criteria

- [ ] Health API response time < 500ms
- [ ] Public listing APIs respond < 2 seconds
- [ ] 10 concurrent requests all complete within 3 seconds
- [ ] Frontend HTML loads < 1 second
- [ ] Page `<title>` tag contains company name
- [ ] `<meta name="description">` tag present
- [ ] `<meta name="viewport">` tag present (mobile support)
- [ ] Favicon accessible at `/favicon.ico`
- [ ] API pagination returns proper metadata (total, per_page, current_page)
- [ ] 404 API endpoint handled gracefully (not 500)
- [ ] SPA frontend returns 200 for unknown routes (Vue Router fallback works)

---

## Optional (Not Required for MVP)

- robots.txt present
- sitemap.xml present
- OpenGraph meta tags (og:title, og:description)
- Gzip compression enabled
- Cache-Control headers on assets

---

## Test Suite Complete

All test tasks (TASK-044 through TASK-060) have been created.

### Summary of Test Coverage

| Task | Type | Area |
|------|------|------|
| 044 | AI | Deploy & Environment |
| 045 | AI | Backend Auth API |
| 046 | AI | Backend Core API (Resume, Jobs, Applications) |
| 047 | AI | Backend CMS & Settings |
| 048 | AI | Backend Interviews & Seminars |
| 049 | Human | Public Pages & Navigation |
| 050 | Human | Auth Flows (Register, Confirm, Login, Reset) |
| 051 | Human | Student Portal Workflow |
| 052 | Human | Enterprise Portal Workflow |
| 053 | Human | Admin Portal Workflow |
| 054 | Human | TRTC Video Interview |
| 055 | Human | TRTC Live Seminar |
| 056 | AI/Human | Social OAuth Configuration |
| 057 | AI | i18n & Multi-language |
| 058 | AI | Email Notifications |
| 059 | AI | Security & PDPA |
| 060 | AI | Performance & SEO |
