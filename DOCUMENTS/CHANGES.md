# DOCUMENTS Change Log
## HorizonHR International Talent Service (HRINT)

**Date:** 2026-05-02  
**Reviewed Against:** DOCUMENTS/REQUIREMENTS-EN.md (HRINT-DOC-001)

---

## Summary

After reviewing all design documents against `REQUIREMENTS-EN.md`, the following gaps and inconsistencies were identified and corrected.

---

## 1. DOCUMENTS/DESIGNS/API_DOCUMENTATION.md

### 1.1 Fixed: Duplicate Section Numbering in Authentication (Section 2)

**Problem:** Two sections were labeled `2.5` — "Reset Password" and "Social Authentication".

**Fix:** Renamed Social Authentication section from `2.5` to `2.6`, and its sub-sections from `2.5.1/2.5.2/2.5.3` to `2.6.1/2.6.2/2.6.3`.

---

### 1.2 Added: Section 2.7 Confirm Email Endpoint

**Problem:** `POST /api/auth/confirm-email` was referenced in the System Design's Traceability Matrix (`IV.C.3 - Registration Workflow - Email Confirm`, table `email_confirmations`) but was completely absent from the API documentation.

**Requirement:** REQ IV.C.3 — "Confirm Email on registration: Student User activate account on email confirmed"

**Fix:** Added `POST /api/auth/confirm-email` endpoint documentation with request body (token, email), success response, enterprise-specific note, and error response.

---

### 1.3 Fixed: Language Settings vs. Translations Endpoint Naming (Section 3.2 → 3.2 + 3.3)

**Problem:** The original section 3.2 ("Languages") used `/api/languages` for **both**:
- Managing active site languages (code, name, is_active, position) — the `language_settings` table
- Managing translation key-value pairs — the `languages` table

This was inconsistent with:
- `SYSTEM_DESIGN.md` Section 3.3 (Feature-to-API table), which clearly separates `/api/languages` (Language Settings) from `/api/translations` (Translation Keys)
- `DESIGN_SYSTEM.md` and `MOCKUP_SETTINGS_MULTI_LANGUAGE.md`, which state "Frontend calls `GET /api/languages` to get the list of active languages"

**Fix:** Split old section 3.2 into two distinct sections:

- **3.2 Language Settings** (`/api/languages`): CRUD for managing which languages are active — `GET`, `POST`, `PUT /{code}`, `DELETE /{code}`. Response shape matches `language_settings` table (code, name, native_name, flag, is_active, position). Added validation note: default language (`en`) cannot be deleted.
- **3.3 Translations** (`/api/translations`): CRUD for translation key-value pairs — `GET`, `GET /{key}`, `POST`, `PUT /{key}`, `DELETE /{key}`, `GET /export`, `POST /import`. Responses match `languages` table (key, zh_cn, en, th).

---

### 1.4 Re-numbered Sections 3.3–3.11 → 3.4–3.12

Due to the insertion of new section 3.3 (Translations), all subsequent sections were renumbered:

| Old Number | New Number | Section |
|-----------|-----------|---------|
| 3.3 | 3.4 | Settings |
| 3.4 | 3.5 | Pages |
| 3.5 | 3.6 | Posts |
| 3.6 | 3.7 | Resumes |
| 3.7 | 3.8 | Jobs |
| 3.8 | 3.9 | Applications |
| 3.9 | 3.10 | Interviews |
| 3.10 | 3.11 | Seminars |
| 3.11 | 3.12 | Universities |

---

### 1.5 Added: Settings File Upload and SMTP Test Endpoints (Section 3.4)

**Problem:** Three endpoints were defined in `SYSTEM_DESIGN.md` Module 2.3.8 (Settings Module) and Traceability Matrix, but not documented in the API documentation:

| Missing Endpoint | Requirement |
|-----------------|-------------|
| `POST /api/settings/test-smtp` | REQ Settings — "SMTP/Sendmail Credentials for send email methods" |
| `POST /api/settings/upload-logo` | REQ Settings — "Able to Set all website Logo" |
| `POST /api/settings/upload-favicon` | REQ Settings — "Able to Set all website...Favicon" |

**Fix:** Added all three endpoints with full request/response documentation (file constraints, success/error responses).

---

### 1.6 Added: Interview Cancel and Join Endpoints (Section 3.10)

**Problem:** Two endpoints listed in `SYSTEM_DESIGN.md` Traceability Matrix were missing from API documentation:

| Missing Endpoint | Requirement |
|-----------------|-------------|
| `PUT /api/interviews/{id}/cancel` | REQ IV.B.1 Admin — "Create, schedule, cancel interviews" |
| `POST /api/interviews/{id}/join` | REQ IV.C.2 — "Student clicks link to join without login" |

**Fix:** Added both endpoints:
- `PUT /api/interviews/{id}/cancel` — with optional `reason` field (sent to student)
- `POST /api/interviews/{id}/join` — supports both JWT auth (admin/enterprise) and unauthenticated student access via `X-Room-Token` header (room token from invitation link). Returns WebRTC config (ICE servers, WebSocket URL).

---

### 1.7 Added: Seminar Recording Endpoint (Section 3.11)

**Problem:** `GET /api/seminars/{id}/recording` was referenced in Section 5.8 (Common Patterns: Playback Speed Control) but never formally documented as an API endpoint.

**Requirement:** REQ IV.C.3 — "Playback: Auto-generated, timeline scrubbing, 0.5x-2x speed, permanent storage"

**Fix:** Added `GET /api/seminars/{id}/recording` endpoint with full response schema including `playback_speeds`, `default_speed`, `video_url`, `duration_sec`, `view_count`.

---

### 1.8 Added: Admin Statistics Endpoint (Section 3.13)

**Problem:** No API endpoint existed for admin dashboard statistics despite the requirement explicitly listing it.

**Requirement:** REQ IV.B.1 — "Data statistics: Visitors, resume uploads, interviews, seminar views"

**Fix:** Added `GET /api/admin/stats` endpoint (Admin only) with `period` query parameter (7d/30d/90d/1y/all). Returns aggregate counts for users, resumes, interviews, seminars, and jobs.

---

### 1.9 Added: Contact Form Endpoint (Section 3.14)

**Problem:** No API endpoint existed for the contact/consultation form despite the requirement specifying it.

**Requirement:** REQ IV.A.8 (Contact Us) — "Online consultation form: Name, contact, content; submission triggers notification"

**Fix:** Added `POST /api/contact` endpoint (public, no auth required) with fields: name, email, phone, subject, message. Returns submission confirmation. Note: triggers admin email notification.

---

### 1.10 Added: New Error Codes

Added missing error codes to Section 4.2:
- `INVALID_PROVIDER` — Unsupported social authentication provider
- `SMTP_CONNECTION_FAILED` — Cannot connect to configured SMTP server
- `CANNOT_DELETE_DEFAULT` — Cannot delete the default language
- `INTERVIEW_NOT_JOINABLE` — Interview is not currently open for joining

---

## 2. DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md

### 2.1 Fixed: Duplicate Module Number 2.3.2

**Problem:** Two modules were numbered `2.3.2`:
- First `2.3.2`: Social Authentication Module (correct)
- Second `2.3.2`: CMS Module (incorrect duplicate)

**Fix:** Renumbered the CMS Module and all subsequent modules:

| Old Number | New Number | Module |
|-----------|-----------|--------|
| 2.3.2 (2nd) | 2.3.3 | CMS Module |
| 2.3.3 | 2.3.4 | Student Module |
| 2.3.4 | 2.3.5 | Enterprise Module |
| 2.3.5 | 2.3.6 | Interview Module (WebRTC) |
| 2.3.6 | 2.3.7 | Seminar Module |
| 2.3.7 | 2.3.8 | Settings Module |
| 2.3.8 | 2.3.9 | Language Module |

---

### 2.2 Fixed: ER Diagram — Duplicate `user_id (FK)` in Students Table

**Problem:** The ASCII ER Diagram (Section 1.1) showed `user_id (FK)` listed twice in the `students` table box. The actual SQL definition in Section 1.2.2 is correct (appears only once).

**Fix:** Replaced the duplicate `user_id (FK)` line in the ER diagram with the missing `bio` field (which exists in the SQL definition as `bio TEXT NULL` but was absent from the diagram).

---

### 2.3 Added: Contacts Table (Section 1.2.22)

**Problem:** The contact form requirement (REQ IV.A.8) had no corresponding database table in the System Design.

**Fix:** Added `1.2.22 Contacts Table` with fields: id, name, email, phone, subject, message, ip_address, status (unread/read/replied), replied_at, created_at, updated_at.

---

### 2.4 Updated: Language Module (2.3.9) and Added Contact Module (2.3.10)

**Problem:** 
- Language Module (2.3.9) only mentioned `LanguageController` without clarifying the split between language settings management and translation key management.
- No Contact Module existed despite the contact form requirement.

**Fix:**
- Updated **2.3.9 Language Module** to clarify the two controllers: `LanguageController` (for `/api/languages` — language settings) and `TranslationController` (for `/api/translations` — translation keys).
- Added **2.3.10 Contact Module** with `ContactController`, its endpoint (`POST /api/contact`), and dependencies (Contacts Model, EmailService, Settings Model).

---

### 2.5 Updated: Traceability Matrix (Section 3.1)

Added two missing rows:
- `IV.A.8 Contact Form Submission` → contacts table → `POST /api/contact` → ContactController
- `IV.B.1 Admin Account - Statistics` → All tables → `GET /api/admin/stats` → StatsController

### 2.6 Updated: Feature-to-API Endpoint Mapping (Section 3.3)

Added two new sections at the end of the mapping table:
- **Contact**: `POST /api/contact` — Submit contact/consultation form
- **Admin Statistics**: `GET /api/admin/stats` — Dashboard statistics (admin only)

---

## 3. Documents Not Changed

The following documents were reviewed and found consistent with requirements:

| Document | Status |
|---------|--------|
| `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` | ✅ Complete and consistent |
| `DOCUMENTS/DESIGNS/MOCKUP_SETTINGS_MULTI_LANGUAGE.md` | ✅ Complete and consistent |

---

## 4. Requirements Coverage Summary

All requirements in `REQUIREMENTS-EN.md` are now traceable to design components:

| Requirement Area | Coverage |
|-----------------|---------|
| Frontend Display Modules (IV.A) | ✅ All 8 modules covered including Contact Us with contact form endpoint |
| Admin Account (IV.B.1) | ✅ All functions covered including statistics dashboard |
| Student Account (IV.B.2) | ✅ Fully covered |
| Enterprise Account (IV.B.3) | ✅ Fully covered |
| Social Authentication (IV.B.4) | ✅ Fully covered (2.6.1–2.6.3) |
| Resume Upload (IV.C.1) | ✅ Fully covered |
| Video Interview (IV.C.2) | ✅ Fully covered including join without login |
| Online Seminar (IV.C.3) | ✅ Fully covered including recording playback |
| Email Confirmation (IV.C.3) | ✅ Added (was missing) |
| CMS (IV.C) | ✅ Fully covered |
| Settings (IV.C) | ✅ Fully covered including logo, favicon, SMTP test |
| Multi-language Support | ✅ Properly split: /api/languages (settings) + /api/translations (keys) |

---

_Review completed: 2026-05-02_

---

## 5. Frontend Tech Stack Update (2026-05-02)

### Change Request

The client removed the original frontend constraint of "HTML5 + CSS3 + JavaScript" and requested a modern framework recommendation.

### Recommendation & Decision

**Frontend Tech Stack: Vue 3 SPA**

| Component | Technology | Notes |
|-----------|------------|-------|
| **Framework** | Vue 3 (Composition API) | SPA |
| **Build Tool** | Vite | Fast HMR, optimized builds |
| **Routing** | Vue Router 4 | SPA routing |
| **State Management** | Pinia | Auth + app state |
| **HTTP Client** | Axios | REST API calls |
| **UI Library** | Element Plus | Forms, tables, dialogs; built-in EN/ZH locale |
| **Internationalization** | Vue i18n | EN / ZH_CN / TH support |
| **Styling** | Tailwind CSS | Utility-first responsive layout |
| **Icons** | Element Plus Icons | Consistent icon set |

**Rationale:**
- **Element Plus** — purpose-built Vue 3 component library; covers all admin/enterprise/student portal needs (data tables, forms, dialogs, date pickers); built-in Chinese and English locales
- **Vue i18n** — required for the multi-language requirement (EN/ZH_CN/TH); supports runtime language switching
- **Tailwind CSS** — utility-first approach for responsive layouts and custom brand styling (Dark Blue #003366 + White); works alongside Element Plus
- **Vite** — fast builds and HMR vs. Webpack; tree-shaking keeps bundle lean

### Documents Updated

| Document | Change |
|----------|--------|
| `DOCUMENTS/REQUIREMENTS-EN.md` | Section VI: replaced `HTML5 + CSS3 + JavaScript` with `Vue 3 SPA (Vite build tool)` |
| `DOCUMENTS/REQUIREMENTS-EN.md` | Section IX.A: expanded from single line to full tech stack table with rationale |
| `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` | Architecture diagram Frontend Layer: replaced `Static Files (HTML5 + CSS3 + JS)` with `Vue 3 SPA (Vite + Vue Router + Pinia + Element Plus)` |
| `DOCUMENTS/SOLUTION.md` | Section 7.1: updated UI row from `Custom CSS` to `Element Plus`; added `Vue i18n` and `Tailwind CSS` rows |
