# HRINT Project Solution Document
## HorizonHR International Talent Service

**Document Version:** 1.0  
**Date:** 2026-04-24  
**Project Status:** In Development  
**Prepared For:** Stakeholders  

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Project Overview](#2-project-overview)
3. [Solution Architecture](#3-solution-architecture)
4. [Core Features](#4-core-features)
5. [Security & Compliance (PDPA)](#5-security--compliance-pdpa)
6. [Development Progress](#6-development-progress)
7. [Tech Stack](#7-tech-stack)
8. [Risks & Mitigations](#8-risks--mitigations)
9. [Timeline & Milestones](#9-timeline--milestones)
10. [Success Criteria](#10-success-criteria)

---

## 1. Executive Summary

**What are we building?**

A professional international talent service platform connecting Southeast Asian students studying in China with Chinese enterprises. The system provides three core functions:

1. **Student Resume Upload** — Students can upload resumes, create talent cards, and showcase their profiles
2. **Online Video Interview** — Web-based 1-on-1 video interviews without app downloads (WebRTC)
3. **Online Seminar** — Live broadcast + playback with up to 10,000 concurrent viewers

**Why this matters:**

- Southeast Asian students need a trusted gateway to Chinese universities
- Chinese enterprises need reliable access to quality international talent
- Currently no unified platform exists for this specific market

**Current Status:**

- ✅ **56 of 59 tasks completed** (95% done)
- ⚠️ 3 features remaining: Video Interview, Online Seminar, Header Language Selector
- 🔒 PDPA compliance implementation in progress (FEAT-054)

---

## 2. Project Overview

### 2.1 Company Background

| Field | Value |
|-------|-------|
| **Company Name** | HorizonHR International Talent Service Co., Ltd. (湖北豪睿国际人才服务有限公司) |
| **Core Positioning** | One-stop platform for SE Asian students studying in China + localized talent recruitment for Chinese enterprises going abroad |
| **Core Slogan** | Connect Southeast Asian youth with Chinese universities, deliver quality local talent to Chinese enterprises |

### 2.2 Target Users

| Role | Description | Access Level |
|------|-------------|--------------|
| **Students** | SE Asian students seeking education and employment opportunities in China | Self-service portal with resume, applications, interviews, seminars |
| **Enterprises** | Chinese companies seeking international talent | Job posting, talent search, interview management |
| **Admin** | HorizonHR staff | Full system management, content control, user management |

### 2.3 Supported Languages

- 🇬🇧 English (primary)
- 🇨🇳 Simplified Chinese (中文简体)
- 🇹🇭 Thai (ภาษาไทย)

### 2.4 Key Compliance

**PDPA (Thailand)** — Personal data protection requirements fully addressed in system design. Implementation in progress (FEAT-054).

---

## 3. Solution Architecture

### 3.1 System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        Frontend (Vue 3 SPA)                      │
│                     Single Page Application                      │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │   Public    │  │  Student    │  │  Enterprise │  ┌────────┐ │
│  │   Pages     │  │   Portal    │  │   Portal    │  │  Admin │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  │  Panel  │ │
│                                                      └────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Backend (PHP/Laravel)                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────┐   │
│  │   Auth   │  │   User   │  │   Jobs   │  │  Interview   │   │
│  │  Service │  │  Service │  │  Service │  │   Service    │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────────┘   │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────┐   │
│  │  Resume  │  │ Seminar  │  │   CMS    │  │  Settings    │   │
│  │  Service │  │  Service │  │  Service │  │   Service    │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Database (MySQL 8.0)                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────┐   │
│  │  users   │  │students  │  │enterprises│ │   resumes    │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────────┘   │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────┐   │
│  │   jobs   │  │applications│ │interviews│ │   seminars   │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### 3.2 Database Schema (21 Tables)

| Category | Tables |
|----------|--------|
| **Core Users** | users, students, enterprises |
| **Content** | pages, posts, languages, settings |
| **Talent** | resumes, talent_cards |
| **Employment** | jobs, applications |
| **Communication** | interviews, interview_records, seminars, seminar_regs, seminar_recs |
| **Reference** | universities, consent_logs |
| **Security** | password_resets, email_confirmations, social_authentications |

**Key Design Decisions:**
- Email-based authentication with optional social login (Google, Facebook, LinkedIn, WeChat)
- Enterprise accounts require admin verification before activation
- Student accounts self-activated via email confirmation
- All passwords hashed with bcrypt
- JWT tokens for API authentication (Sanctum)

### 3.3 Module Architecture

| Module | Description | Status |
|--------|-------------|--------|
| **Public Pages** | Home, About, Study in China, Talent Pool, Corporate, News, Contact | ✅ |
| **User System** | Registration, Login, Password Reset, Social Auth | ✅* |
| **Email System** | Email Confirmation, Password Reset, Notifications (HTML Templates) | ⚠️ In Progress |
| **Student Portal** | Dashboard, Profile, Resume, Applications, Interviews, Seminars | ✅ |
| **Enterprise Portal** | Dashboard, Profile, Jobs, Talent Pool, Interviews | ✅ |
| **Admin Panel** | Users, CMS, Resumes, Interviews, Seminars, Settings, Language | ✅ |
| **Video Interview** | WebRTC-based 1-on-1 video (Tencent RTC) | ⏳ Pending |
| **Online Seminar** | Live broadcast + playback (Tencent CSS / TRTC CDN) | ⏳ Pending |

---

### 3.4 Email System Architecture

The email system uses HTML templates stored in the database with a flexible rendering system.

#### Email Templates (stored in `email_templates` table)

| Category | Template File | Trigger | Status |
|----------|--------------|---------|--------|
| **Auth** | student-verify-email.html | Student registration | ⚠️ Not wired |
| **Auth** | password-reset.html | Forgot password | ⚠️ Not wired |
| **Auth** | enterprise-pending-notify-admin.html | Enterprise needs admin activation | ⚠️ Not wired |
| **Auth** | enterprise-activated.html | Admin activates enterprise | ⚠️ Not wired |
| **Application** | job-application-received.html | Job application submitted | ⏳ Future |
| **Interview** | interview-invitation.html | Interview scheduled | ⏳ Future |
| **Interview** | interview-result.html | Interview result posted | ⏳ Future |
| **Seminar** | seminar-reminder.html | Seminar reminder | ⏳ Future |

#### Email Flow

**Student Registration:**
1. User submits `POST /api/auth/register` with role=student
2. AuthService creates user (status=pending), generates EmailConfirmation token
3. AuthService calls EmailService → sends student-verify-email.html
4. User clicks confirmation link → `GET /confirm-email?token=X&email=Y`
5. Backend validates token, sets user.status=active, email_verified=true
6. User can now login

**Enterprise Registration:**
1. User submits `POST /api/auth/register` with role=enterprise
2. AuthService creates user (status=pending, enterprise_status=pending)
3. AuthService sends confirmation email
4. After confirmation: user.email_verified=true, but status=pending, enterprise_status=pending
5. Admin activates enterprise via Admin Panel → sends enterprise-activated.html
6. Enterprise can now login

#### Key Integration Points

| File | Issue | Fix Required |
|------|-------|-------------|
| `AuthService::sendConfirmationEmail()` | Only logs URL in dev | Wire to EmailService |
| `AuthService::activateEnterprise()` | Doesn't send email | Call EmailService with enterprise-activated.html |
| `EmailService::sendEmailConfirmation()` | Plain text only | Load HTML template, render variables |
| `EmailService::sendPasswordReset()` | Plain text only | Load password-reset.html template |

#### SMTP Configuration
SMTP settings are managed via Admin Panel > Settings > SMTP (stored in `settings` table, not .env).

*Note: User System ✅ marked with asterisk because registration exists but email confirmation is not fully wired (pending Phase 1 completion).*


## 4. Core Features

### 4.1 Student Resume System

**What it does:**
- Students upload resumes (PDF, Word, JPG, PNG — max 20MB)
- Admin reviews and approves uploads
- Approved resumes generate visual talent cards
- Students control visibility: Admin-only, Enterprise-visible, Public

**Data Flow:**
```
Student Upload → Admin Review → Approved → Talent Card Generated
                                         ↓
                              Visibility Settings Applied
                                         ↓
                    Enterprise Searches → Sees Masked Data
                    Admin Searches → Sees Full Data
```

**Status:** ✅ Implemented

### 4.2 Online Video Interview (WebRTC)

**What it does:**
- No app download — browser-based using Tencent RTC
- Creator (admin/enterprise) schedules interview → sends link via email/SMS
- Student clicks link to join without login
- Supports: Video, voice, text chat, screen sharing
- Auto-records: time, participants, duration, notes, result tagging

**Technical:**
- WebRTC for real-time communication
- Tencent RTC SDK integration
- Room-based architecture with unique room IDs

**Status:** ⏳ Pending (FEAT-009)

### 4.3 Online Seminar System

**What it does:**
- PPT + speaker camera dual display
- Supports 10,000+ concurrent viewers
- Bullet comments (danmu), online Q&A
- 15-minute reminder before start
- Playback with 0.5x-2x speed, timeline scrubbing

**Technical:**
- Tencent CSS (TRTC CDN) for live streaming
- Auto-recorded and stored permanently
- Permission levels: Public or Registered-only

**Status:** ⏳ Pending (FEAT-010)

### 4.4 Multi-language CMS

**What it does:**
- Full content management for Pages and Posts
- Separate content fields per language (zh_cn, en, th)
- SEO meta tags per language
- Category system: Company News, Industry News, Study Abroad, Recruitment

**Status:** ✅ Implemented

### 4.5 Language Settings Management

**What it does:**
- Admin can add/edit/remove UI text translations
- Language switcher in header (dynamic from API)
- Fallback to English when translation missing

**Status:** ✅ Implemented (FEAT-052)

---

## 5. Security & Compliance (PDPA)

### 5.1 PDPA Requirements

The system implements comprehensive personal data protection compliant with Thailand's Personal Data Protection Act (PDPA).

**Key Principles Implemented:**

| Principle | Implementation |
|-----------|----------------|
| **Lawfulness** | Consent management during registration |
| **Purpose Limitation** | Clear purpose for each data collection |
| **Data Minimization** | Only necessary data collected |
| **Accuracy** | Students can update their own profile |
| **Storage Limitation** | Data retention policies enforced |
| **Integrity & Confidentiality** | Encryption, masking, access controls |
| **Accountability** | Audit logging, data subject rights |

### 5.2 Data Classification

| Level | Data Examples | Protection |
|-------|---------------|------------|
| **Critical** | National ID, passport | Encryption + Masking + Strict Access |
| **High** | Phone, email, address, resume | Encryption + Access Control |
| **Medium** | Name, education history | Standard Security |
| **Low** | Company name, job title | Basic Protection |

### 5.3 Technical Security Controls

**Implemented:**
- ✅ Passwords hashed with bcrypt
- ✅ JWT authentication (Sanctum)
- ✅ RBAC (Role-Based Access Control)
- ✅ CSRF protection (Laravel default)
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS prevention (Vue 3 auto-escape)

**In Progress:**
- ⚠️ Data masking in API responses
- ⚠️ Audit logging for personal data access
- ⚠️ Rate limiting configuration
- ⚠️ Consent management flow

### 5.4 Data Masking Rules

| Data Type | Display Rule |
|-----------|--------------|
| National ID | `***-****-1234` (last 4 visible) |
| Phone | `***-***-1234` (last 4 visible) |
| Email | `joh***@example.com` (first 3 chars) |
| Birth Date | `**/**/1990` → `**/**/****` (day only) |

### 5.5 Rate Limiting

| Endpoint Type | Limit |
|---------------|-------|
| Authentication | 5 requests/min per IP |
| API (authenticated) | 60 requests/min per user |
| API (anonymous) | 30 requests/min per IP |
| File uploads | 10 uploads/hour per user |
| Password reset | 3 requests/hour per email |

### 5.6 Data Retention Policy

| Data Type | Retention Period |
|-----------|------------------|
| Active accounts | Retained while active |
| Deleted accounts | 90-day grace period → permanent deletion |
| Inactive accounts | 24 months no activity → anonymization |
| Resume files | Purged from storage upon account deletion |
| Activity logs | 1 year |
| Email confirmations | 30 days |
| Consent records | Account exists + 2 years after deletion |

### 5.7 User Rights

| Right | Implementation |
|-------|----------------|
| **Right to Access** | GET /api/users/{id}/export — full data export as JSON |
| **Right to Correction** | Profile self-edit in portal |
| **Right to Deletion** | Account deletion flow (anonymize + retain transactional) |
| **Right to Withdraw Consent** | Settings page + consent log |

---

## 6. Development Progress

### 6.1 Overall Progress

```
Total Tasks: 59
Completed:   56 (95%)
In Testing:    1 (FEAT-054 - PDPA Implementation)
Pending:       2 (FEAT-009 Video Interview, FEAT-010 Online Seminar)
```

### 6.2 Completed Features

| Category | Features |
|----------|----------|
| **Infrastructure** | Docker setup, Git repo, folder structure |
| **Backend API** | User system, job management, resume handling, CMS, settings |
| **Frontend Pages** | Home, About, Study in China, Talent Pool, Corporate, News, Contact |
| **Student Portal** | Dashboard, Profile, Resume, Applications, Interviews, Seminars |
| **Enterprise Portal** | Dashboard, Profile, Jobs, Talent Pool, Interviews |
| **Admin Panel** | Dashboard, Users, Pages, Posts, Resumes, Interviews, Seminars, Settings, Language |
| **Authentication** | Email login, social login (Google, FB, LinkedIn, WeChat), password reset |
| **CMS** | Page CRUD, Post CRUD, Multi-language support |
| **Language Settings** | Admin translation management (FEAT-052) |

### 6.3 Remaining Features

| Task | Description | Status |
|------|-------------|--------|
| **FEAT-009** | Online Video Interview System | ⏳ Pending |
| **FEAT-010** | Online Seminar System | ⏳ Pending |
| **FEAT-053** | Header Language Selector - Dynamic | ⏳ Pending |

### 6.4 Recent Completions

| Date | Feature | Notes |
|------|---------|-------|
| 2026-04-24 | PDPA Security Review (FEAT-055) | Section 5 design review completed |
| 2026-04-24 | PDPA Section Fixes (FEAT-056) | 10 gaps fixed in SYSTEM_DESIGN.md |
| 2026-04-23 | FEAT-052: Admin Language Settings | Full translation management |
| 2026-04-23 | FEAT-048: Admin Dashboard | Mockup compliant |
| 2026-04-22 | FEAT-022: Login Page | 3-tab split screen |

---

## 7. Tech Stack

### 7.1 Frontend

| Component | Technology | Notes |
|-----------|------------|-------|
| **Framework** | Vue 3 (Composition API) | SPA |
| **Build Tool** | Vite | Fast HMR, optimized builds |
| **Routing** | Vue Router 4 | SPA routing |
| **State** | Pinia | Auth + app state |
| **HTTP** | Axios | API calls |
| **UI Library** | Element Plus | Forms, tables, dialogs; built-in EN/ZH locale |
| **i18n** | Vue i18n | EN / ZH_CN / TH support |
| **Styling** | Tailwind CSS | Utility-first responsive layout |
| **Icons** | Element Plus Icons | Consistent icon set |

### 7.2 Backend

| Component | Technology | Notes |
|-----------|------------|-------|
| **Framework** | Laravel 10 | PHP 8.2 |
| **API** | REST + Sanctum | JWT authentication |
| **Database** | MySQL 8.0 | Via Docker |
| **Cache** | Redis | Session + cache |
| **Queue** | Redis | Background jobs |
| **File Storage** | Local + OSS ready | For uploads |

### 7.3 Infrastructure

| Component | Technology | Notes |
|-----------|------------|-------|
| **Container** | Docker Compose | nginx + PHP + MySQL + Redis |
| **Web Server** | Nginx | Reverse proxy |
| **Database** | MariaDB 10.11 | MySQL-compatible |

---

## 8. Risks & Mitigations

| Risk | Severity | Mitigation |
|------|----------|------------|
| **Video Interview integration complexity** | Medium | Tencent RTC well-documented; sandbox testing planned |
| **Seminar streaming scalability** | Medium | Tencent CSS handles 10K+ concurrent via TRTC CDN |
| **PDPA compliance gaps** | High | ✅ Design reviewed by sa; FEAT-054 implementing controls |
| **International data transfer** | Medium | Data localization; consent required for cross-border |
| **Social auth WeChat (China-specific)** | Low | Configurable; can be enabled after ICP备案 |
| **Enterprise account verification** | Low | Admin manual approval; clear workflow |

---

## 9. Timeline & Milestones

### Original Timeline (27-38 working days)

| Phase | Duration | Status |
|-------|----------|--------|
| Design | 7-10 days | ✅ Complete |
| Development | 15-20 days | 🔄 95% complete |
| Testing & Revision | 3-5 days | ⏳ Pending |
| Launch & Training | 2-3 days | ⏳ Pending |

### Current Progress

- **Phase 1 & 2:** 95% complete (56 of 59 tasks)
- **Remaining work:** FEAT-009, FEAT-010, FEAT-053, FEAT-054
- **Estimated remaining:** ~5-7 working days

---

## 10. Success Criteria

### 10.1 Functional Criteria

| Criterion | Measurement |
|-----------|-------------|
| ✅ Multi-language support | User can switch between EN, ZH, TH |
| ✅ Student registration + email activation | New student can register and activate via email |
| ✅ Enterprise registration + admin approval | Enterprise registers → admin activates |
| ✅ Resume upload and review | Student uploads → admin reviews → approved/rejected |
| ✅ Job posting and application | Enterprise posts job → student applies → application tracked |
| ✅ Video interview (when implemented) | Scheduled interview → student joins via link → recorded |
| ✅ Seminar system (when implemented) | Live broadcast → playback available → 10K+ viewers |
| ✅ PDPA compliance | All personal data access logged; masking applied; consent managed |

### 10.2 Technical Criteria

| Criterion | Target |
|----------|--------|
| Page load time | ≤ 3 seconds |
| API response time | ≤ 500ms |
| Concurrent users | 10,000+ for seminar |
| Browser compatibility | Chrome, Edge, Firefox, Safari (latest) |
| Mobile support | iOS, Android, Huawei, Xiaomi |
| Security | No data leakage, bcrypt passwords, JWT auth |

### 10.3 Security Checklist

| Item | Status |
|------|--------|
| Password hashing | ✅ bcrypt |
| JWT authentication | ✅ Sanctum |
| Input validation | ⚠️ In progress |
| Data masking | ⚠️ In progress |
| Audit logging | ⚠️ In progress |
| Rate limiting | ⚠️ In progress |
| Consent management | ⚠️ In progress |
| CORS strict policy | ⚠️ To verify |
| File upload security | ⚠️ ClamAV in progress |

---

## Appendix: Document Reference

| Document | Location | Description |
|----------|----------|-------------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Complete project requirements (292 lines) |
| System Design | `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` | Technical architecture (1730 lines) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | API endpoints reference |
| Visual Mockups | `DOCUMENTS/DESIGNS/visual-mockups/` | UI design mockups |

---

**Document Prepared By:** โปร (Pro) — Project Manager  
**Last Updated:** 2026-04-24  
**Next Review:** Upon FEAT-054 completion