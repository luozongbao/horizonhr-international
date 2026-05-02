# Settings Page — Dynamic Multi-Language Fields Mockup

**Task:** HRINT-DOC-071
**Project:** HorizonHR International Talent Service (HRINT)
**Purpose:** Visual mockup แสดง Settings page ที่รองรับ dynamic multi-language fields
**Format:** Markdown + ASCII diagram (dev reference)

---

## 1. Concept: Dynamic Field Count

Fields ใน Settings page จะ render ตามจำนวน **active languages** ใน `language_settings`

```
┌─────────────────────────────────────────────────────────┐
│  language_settings (database)                          │
│  ─────────────────────────────────────────────────────  │
│                                                         │
│  active_languages: ["en", "zh_cn", "th"]   ← 3 languages │
│                                                         │
│  ↓ renders these fields:                               │
│                                                         │
│  [ Site Name (EN)          ]  [ Site Name (中文)        ]  [ Site Name (ไทย)       ]  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

**เมื่อเพิ่มภาษาญี่ปุ่น:**

```
┌─────────────────────────────────────────────────────────────┐
│  active_languages: ["en", "zh_cn", "th", "ja"]  ← 4 languages │
│                                                             │
│  ↓ renders these fields:                                   │
│                                                             │
│  [ Site Name (EN) ] [ Site Name (中文) ] [ Site Name (ไทย) ] [ Site Name (日本語) ]  │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. General Tab — Branding Section

### 2A. Site Name Fields (3 languages active: EN, ZH_CN, TH)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│  SECTION: Branding / 品牌                                                    │
│  ────────────────────────────────────────────────────────────────────────────  │
│                                                                                 │
│  Site Name / 网站名称                                                           │
│  ┌─────────────────────────────────┐ ┌─────────────────────────────────┐ ┌─────────────────────┐  │
│  │ 🌐 English (EN)                 │ │ 🌐 简体中文 (ZH_CN)              │ │ 🌐 ไทย (TH)         │  │
│  ├─────────────────────────────────┤ ├─────────────────────────────────┤ ├─────────────────────┤  │
│  │                                 │ │                                 │ │                     │  │
│  │  Hubei Horizon International   │ │  湖北纵衡国际人才                │ │  ฮูเปกซิสฮอไรซัน    │  │
│  │                                 │ │                                 │ │  นานาชาติ           │  │
│  │                                 │ │                                 │ │                     │  │
│  └─────────────────────────────────┘ └─────────────────────────────────┘ └─────────────────────┘  │
│                                                                                 │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │ 💡 This row renders N times where N = count(active_languages).          │  │
│  │    Each field gets unique key: site_name_{locale}                       │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 2B. Logo Upload (non-language field — single instance)

```
│  Logo / 标志                                                                   │
│  ┌─────────────────────────────────┐                                         │
│  │  [LOGO PREVIEW]                  │  Currently: logo_en.png                │
│  │     200×60px                     │  Size: 124KB | PNG                     │
│  │                                 │  [Change Logo] [Remove]                │
│  └─────────────────────────────────┘                                         │
│  Favicon                                                                          │
│  ┌─────────────────────────────────┐                                         │
│  │  [ICO]                          │  Currently: favicon.ico                │
│  │   32×32px                       │  [Change] [Remove]                    │
│  └─────────────────────────────────┘                                         │
```

### 2C. Footer / Copyright Fields (3 languages)

```
│  Copyright / 版权信息                                                              │
│  ┌─────────────────────────────────┐ ┌─────────────────────────────────┐ ┌─────────────────────┐  │
│  │ 🌐 English (EN)                 │ │ 🌐 简体中文 (ZH_CN)              │ │ 🌐 ไทย (TH)         │  │
│  ├─────────────────────────────────┤ ├─────────────────────────────────┤ ├─────────────────────┤  │
│  │                                 │ │                                 │ │                     │  │
│  │  © 2026 Hubei Horizon Intl.    │ │  © 2026 湖北纵衡国际人才有限公司 │ │  © 2026 บริษัท ฮูเปก │  │
│  │  All Rights Reserved.          │ │  版权所有                        │ │  ซิสฮอไรซัน อินเตอร์ │  │
│  │                                 │ │                                 │ │                     │  │
│  └─────────────────────────────────┘ └─────────────────────────────────┘ └─────────────────────┘  │
```

### 2D. Contact Information (mixed: some single, some per-language)

```
│  Contact / 联系方式                                                               │
│                                                                                    │
│  ┌────────────────────────────┐  ┌────────────────────────────┐                  │
│  │ 📧 Email                   │  │ 📞 Phone                   │                  │
│  │ ─────────────────────────  │  │ ─────────────────────────   │                  │
│  │ info@hbhr.com              │  │ +86 27-1234-5678           │                  │
│  │                            │  │                            │                  │
│  └────────────────────────────┘  └────────────────────────────┘                  │
│                                                                                    │
│  Address / 地址 (per language)                                                    │
│  ┌─────────────────────────────────┐ ┌─────────────────────────────────┐ ┌─────────────────────┐  │
│  │ 🌐 English (EN)                 │ │ 🌐 简体中文 (ZH_CN)              │ │ 🌐 ไทย (TH)         │  │
│  ├─────────────────────────────────┤ ├─────────────────────────────────┤ ├─────────────────────┤  │
│  │                                 │ │                                 │ │                     │  │
│  │  Building 8, Business Center   │ │  湖北省武汉市光谷大道8号         │ │  อาคาร 8 ศูนย์ธุรกิจ  │  │
│  │  Guanggu Avenue, Wuhan         │ │  光谷中心                        │ │  ถนนกวงกู อู่ฮั่น     │  │
│  │                                 │ │                                 │ │                     │  │
│  └─────────────────────────────────┘ └─────────────────────────────────┘ └─────────────────────┘  │
```

---

## 3. SEO Tab — Meta Data Fields (per language)

### 3A. Meta Title (3 languages → 3 field rows)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│  WEBSITE SETTINGS  |  [General]  [SEO ●]  [Social]                             │
│  ────────────────────────────────────────────────────────────────────────────  │
│                                                                                 │
│  SECTION: SEO / 搜索引擎优化                                                     │
│  ────────────────────────────────────────────────────────────────────────────  │
│                                                                                 │
│  Meta Title / 元标题 / ชื่อเรื่อง                                               │
│  ┌─────────────────────────────────┐ ┌─────────────────────────────────┐ ┌─────────────────────┐  │
│  │ 🌐 English (EN)                 │ │ 🌐 简体中文 (ZH_CN)              │ │ 🌐 ไทย (TH)         │  │
│  ├─────────────────────────────────┤ ├─────────────────────────────────┤ ├─────────────────────┤  │
│  │                                 │ │                                 │ │                     │  │
│  │  Hubei Horizon International   │ │  湖北纵衡国际人才 — 专业人力     │ │  ฮูเปกซิสฮอไรซัน     │  │
│  │  - Premier HR Solutions        │ │  资源服务                      │ │  นานาชาติ           │  │
│  │                                 │ │                                 │ │                     │  │
│  │  Characters: 42/60             │ │  Characters: 18/60              │ │  Characters: 21/60   │  │
│  └─────────────────────────────────┘ └─────────────────────────────────┘ └─────────────────────┘  │
│                                                                                 │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │ ⚠️  Each language field has independent character counter.              │  │
│  │     Max recommended: 60 characters. Title tag renders per locale.       │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 3B. Meta Description (3 languages → 3 textarea rows)

```
│  Meta Description / 元描述 / คำอธิบาย                                         │
│  ┌─────────────────────────────────┐ ┌─────────────────────────────────┐ ┌─────────────────────┐  │
│  │ 🌐 English (EN)                 │ │ 🌐 简体中文 (ZH_CN)              │ │ 🌐 ไทย (TH)         │  │
│  ├─────────────────────────────────┤ ├─────────────────────────────────┤ ├─────────────────────┤  │
│  │                                 │ │                                 │ │                     │  │
│  │  Connecting global talent with  │ │  连接全球人才与中国顶尖企业。   │ │  เชื่อมต่อบุคลากร    │  │
│  │  world-class opportunities.     │ │  专业招聘、签证办理、人力资源   │ │  ทั่วโลกกับโอกาส     │  │
│  │  Professional recruitment,     │ │  解决方案。                    │ │  ชั้นเยี่ยม          │  │
│  │  visa processing, HR solutions. │ │                                 │ │                     │  │
│  │                                 │ │                                 │ │                     │  │
│  │  Characters: 138/160           │ │  Characters: 42/160            │ │  Characters: 48/160 │  │
│  └─────────────────────────────────┘ └─────────────────────────────────┘ └─────────────────────┘  │
│                                                                                 │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │ 💡 Each field stores to: seo_meta_description_{locale}                  │  │
│  │    Google displays ~155-160 chars.                                      │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
```

### 3C. Keywords (single field, comma-separated — no per-language split)

```
│  Keywords / 关键词 / คำหลัก                                                    │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                         │  │
│  │  HR solutions, recruitment, China jobs, visa services, talent          │  │
│  │  acquisition, 湖北人力资源, 武汉就业, 留学中国, thai chinese recruitment  │  │
│  │                                                                         │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│  💡 Single field. Stored in `seo_keywords` (JSON per locale if needed).    │
│                                                                                 │
│  OG Image / 开放图                                                                 │
│  ┌─────────────────────────────────┐                                         │
│  │  [OG PREVIEW 1200×630]          │  Currently: og-default.png             │
│  │         Preview                 │  Size: 342KB | PNG, 1200×630px         │
│  │                                 │  [Change] [Remove]                     │
│  └─────────────────────────────────┘                                         │
│  💡 Single image for all languages. Shown when link shared on social.      │
```

---

## 4. Social Tab

### Social Links (mixed: some global, some per-language)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│  WEBSITE SETTINGS  |  [General]  [SEO]  [Social ●]                             │
│  ────────────────────────────────────────────────────────────────────────────  │
│                                                                                 │
│  SECTION: Social Links / 社交媒体                                              │
│  ────────────────────────────────────────────────────────────────────────────  │
│                                                                                 │
│  WeChat / 微信                                                    [global]      │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │  HorizonHR_Wuhan                                           [QR Code: 📷]  │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  WhatsApp / WhatsApp                                          [global]          │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │  +86 27-8888-8888                                                    📱  │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  Line / LINE                                                    [global]       │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │  @HorizonHR                                                      📱       │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  Facebook                                                        [global]       │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │  https://facebook.com/horizonhrintl                              🌐       │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  LinkedIn                                                        [global]       │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │  https://linkedin.com/company/horizonhr                                🌐       │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  ┌──────────────────────────────────────────────────────────────────────────┐  │
│  │ 💡 Social links are global (same URL across all languages).            │  │
│  │    If per-language social pages needed, render as per-language rows.    │  │
│  └──────────────────────────────────────────────────────────────────────────┘  │
```

---

## 5. Field Count Dynamics — Side-by-Side Comparison

### Scenario A: 3 Active Languages (EN, ZH_CN, TH)

```
┌─────────────────────────────────────────────────────────────────┐
│  Language Settings: EN  ZH_CN  TH  (3 total)                   │
├─────────────────────────────────────────────────────────────────┤
│  General Tab                                                    │
│  ├─ Site Name        → 3 fields (one per locale)              │
│  ├─ Logo             → 1 field (single instance)              │
│  ├─ Copyright        → 3 fields (one per locale)             │
│  └─ Contact Address  → 3 fields (one per locale)              │
│                                                                 │
│  SEO Tab                                                        │
│  ├─ Meta Title       → 3 fields (one per locale)              │
│  ├─ Meta Description → 3 fields (one per locale)              │
│  └─ Keywords         → 1 field  (global)                      │
│                                                                 │
│  Social Tab                                                     │
│  └─ Social Links    → 5 fields (global, not per-locale)      │
│                                                                 │
│  TOTAL per-language fields: 14                                  │
│  TOTAL global fields:      7                                    │
└─────────────────────────────────────────────────────────────────┘
```

### Scenario B: 4 Active Languages (EN, ZH_CN, TH, JA)

```
┌──────────────────────────────────────────────────────────────────┐
│  Language Settings: EN  ZH_CN  TH  JA  (4 total)                │
├──────────────────────────────────────────────────────────────────┤
│  General Tab                                                     │
│  ├─ Site Name        → 4 fields ⬆ [+1 new Japanese field]      │
│  ├─ Logo             → 1 field  (unchanged)                     │
│  ├─ Copyright        → 4 fields ⬆ [+1 new Japanese field]      │
│  └─ Contact Address  → 4 fields ⬆ [+1 new Japanese field]      │
│                                                                  │
│  SEO Tab                                                         │
│  ├─ Meta Title       → 4 fields ⬆ [+1 new Japanese field]       │
│  ├─ Meta Description → 4 fields ⬆ [+1 new Japanese field]       │
│  └─ Keywords         → 1 field  (unchanged)                     │
│                                                                  │
│  Social Tab                                                      │
│  └─ Social Links    → 5 fields (unchanged)                      │
│                                                                  │
│  TOTAL per-language fields: 17  (+3 from Scenario A)            │
│  TOTAL global fields:      7                                    │
└──────────────────────────────────────────────────────────────────┘
```

---

## 6. UI Component: Language Indicator Row

Each per-language group renders with a language indicator:

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│  🌐 = Language selector badge (colored per locale)                            │
│                                                                                 │
│  EN  → Blue badge   (#003366 text, #E6F0FF background)                        │
│  ZH_CN → Red badge   (#CC0000 text, #FFE6E6 background)                       │
│  TH  → Green badge  (#1A5276 text, #D5F5E3 background)                        │
│  JA  → Orange badge (#E67E22 text, #FDEBD0 background)                        │
│                                                                                 │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐                      │
│  │  🌐 EN   │  │ 🌐 ZH_CN │  │  🌐 TH   │  │  🌐 JA   │                      │
│  ├──────────┤  ├──────────┤  ├──────────┤  ├──────────┤                      │
│  │ [field]  │  │ [field]  │  │ [field]  │  │ [field]  │                      │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘                      │
│                                                                                 │
│  When a language is deactivated in language_settings:                         │
│  → That language's row is REMOVED (not hidden) — saves DB space               │
│  → Re-activating restores the row with empty value                           │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 7. Data Model Reference

```
Table: settings
─────────────────────────────────────────────────────────────

Column               │ Type       │ Description
─────────────────────┼────────────┼─────────────────────────────────────
site_name_en         │ VARCHAR    │ Site name in English
site_name_zh_cn      │ VARCHAR    │ Site name in Simplified Chinese
site_name_th         │ VARCHAR    │ Site name in Thai
site_name_ja         │ VARCHAR    │ Site name in Japanese (nullable)

logo_url             │ VARCHAR    │ Logo file path (global)
favicon_url          │ VARCHAR    │ Favicon file path (global)

copyright_en         │ VARCHAR    │ Copyright text in English
copyright_zh_cn      │ VARCHAR    │ Copyright in Chinese
copyright_th         │ VARCHAR    │ Copyright in Thai
copyright_ja         │ VARCHAR    │ Copyright in Japanese (nullable)

contact_email        │ VARCHAR    │ Global
contact_phone        │ VARCHAR    │ Global
contact_address_en   │ TEXT       │ Address in English
contact_address_zh_cn│ TEXT       │ Address in Chinese
contact_address_th   │ TEXT       │ Address in Thai
contact_address_ja   │ TEXT       │ Address in Japanese (nullable)

seo_meta_title_en    │ VARCHAR    │
seo_meta_title_zh_cn │ VARCHAR    │ Per-language SEO fields
seo_meta_title_th    │ VARCHAR    │
seo_meta_title_ja    │ VARCHAR    │ (nullable — only if language active)

seo_meta_desc_en     │ TEXT       │
seo_meta_desc_zh_cn  │ TEXT       │
seo_meta_desc_th     │ TEXT       │
seo_meta_desc_ja     │ TEXT       │ (nullable)

seo_keywords         │ TEXT       │ Global (comma-separated)
og_image_url         │ VARCHAR    │ Global

social_wechat        │ VARCHAR    │ Global
social_whatsapp      │ VARCHAR    │ Global
social_line          │ VARCHAR    │ Global
social_facebook      │ VARCHAR    │ Global
social_linkedin      │ VARCHAR    │ Global

─────────────────────────────────────────────────────────────
Language activation controlled by: language_settings.active_languages (JSON array)
Columns for inactive languages remain NULL — not used by frontend
─────────────────────────────────────────────────────────────
```

---

## 8. Acceptance Criteria Summary

| Criteria | Status |
|----------|--------|
| Mockup แสดง 3 fields เมื่อมี 3 ภาษา (EN, ZH_CN, TH) | ✅ ดู Section 5, Scenario A |
| Mockup แสดง 4 fields เมื่อเพิ่ม JA | ✅ ดู Section 5, Scenario B |
| General tab: Site Name, Logo, Footer, Contact | ✅ ดู Section 2 |
| SEO tab: Meta Title, Meta Description, Keywords | ✅ ดู Section 3 |
| Social tab | ✅ ดู Section 4 |
| Format: Markdown + ASCII diagram | ✅ This document |
| Dev reference: data model in Section 7 | ✅ |

---

**File:** `DOCUMENTS/DESIGNS/MOCKUP_SETTINGS_MULTI_LANGUAGE.md`
**Status:** ✅ Completed