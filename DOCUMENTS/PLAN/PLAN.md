# HRINT Development Plan
## HorizonHR International Talent Service

**Document ID:** HRINT-PLAN-001  
**Date:** 2026-05-02  
**Status:** Active  
**Total Tasks:** 43

---

## Project Overview

Build the HorizonHR International Talent Service (HRINT) platform — a professional international talent service website connecting Southeast Asian students studying in China with Chinese enterprises.

**Three Core Functions:**
1. Student Resume Upload & Talent Pool
2. Online 1-on-1 Video Interview (TRTC/WebRTC)
3. Online Seminar (Live + Playback, 10,000+ concurrent viewers)

**Three User Roles:** Admin · Student · Enterprise

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend Framework** | Laravel 10 (PHP 8.2) |
| **Database** | MariaDB 10.11 |
| **Cache / Queue** | Redis |
| **Frontend Framework** | Vue 3 (Composition API) + Vite |
| **UI Library** | Element Plus |
| **i18n** | Vue i18n (EN / ZH_CN / TH) |
| **Styling** | Tailwind CSS |
| **State Management** | Pinia |
| **HTTP Client** | Axios |
| **Auth** | Laravel Sanctum (Bearer Token) |
| **File Storage** | Aliyun OSS |
| **Video Interview** | Tencent RTC (TRTC) |
| **Live Streaming** | Tencent CSS (TRTC CDN Live) |
| **Email** | SMTP (configurable via Admin Settings) |
| **Container** | Docker Compose (nginx + PHP-FPM + MariaDB + Redis) |

---

## Repository Structure

```
hrint/
├── backend/                   # Laravel 10 application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Jobs/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   └── api.php
│   └── ...
├── frontend/                  # Vue 3 + Vite application
│   ├── src/
│   │   ├── views/
│   │   │   ├── public/        # Public pages
│   │   │   ├── auth/          # Login / Register pages
│   │   │   ├── student/       # Student portal
│   │   │   ├── enterprise/    # Enterprise portal
│   │   │   └── admin/         # Admin portal
│   │   ├── components/        # Shared components
│   │   ├── stores/            # Pinia stores
│   │   ├── composables/       # Vue composables
│   │   ├── router/            # Vue Router config
│   │   ├── i18n/              # Translation files (en, zh_cn, th)
│   │   └── assets/
│   └── ...
├── docker-compose.yml
├── docker-compose.prod.yml
└── DOCUMENTS/                 # All project documentation
```

---

## Development Phases & Task Index

### Phase 0 — Design Validation

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-001](TASKS/001-VISUAL-MOCKUP-REVIEW.md) | Visual Mockup Review & Corrections | — | HIGH |

### Phase 1 — Infrastructure

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-002](TASKS/002-PROJECT-INFRASTRUCTURE.md) | Project Structure & Docker Infrastructure | TASK-001 | HIGH |
| [TASK-003](TASKS/003-DATABASE-MIGRATIONS.md) | Database Schema & All Migrations | TASK-002 | HIGH |

### Phase 2 — Backend: Authentication

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-004](TASKS/004-BACKEND-AUTH.md) | Backend Auth Module | TASK-003 | HIGH |
| [TASK-005](TASKS/005-BACKEND-SOCIAL-OAUTH.md) | Backend Social OAuth Module | TASK-004 | HIGH |

### Phase 3 — Backend: Administration & Content

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-006](TASKS/006-BACKEND-USER-ADMIN.md) | Backend User & Admin Management | TASK-004 | HIGH |
| [TASK-007](TASKS/007-BACKEND-CMS.md) | Backend CMS Module (Pages + Posts) | TASK-004 | MEDIUM |
| [TASK-008](TASKS/008-BACKEND-SETTINGS.md) | Backend Settings Module | TASK-004 | MEDIUM |
| [TASK-009](TASKS/009-BACKEND-LANGUAGE.md) | Backend Language & Translations Module | TASK-008 | MEDIUM |

### Phase 4 — Backend: Core Business Features

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-010](TASKS/010-BACKEND-STUDENT.md) | Backend Student Module | TASK-004, TASK-016 | HIGH |
| [TASK-011](TASKS/011-BACKEND-ENTERPRISE.md) | Backend Enterprise Module | TASK-004, TASK-016 | HIGH |
| [TASK-012](TASKS/012-BACKEND-APPLICATIONS.md) | Backend Job Applications Module | TASK-010, TASK-011 | HIGH |
| [TASK-013](TASKS/013-BACKEND-INTERVIEWS.md) | Backend Interview Module | TASK-012 | HIGH |
| [TASK-014](TASKS/014-BACKEND-SEMINARS.md) | Backend Seminar Module | TASK-004 | HIGH |
| [TASK-015](TASKS/015-BACKEND-UNIVERSITY-CONTACT-STATS.md) | Backend University, Contact & Statistics | TASK-004 | MEDIUM |

### Phase 5 — External Service Integrations (Backend)

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-016](TASKS/016-OSS-STORAGE.md) | OSS File Storage Integration | TASK-003 | HIGH |
| [TASK-017](TASKS/017-EMAIL-SERVICE.md) | Email Service + All Templates | TASK-008 | HIGH |
| [TASK-018](TASKS/018-TRTC-BACKEND.md) | TRTC Video Interview Backend | TASK-013 | HIGH |
| [TASK-019](TASKS/019-TRTC-LIVE-BACKEND.md) | TRTC CSS Seminar Live Streaming Backend | TASK-014 | HIGH |

### Phase 6 — Frontend: Foundation

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-020](TASKS/020-FRONTEND-BOOTSTRAP.md) | Frontend Project Bootstrap | TASK-002 | HIGH |
| [TASK-021](TASKS/021-FRONTEND-LAYOUT-STORES.md) | Frontend Router, Stores & Shared Components | TASK-020 | HIGH |

### Phase 7 — Frontend: Auth Pages

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-022](TASKS/022-FRONTEND-AUTH-PAGES.md) | Login, Register, Email Confirm, Password Reset | TASK-021, TASK-004 | HIGH |

### Phase 8 — Frontend: Public Pages

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-023](TASKS/023-FRONTEND-HOME.md) | Home Page | TASK-021, TASK-007 | HIGH |
| [TASK-024](TASKS/024-FRONTEND-ABOUT-STUDY.md) | About Us + Study in China Pages | TASK-021, TASK-007 | MEDIUM |
| [TASK-025](TASKS/025-FRONTEND-TALENT-CORPORATE.md) | Talent Pool + Corporate Cooperation Pages | TASK-021, TASK-010, TASK-011 | HIGH |
| [TASK-026](TASKS/026-FRONTEND-NEWS-CONTACT.md) | News + Contact Us Pages | TASK-021, TASK-007 | MEDIUM |
| [TASK-027](TASKS/027-FRONTEND-SEMINAR-PUBLIC.md) | Seminar Center Public Pages | TASK-021, TASK-014 | HIGH |

### Phase 9 — Frontend: Student Portal

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-028](TASKS/028-FRONTEND-STUDENT-DASHBOARD-PROFILE.md) | Student Dashboard + Profile + Resume | TASK-022, TASK-010 | HIGH |
| [TASK-029](TASKS/029-FRONTEND-STUDENT-JOBS-APPLICATIONS.md) | Student Jobs + Applications Pages | TASK-028, TASK-012 | HIGH |
| [TASK-030](TASKS/030-FRONTEND-STUDENT-INTERVIEWS.md) | Student Interviews Page | TASK-028, TASK-013 | HIGH |
| [TASK-031](TASKS/031-FRONTEND-STUDENT-SEMINARS.md) | Student Seminars Pages | TASK-028, TASK-014 | HIGH |

### Phase 10 — Frontend: Enterprise Portal

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-032](TASKS/032-FRONTEND-ENTERPRISE-DASHBOARD-JOBS.md) | Enterprise Dashboard + Profile + Jobs | TASK-022, TASK-011 | HIGH |
| [TASK-033](TASKS/033-FRONTEND-ENTERPRISE-TALENT-INTERVIEWS.md) | Enterprise Talent + Interview Management | TASK-032, TASK-012, TASK-013 | HIGH |

### Phase 11 — Frontend: Admin Portal

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-034](TASKS/034-FRONTEND-ADMIN-DASHBOARD-USERS.md) | Admin Dashboard + User Management | TASK-022, TASK-006 | HIGH |
| [TASK-035](TASKS/035-FRONTEND-ADMIN-RESUMES.md) | Admin Resume Management | TASK-034, TASK-010 | HIGH |
| [TASK-036](TASKS/036-FRONTEND-ADMIN-INTERVIEWS-SEMINARS.md) | Admin Interview + Seminar Management | TASK-034, TASK-013, TASK-014 | HIGH |
| [TASK-037](TASKS/037-FRONTEND-ADMIN-CMS.md) | Admin CMS Pages (Pages + Posts) | TASK-034, TASK-007 | MEDIUM |
| [TASK-038](TASKS/038-FRONTEND-ADMIN-SETTINGS-LANGUAGE.md) | Admin Settings + Language/Translations | TASK-034, TASK-008, TASK-009 | MEDIUM |

### Phase 12 — Frontend: Integrations

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-039](TASKS/039-FRONTEND-TRTC-INTERVIEW.md) | Frontend TRTC Interview Integration | TASK-030, TASK-018 | HIGH |
| [TASK-040](TASKS/040-FRONTEND-TRTC-LIVE-SEMINAR.md) | Frontend TRTC CSS Seminar Live Integration | TASK-031, TASK-019 | HIGH |
| [TASK-041](TASKS/041-FRONTEND-SOCIAL-OAUTH.md) | Frontend Social OAuth Integration | TASK-022, TASK-005 | HIGH |

### Phase 13 — Finalization

| Task | Title | Depends On | Priority |
|------|-------|------------|----------|
| [TASK-042](TASKS/042-SEO-I18N-PERFORMANCE.md) | SEO, i18n Completeness & Performance | All frontend tasks | MEDIUM |
| [TASK-043](TASKS/043-DEPLOYMENT.md) | Production Deployment & Final Config | TASK-042 | HIGH |

---

## Dependency Graph (Critical Path)

```
TASK-001 (Design)
    └── TASK-002 (Infrastructure)
            └── TASK-003 (Migrations)
                    ├── TASK-004 (Auth)
                    │       ├── TASK-005 (Social OAuth)
                    │       ├── TASK-006 (User Admin)
                    │       ├── TASK-007 (CMS)
                    │       ├── TASK-008 (Settings)
                    │       │       └── TASK-009 (Language)
                    │       │       └── TASK-017 (Email)
                    │       ├── TASK-010 (Student)─────────┐
                    │       ├── TASK-011 (Enterprise)──────┤
                    │       └── TASK-014 (Seminars)        │
                    │               └── TASK-019 (Live)    │
                    └── TASK-016 (OSS)         └── TASK-012 (Applications)
                                                        └── TASK-013 (Interviews)
                                                                └── TASK-018 (TRTC)
                                                                
TASK-002 ──► TASK-020 (FE Bootstrap)
                 └── TASK-021 (Layout/Stores)
                         └── TASK-022 (Auth Pages)
                                 ├── TASK-023~027 (Public Pages)
                                 ├── TASK-028~031 (Student Portal)
                                 │       ├── TASK-039 (TRTC Frontend)
                                 │       └── TASK-040 (Live Frontend)
                                 ├── TASK-032~033 (Enterprise Portal)
                                 └── TASK-034~038 (Admin Portal)
                                         └── TASK-041 (Social OAuth)
                                                 └── TASK-042 (SEO/i18n)
                                                         └── TASK-043 (Deploy)
```

---

## Key Reference Documents

All task documents reference these files. Read them before starting any task:

| Document | Path | Description |
|----------|------|-------------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Business requirements (source of truth) |
| System Design | `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` | DB schema, modules, traceability |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | All REST API endpoints |
| Design System | `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` | Multi-language form field patterns |
| Solution Overview | `DOCUMENTS/SOLUTION.md` | Architecture overview |
| TRTC Integration | `DOCUMENTS/TRTC_Integration.md` | TRTC SDK integration guide |
| Visual Mockups | `DOCUMENTS/DESIGNS/visual-mockups/` | HTML mockup files for all pages |
| Email Templates | `DOCUMENTS/DESIGNS/email-templates/` | HTML email template files |

---

## Conventions & Standards

### Backend (Laravel)
- All controllers in `app/Http/Controllers/` organized by module
- All API routes in `routes/api.php`, grouped by role middleware
- Request validation via Form Request classes
- Response format: `{ success: bool, data: any, message: string }`
- Paginated responses include `meta` object
- Errors include `error.code` (string constant) + `error.message`
- All file paths stored as relative OSS keys, not full URLs
- Multi-language DB fields follow pattern: `field_zh_cn`, `field_en`, `field_th`

### Frontend (Vue 3)
- Composition API (`<script setup>`) throughout
- Vue i18n keys follow pattern: `section.key` (e.g., `nav.home`, `auth.login`)
- Pinia stores: `auth`, `settings`, `languages`
- API calls centralized in `src/api/` modules
- Element Plus components used for all forms, tables, dialogs
- Tailwind CSS for layout and spacing; Element Plus for components
- All pages must support EN / ZH_CN / TH language switching
- Colors: Primary `#003366`, Secondary `#E6F0FF`, White `#FFFFFF`

### Git & Code Quality
- Branch naming: `feature/TASK-XXX-short-description`
- Commits reference task ID: `[TASK-XXX] Description`
- No secrets in code; use `.env` files only
- OWASP Top 10 compliance required

---

## How to Use Task Documents

1. Read `PLAN.md` to understand the overall plan and current phase
2. Check task dependencies — all `Depends On` tasks must be complete first
3. Open the task file (e.g., `TASKS/004-BACKEND-AUTH.md`)
4. Read all **Reference Documents** listed in the task before starting
5. Implement all **Deliverables** listed in the task
6. Verify all **Acceptance Criteria** are met before marking complete
7. Update task status in this PLAN.md: `Pending → In Progress → Complete`

---

*Generated: 2026-05-02*
