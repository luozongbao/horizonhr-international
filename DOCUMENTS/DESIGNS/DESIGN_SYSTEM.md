# HRINT Design System Document
## HorizonHR International Talent Service

**Document ID:** HRINT-DOC-070
**Project:** HorizonHR International Talent Service (HRINT)
**Date:** 2026-04-25
**Status:** Complete

---

## Table of Contents
1. [Design Principles](#1-design-principles)
2. [Dynamic Form Fields Pattern](#2-dynamic-form-fields-pattern)
3. [Multi-Language Settings Integration](#3-multi-language-settings-integration)

---

## 1. Design Principles

### 1.1 Multi-Language Field Design

The system supports **dynamic multi-language fields** — fields that have distinct values per active language. Unlike static column-based multi-language (e.g., `title_en`, `title_zh_cn` as fixed columns), the system uses a **key-based approach** where:

- Each language variant is a separate setting key: `{field}_{lang_code}`
- New languages can be added without schema changes
- Frontend loops through active languages to render form fields

**Key Properties:**
- Language-agnostic base field names: `site_name`, `seo_title`, `contact_address`
- Language-specific keys: `site_name_en`, `site_name_zh_cn`, `site_name_th`
- Language codes follow ISO/i18n convention: `en`, `zh_cn`, `th`, `ja`, etc.

---

## 2. Dynamic Form Fields Pattern

### 2.1 Definition

**Dynamic Form Fields** are UI form components that automatically adapt to the currently active languages configured in the system. Instead of hardcoding form fields for 3 languages, the form renders N sets of fields — one per active language.

### 2.2 When to Use

Use this pattern when:
- Building settings/admin forms with translatable text fields
- CMS content forms (pages, posts) with multi-language titles, descriptions, meta
- Any form where a user needs to enter language-specific content

Do NOT use this pattern for:
- Fixed-choice dropdowns or radio buttons
- Language-agnostic fields (IDs, timestamps, file uploads)

### 2.3 Component Structure

```
Language-aware Form
├── Language Selector (from /api/languages)
└── Dynamic Field Groups
    ├── Field Group: site_name
    │   ├── [site_name_en] Input (English)
    │   ├── [site_name_zh_cn] Input (简体中文)
    │   └── [site_name_th] Input (ภาษาไทย)
    ├── Field Group: seo_title
    │   ├── [seo_title_en] Input (English)
    │   ├── [seo_title_zh_cn] Input (简体中文)
    │   └── [seo_title_th] Input (ภาษาไทย)
    └── ...
```

### 2.4 Implementation Flow

```
┌─────────────────────────────┐
│  1. Fetch Active Languages  │
│     GET /api/languages      │
└──────────────┬──────────────┘
               ▼
┌─────────────────────────────┐
│  2. Filter is_active=true   │
│     Sort by position ASC    │
└──────────────┬──────────────┘
               ▼
┌─────────────────────────────┐
│  3. Render form fields for  │
│     each (field × language) │
└──────────────┬──────────────┘
               ▼
┌─────────────────────────────┐
│  4. On submit, send all     │
│     {field}_{lang_code}     │
│     keys in one batch       │
└─────────────────────────────┘
```

### 2.5 Key Naming Convention

| Base Field | en | zh_cn | th |
|------------|-----|-------|-----|
| site_name | site_name_en | site_name_zh_cn | site_name_th |
| seo_title | seo_title_en | seo_title_zh_cn | seo_title_th |
| seo_description | seo_description_en | seo_description_zh_cn | seo_description_th |
| contact_address | contact_address_en | contact_address_zh_cn | contact_address_th |

### 2.6 Validation Rules

- **Required per language:** Each `{field}_{lang_code}` can be independently required or optional
- **Cross-language consistency:** No enforced relationship between language variants (e.g., `site_name_en` can be empty while `site_name_zh_cn` has a value)
- **Max length:** VARCHAR(500) for short text, TEXT for long content

---

## 3. Multi-Language Settings Integration

### 3.1 Backend API Contract

**GET /api/languages** — Returns list of enabled languages
```json
[
  { "code": "en", "name": "English", "native_name": "English", "flag": "🇬🇧", "is_active": true, "position": 1 },
  { "code": "zh_cn", "name": "中文简体", "native_name": "简体中文", "flag": "🇨🇳", "is_active": true, "position": 2 },
  { "code": "th", "name": "ภาษาไทย", "native_name": "ภาษาไทย", "flag": "🇹🇭", "is_active": true, "position": 3 }
]
```

**GET /api/settings** — Returns all settings (grouped)
```json
{
  "website": {
    "site_name": "HorizonHR",
    "site_name_en": "HorizonHR International Talent Service",
    "site_name_zh_cn": "湖北豪睿国际人才服务有限公司",
    "site_name_th": "บริการบุคลากรระหว่างประเทศ HorizonHR"
  }
}
```

### 3.2 Frontend Responsibilities

1. **Always fetch languages first** before rendering multi-language form fields
2. **Use `is_active` filter** to only show enabled languages
3. **Sort by `position`** for consistent ordering across the UI
4. **Build field keys dynamically:** `${field}_${lang.code}`
5. **Handle missing translations gracefully** (fallback to empty string or default language value)

### 3.3 Data Flow Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                        Frontend                                   │
│                                                                   │
│   fetch('/api/languages')                                        │
│         │                                                        │
│         ▼                                                        │
│   languages = [en, zh_cn, th] (active, sorted)                   │
│         │                                                        │
│         ▼                                                        │
│   fetch('/api/settings')                                         │
│         │                                                        │
│         ▼                                                        │
│   settings object with keys:                                     │
│   { site_name_en, site_name_zh_cn, site_name_th, ... }          │
│         │                                                        │
│         ▼                                                        │
│   multiLangFields.forEach(field => {                             │
│     languages.forEach(lang => {                                 │
│       renderFormField(`${field}_${lang.code}`, settings[key])   │
│     })                                                           │
│   })                                                            │
└──────────────────────────────────────────────────────────────────┘
```

### 3.4 Reference Implementation (Vue.js)

```vue
<template>
  <div class="multi-lang-form">
    <div v-for="field in multiLangFields" :key="field" class="field-group">
      <label>{{ fieldLabels[field] }}</label>
      <div v-for="lang in activeLanguages" :key="lang.code" class="lang-field">
        <span class="lang-flag">{{ lang.flag }}</span>
        <input
          type="text"
          v-model="formData[`${field}_${lang.code}`]"
          :placeholder="`${field} (${lang.native_name})`"
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      activeLanguages: [],
      multiLangFields: ['site_name', 'seo_title', 'contact_address'],
      fieldLabels: {
        site_name: 'Site Name',
        seo_title: 'SEO Title',
        contact_address: 'Contact Address'
      },
      formData: {}
    };
  },
  async mounted() {
    // Step 1: Fetch active languages
    const langRes = await fetch('/api/languages');
    const langs = await langRes.json();
    this.activeLanguages = langs
      .filter(l => l.is_active)
      .sort((a, b) => a.position - b.position);

    // Step 2: Fetch settings
    const setRes = await fetch('/api/settings');
    const settings = await setRes.json();

    // Step 3: Build form data from settings
    this.multiLangFields.forEach(field => {
      this.activeLanguages.forEach(lang => {
        const key = `${field}_${lang.code}`;
        this.formData[key] = settings[key] || '';
      });
    });
  }
};
</script>
```

### 3.5 Design Decision Summary

| Aspect | Decision | Rationale |
|--------|----------|------------|
| Field key format | `{field}_{lang_code}` | Predictable, extensible, matches settings storage |
| Language discovery | Fetch `/api/languages` first | Frontend must know which languages to render |
| Missing value handling | Default to empty string | No hard fallback — admin fills all active languages |
| Field ordering | Sort by `language.position` ASC | Consistent ordering across all forms |
| Grouping | Group settings by `group` column | Natural separation: website, seo, smtp, social |

---

## Appendix: Quick Reference for Dev Team

### API Endpoints Used

| Purpose | Method | Endpoint |
|---------|--------|----------|
| Get active languages | GET | `/api/languages` |
| Get all settings | GET | `/api/settings` |
| Update settings | PUT | `/api/settings` |

### Key Format Examples

```
site_name_en        → Site Name (English)
site_name_zh_cn    → Site Name (Simplified Chinese)
site_name_th       → Site Name (Thai)
seo_title_en       → SEO Title (English)
og_description_zh_cn → OG Description (Simplified Chinese)
```

### Rules

1. Always fetch languages before rendering multi-language forms
2. Use `is_active=true` filter on language list
3. Sort languages by `position` ASC
4. Build field keys as `${baseField}_${langCode}`
5. On submit, include ALL language variants in the same request
6. Do NOT hardcode language codes — read from `/api/languages`

---

_This document captures design decisions for Dynamic Multi-Language Settings (HRINT-DOC-070)_
