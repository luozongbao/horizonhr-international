# TASK-038: Frontend Admin Settings & Language Management

**Phase:** 11 — Frontend: Admin Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-008, TASK-009  
**Priority:** MEDIUM  

---

## Objective

Implement the admin Settings page (site configuration, SMTP, social OAuth credentials, file limits) and the Language Management page (enable/disable languages + manage translation keys).

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/admin-settings.html` — Settings page mockup
2. `DOCUMENTS/DESIGNS/visual-mockups/admin-language-settings.html` — Language settings mockup
3. `DOCUMENTS/DESIGNS/MOCKUP_SETTINGS_MULTI_LANGUAGE.md` — Multi-language settings design spec (PRIMARY)
4. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.2 (Settings), Section 3.3 (Language/Translations)
5. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.8 (Admin: Settings), IV.B.9 (Language Management)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/admin/settings` | Get all settings |
| PUT | `/api/admin/settings` | Update settings (bulk) |
| POST | `/api/admin/settings/upload-logo` | Upload site logo |
| POST | `/api/admin/settings/upload-favicon` | Upload favicon |
| POST | `/api/admin/settings/test-smtp` | Test SMTP connection |
| GET | `/api/admin/languages` | List languages |
| PUT | `/api/admin/languages/{id}` | Update language (enable/disable, set default) |
| GET | `/api/admin/translations` | Get translation keys |
| PUT | `/api/admin/translations` | Update translation keys |
| POST | `/api/admin/translations/import` | Import translations (JSON file) |
| GET | `/api/admin/translations/export` | Export translations (JSON download) |

---

## Deliverables

### Admin Settings Page
**`frontend/src/views/admin/SettingsPage.vue`**

Reference: `DOCUMENTS/DESIGNS/MOCKUP_SETTINGS_MULTI_LANGUAGE.md` for the multi-language tab layout design.

Use `el-tabs` with vertical tabs (or horizontal) for setting categories:

**Tab 1: General**
- Site Name (multi-language: EN/ZH_CN/TH sub-tabs)
- Site Logo (upload: `el-upload` + preview)
- Site Favicon (upload: icon only, accept .ico, .png)
- Contact Email
- Contact Phone
- Address (multi-language)
- Social Links: Facebook URL, Instagram URL, LinkedIn URL, WeChat QR Image

**Tab 2: SMTP Email**
- SMTP Host
- SMTP Port (number)
- Encryption (none / tls / ssl — `el-select`)
- SMTP Username
- SMTP Password (password input with reveal)
- From Address
- From Name
- "Test SMTP Connection" button → calls `POST /api/admin/settings/test-smtp` → show success/fail toast
- Save button (saves only SMTP settings group)

**Tab 3: Social OAuth**
- Google Client ID + Secret
- Facebook App ID + Secret
- LinkedIn Client ID + Secret
- WeChat App ID + Secret
- All password-type inputs with reveal toggles
- Save button

**Tab 4: File & Upload Limits**
- Max Resume File Size (MB): number input (default 20)
- Max Avatar File Size (MB): number input (default 5)
- Allowed Resume Types: checkboxes (pdf, doc, docx, jpg, png)
- Allowed Avatar Types: checkboxes (jpg, png, webp)
- Save button

**Save behavior:** Each tab has its own "Save" button to avoid accidentally overwriting other settings. Each save calls `PUT /api/admin/settings` with only the relevant keys.

### Admin Language Management Page
**`frontend/src/views/admin/LanguagesPage.vue`**

Reference: `visual-mockups/admin-language-settings.html`

Section 1: Language Enable/Disable
- Table: Language Name, Code, Status (toggle), Default (radio), Last Updated
- Toggle status: `PUT /api/admin/languages/{id}` with `{ is_enabled: true/false }`
- Set Default: `PUT /api/admin/languages/{id}` with `{ is_default: true }` (only one language can be default)

Section 2: Translation Key Editor
- Language selector: dropdown (EN / ZH_CN / TH)
- Search/filter by key
- Table: Key, Value (editable inline), Last Updated
- Inline edit: click "Edit" → cell becomes input → "Save" saves that single key
- Bulk export: "Export JSON" button → downloads `translations_{lang}.json`
- Bulk import: "Import JSON" button → file picker → `POST /api/admin/translations/import`

---

## API Module Additions (to `admin.js`)
```js
export const adminApi = {
  // Settings
  getSettings(),
  updateSettings(data),
  uploadLogo(formData),
  uploadFavicon(formData),
  testSmtp(),
  
  // Languages
  getLanguages(),
  updateLanguage(id, data),
  
  // Translations
  getTranslations(lang),
  updateTranslations(data),
  importTranslations(lang, formData),
  exportTranslations(lang),
}
```

---

## i18n Keys to Add

```json
"adminSettings": {
  "pageTitle": "System Settings",
  "tabs": {
    "general": "General",
    "smtp": "Email (SMTP)",
    "oauth": "Social OAuth",
    "uploads": "File Uploads"
  },
  "siteName": "Site Name",
  "logo": "Site Logo",
  "favicon": "Favicon",
  "smtpHost": "SMTP Host",
  "smtpPort": "SMTP Port",
  "encryption": "Encryption",
  "smtpUsername": "Username",
  "smtpPassword": "Password",
  "fromAddress": "From Address",
  "fromName": "From Name",
  "testSmtp": "Test SMTP",
  "smtpTestSuccess": "SMTP connection successful!",
  "smtpTestFailed": "SMTP connection failed: {error}"
},
"adminLanguages": {
  "pageTitle": "Language Management",
  "languages": "Languages",
  "translations": "Translations",
  "enable": "Enable",
  "disable": "Disable",
  "setDefault": "Set as Default",
  "exportJson": "Export JSON",
  "importJson": "Import JSON",
  "key": "Translation Key",
  "value": "Value",
  "searchKey": "Search key..."
}
```

---

## Acceptance Criteria

- [ ] Settings page loads current settings from API and pre-fills all fields
- [ ] Each settings tab saves independently
- [ ] Site logo upload works and shows preview
- [ ] Favicon upload works
- [ ] SMTP test shows success/failure toast with clear message
- [ ] Social OAuth credentials save (with password reveal toggle)
- [ ] Upload limits save correctly
- [ ] Language list shows all 3 languages with enable/disable toggles
- [ ] Default language can be changed (only one at a time)
- [ ] Translation key editor loads keys for selected language
- [ ] Inline edit saves single translation key
- [ ] Export JSON downloads correct file
- [ ] Import JSON uploads and refreshes translation list
- [ ] Multi-language site name tabs show per-language values
- [ ] All text via i18n

---

## Notes

- Settings page: read `DOCUMENTS/DESIGNS/MOCKUP_SETTINGS_MULTI_LANGUAGE.md` for the exact multi-language tab layout design — this document describes how multi-language settings should be presented in the UI
- SMTP password: use `el-input show-password` for reveal toggle
- OAuth secrets: same reveal toggle approach
- Translation editor: for MVP, load all keys at once (no virtual scroll) — keys are typically < 500
- Import: validate JSON structure before uploading — must be `{ "key.path": "value" }` flat object
