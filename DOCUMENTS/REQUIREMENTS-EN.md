# HorizonHR International Talent Service - Website Requirements

## I. Document Overview

This document provides complete requirements for the official website of Hubei Horizon International Talent Service Co., Ltd. (hereinafter referred to as "Horizon International"). It defines website positioning,栏目功能 (module functions), technical requirements, design style, and other core content for reference by the website design company, ensuring the final product meets business needs and brand image.

**Core Requirements:** Build a professional international talent service website combining "Company Promotion, Student Showcase, Corporate Client Showcase" with three core functions: student resume upload, online interview, and online seminar. Multi-terminal and multi-language support with reserved capacity for domestic business promotion as well (separated into domestic and international sections).

---

## II. Basic Information

### A. Company Core Information

- **Chinese Name:** 湖北豪睿国际人才服务有限公司
- **English Name:** HorizonHR International Talent Service Co., Ltd.
- **Core Positioning:** One-stop platform for Southeast Asian students studying in China + localized talent recruitment for Chinese enterprises going abroad
- **Core Slogan:** Connect Southeast Asian youth with Chinese universities, deliver quality local talent to Chinese enterprises
- **Brand Core:** Professional, International, Reliable, Closed-loop (Study → Service → Employment → Chinese Enterprise Talent)

### B. Website Basic Requirements

- **Languages:** a Multilangauage Website should be (but start project with English-Chinese-Thai) 
- **Terminals:** PC, Mobile, Tablet (responsive design)
- **Access Speed:** Page load ≤3 seconds, no lag, no display errors
- **Security:** Data encryption support, no leakage risk for resume upload and video interview
- **Maintainability:** Simple backend operations, supports independent content updates without technical staff

---

## III. Website Core Objectives

### A. Three Promotion Objectives

1. **Promote Company:** Highlight Horizon International's qualifications, service system, core advantages, and industry influence; build professional international talent service brand image.
2. **Promote Students:** Showcase outstanding student profiles (photos, education, skills, job intentions); build transparent international talent database.
3. **Promote Corporate Clients:** Display partner company logos, profiles, recruitment needs, and cooperation cases; attract more SE Asian Chinese enterprises.

### B. Three Core Function Objectives (Key Implementation)

1. **Student Resume Online Upload:** Support student self-registration, profile completion, resume upload; enable resume classification, filtering, export; generate visual talent cards.
2. **Online Video Interview:** Support one-on-one video interview directly via web (no APP download needed); implement interview scheduling, initiation, and record management.
3. **Online Seminar:** Support live broadcast, playback viewing, reservation reminders; meet company, university, and enterprise seminar needs.

---

## IV. Website Modules and Detailed Functions

### A. Frontend Display Modules (Visible to All Visitors)

#### 1. Home
- **Header:** Company name (CN/EN), navigation menu (CN/EN), online consultation (WeChat, WhatsApp, Line), login/register (differentiated for students, enterprises, admin)
- **Banner:** Slogan + business closed-loop diagram (Study in China → Services in China → Employment → Chinese Enterprise Talent), auto-rotate (3-4 images)
- **Core Section Entries:** Study in China, Outstanding Students, Corporate Cooperation, Seminar Center (图文结合, click to navigate)
- **Scroll Display:** Partner university logos, partner company logos, outstanding student showcase, upcoming seminars
- **Core Advantages:** 3-4 key advantages (stable SE Asia channels, rich university partnerships, targeted Chinese enterprise recruitment, full closed-loop service)
- **Footer:** Address, phone, email, WeChat, copyright, privacy policy, terms

#### 2. About Us
- Company introduction: development history, business scope, core team, qualifications
- Service system: full-process service display (recruitment, living services, academic tutoring, employment placement)
- Qualification display: certificates, authorization documents

#### 3. Study in China
- **Recruitment Projects:** Vocational, Bachelor's, Master's, Chinese language training
- **Application Guide:** Process, required materials, visa, airport pickup, accommodation, insurance
- **Partner Universities:** Logos, profiles,优势专业 (strength majors), filterable by major/location
- **FAQ:** Application difficulty, language requirements, fee details

#### 4. Talent Pool
- **Talent Cards:** Photo, name, nationality, university, major, language ability, job intention, resume preview (visibility settings: admin-only/enterprise-visible/public)
- **Filtering:** By nationality, major, language, education, availability
- **Student Showcase:** Outstanding student cases, learning experience, employment results

#### 5. Corporate Cooperation
- **Enterprise Services:** Localized recruitment, targeted training, labor dispatch, customized talent programs
- **Partner Display:** Large logo wall, click for profile and recruitment needs
- **Cooperation Cases:** Case studies with company name, positions, numbers, results
- **Enterprise Portal:** Registration, login, job posting, resume viewing, interview initiation

#### 6. Seminar Center
- **Seminar Preview:** Topic, time, speaker, target audience, reservation; SMS/email reminders
- **Live Broadcast:** Web-based real-time streaming, PPT + speaker camera, danmu (bullet comments), online Q&A
- **Playback Section:** Auto-saved recordings, filterable, 0.5x-2x speed

#### 7. News
- Categories: Company news, industry news, study abroad policies, enterprise recruitment info
- Functions: Time sorting, keyword search, detail view, sharing

#### 8. Contact Us
- Contact info: Address, phone, email, WeChat, WhatsApp, Line, Facebook, LinkedIn; map location
- Online consultation form: Name, contact, content; submission triggers notification
- Regional offices: Display office information by country/region

---

### B. User System (Three Roles, Differentiated Permissions)

One email should not be restricted to be only one type of acccount, but an email can be registered as Admin/Student/Enterprise account

#### 1. Admin Account
- User management: Review student/enterprise accounts, manage status
- Content management: Update all frontend content
- Resume management: Review, filter, export, download resumes
- Interview management: Create, schedule, cancel interviews; view records
- Seminar management: Create, edit, delete seminars; manage playback
- Data statistics: Visitors, resume uploads, interviews, seminar views
- Preference language: Used in Website, Admin Pages, Recieve Emails

#### 2. Student Account
- Registration/login: Phone, email; bind personal info (name, nationality, education)
- Resume management: Upload (PDF, Word, JPG/PNG, ≤20MB), edit profile, set visibility
- Seminars: Register, receive reminders, watch live/playback
- Interview: Receive invitations, view time, join video room via link
- Job viewing: View postings, submit resumes
- Preference language: Used in Website, Student Pages, Recieve Emails

#### 3. Enterprise Account
- Registration/login: After review, complete company profile
- Job management: Post, edit, delete positions; view submitted resumes
- Talent filtering: View student database, filter, view resume details
- Interview management: Send invitations, schedule, initiate video interview
- Seminars: Watch live/playback
- Preference language: Used in Website, Enterprise Pages, Recieve Emails

#### 4. Social Authentication
- **Supported Providers:**
  - Google Login/Register
  - Facebook Login/Register
  - LinkedIn Login/Register
  - WeChat Login/Register
- **Scope:** Social auth is for user authentication only (student and enterprise login/register)
- **Admin Accounts:** Remain email-based only (no social login for admin)
- **Enterprise Verification:** Enterprise accounts may require additional verification after social registration
- **WeChat Configuration:** May require China-specific configuration (ICP备案, WeChat Open Platform registration)
- **Flow:**
  - User initiates social login → Redirect to provider → User authorizes → Callback with auth code → Exchange for user info → Create/ link account → Issue JWT token

---

### C. Core Functions - Detailed Requirements

#### 1. Student Resume Online Upload
- **Supported formats:** PDF, Word (doc/docx), Images (jpg/png), ≤20MB per file
- **Visibility settings:** Admin-only / Enterprise-visible / Public
- **Backend:** Filter (nationality, major, education), export, download, batch operations
- **Talent card auto-generation:** After review approval, auto-generate standardized talent card

#### 2. Online Video Interview (1-on-1)
- **Technical:** No APP download, web-based (Chrome, Edge, Firefox), WebRTC, no plugins, no lag
- **Process:** Creator (admin/enterprise) creates interview → sets time → sends link (SMS/email/site message) → student clicks link to join without login
- **Core features:** Video, voice, text chat, screen sharing, real-time status updates
- **Records:** Auto-record time, participants; support notes, result tagging (Pass/Fail/Pending)

#### 3. Online Seminar (Live + Playback)
- **Live:** PPT + camera dual display, support 10,000+ concurrent viewers, no lag; danmu, online Q&A
- **Reservation:** 15-minute reminder before start (SMS/site message)
- **Playback:** Auto-generated, timeline scrubbing, 0.5x-2x speed, permanent storage
- **Management:** Set permissions (public/register-only), edit title/description, delete无效 seminars

#### Registration Workflow
- Confirm Email on registration
    - Stuent User activate account on email confirmed
    - Enterprise User need to contact Office to activate account
- Admin should not know anyone password
    - On change/Reset Password send Set New Password Link to registered email

#### CMS for Website 
- Support all suported languages
- PAGE CRUD
- POST (News, Announcement) CRUD

#### Settings
- Able to Set all website Logo, Favicon, Header, Footer, Copyright
- SMTP/Sendmail Credentials for send email methods

---

## V. Design Style Requirements

- **Primary Color:** Dark Blue (#003366) + White (#FFFFFF)
- **Secondary Color:** Light Blue (#E6F0FF)
- **Style:** Clean, professional, international; reject redundant decoration
- **Visual Elements:** International talent, study abroad, corporate cooperation icons (student silhouettes, university buildings, handshake); unified, simple style
- **Fonts:** Chinese: Microsoft YaHei / Source Han Sans; English: Arial; clear hierarchy
- **UX:** Clear navigation, smooth transitions, obvious button feedback; simple forms, resume upload, interview entry; clear prompts

---

## VI. Technical Standards and Development Requirements

- **Backend:** PHP + Mariadb recommended (simple maintenance); 
- **Frontend:** Vue 3 SPA (Vite build tool) — see Section IX.A for full frontend tech stack
- **Storage:** OSS (or equivalent) for resumes, images, seminar recordings
- **Video:** WebRTC for online interview (Tencent TRTC); Tencent CSS (Cloud Streaming Services) / TRTC CDN for seminar live broadcast (10,000+ concurrent viewers)
- **TRTC Reference:** See `DOCUMENTS/TRTC_Integration.md` — covers both video interview (Section 3) and seminar live streaming (Section 4–6)
- **SEO:** TDK settings, optimized page structure, search engine indexing
- **Compatibility:** Chrome, Edge, Firefox, Safari (latest versions); iOS, Android, Huawei, Xiaomi
- **Support:** Minimum 6 months free maintenance (bug fixes, minor adjustments); backend operation training

---

## VII. Deliverables and Timeline

### A. Deliverables
- Design drafts: Homepage + all module pages (PC + Mobile), confirmed before development
- Complete website: Frontend, backend, all core functions, operational
- Materials: Backend operation manual, technical documentation, domain/server configuration guide
- Test reports: Functional, compatibility, security testing

---

## VIII. Supplementary Notes

- This document is core requirements; design company may propose reasonable optimizations with client confirmation
- All content (text, images, logos) provided by client; design company handles layout, integration, optimization
- Future extensions (new languages, talent assessment features): design company provides technical solutions and pricing

---

**Document Info:**
- Language: English Translation
- Date: 2026-04-21
- Project Code: HRINT

---

## IX. Supplementary Requirements (2026-04-24)

### A. Frontend Tech Stack

The frontend uses a modern SPA (Single Page Application) framework. HTML5 + CSS3 + JavaScript alone is no longer used.

| Component | Technology | Notes |
|-----------|------------|-------|
| **Framework** | Vue 3 (Composition API) | SPA |
| **Build Tool** | Vite | Fast HMR, optimized builds |
| **Routing** | Vue Router 4 | SPA routing |
| **State Management** | Pinia | Auth + app state |
| **HTTP Client** | Axios | REST API calls |
| **UI Library** | Element Plus | Forms, tables, dialogs; built-in EN/ZH locale |
| **Internationalization** | Vue i18n | EN / ZH\_CN / TH support |
| **Styling** | Tailwind CSS | Utility-first responsive layout |
| **Icons** | Element Plus Icons | Consistent icon set |

**Rationale:**
- **Element Plus** — purpose-built for Vue 3 admin/enterprise UIs; covers all form, table, and dialog needs across Admin, Student, and Enterprise portals; includes built-in Chinese and English locale out of the box
- **Vue i18n** — required for the multi-language requirement (EN/ZH\_CN/TH); allows runtime language switching without page reload
- **Tailwind CSS** — utility-first approach enables responsive layouts and custom brand styling (Dark Blue #003366 + White) without heavy CSS files; works alongside Element Plus with proper prefix configuration
- **Vite** — significantly faster than Webpack for development and production builds; tree-shaking ensures small bundle size

### B. PDPA Compliance - Personal Data Protection

**Effective immediately:** All development must consider PDPA (Personal Data Protection Act) requirements, particularly relevant for Thailand operations.

#### Data Security Requirements

1. **No Data Leakage**
   - All personal data must be protected against unauthorized access
   - Sensitive fields must be masked in API responses based on viewer role
   - File uploads (resumes) must be stored securely with encryption at rest

2. **Input Validation**
   - All user inputs must be validated and sanitized
   - Prevent SQL injection, XSS attacks, CSRF attacks
   - File upload validation: type, size, malware scanning

3. **Audit Logging**
   - All access to personal data must be logged
   - Log user actions: view, create, update, delete, export, download
   - Retain logs for minimum 1 year

4. **Data Minimization**
   - Collect only necessary data
   - Clear purpose statement for each data collection

5. **User Rights**
   - Users can request data access, correction, deletion
   - Consent management for optional data processing
   - Privacy policy must be accessible and clear

6. **Technical Controls**
   - Passwords: bcrypt hashing (required)
   - API authentication: JWT/Sanctum
   - Rate limiting to prevent abuse
   - CORS: strict origin policy only

7. **Data Retention**
   - Deleted accounts: 90-day grace period before permanent deletion
   - Inactive accounts: anonymize after 2 years
   - Activity logs: 1 year retention

#### Implementation Reference
- See: DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md Section 5 (PDPA Compliance & Security Controls)
- Review with: sa (System Analyst), u (Developer)

---

**Document Update Date:** 2026-04-24  
**Updated By:** โปร (Pro) - PM  

