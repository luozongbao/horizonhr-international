# HRINT System Design Document
## HorizonHR International Talent Service

**Document ID:** HRINT-DOC-002  
**Project:** HorizonHR International Talent Service (HRINT)  
**Based on:** Requirements (HRINT-DOC-001), Visual Mockups by ยู (u)  
**Date:** 2026-04-21  
**Status:** Complete

---

## Table of Contents
1. [Database Schema](#1-database-schema)
2. [Module Architecture](#2-module-architecture)
3. [Traceability Matrix](#3-traceability-matrix)
4. [API Reference](#4-api-reference)

---

## 1. Database Schema

### 1.1 ER Diagram Overview

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                    HRINT Database Schema                               │
│                                                                                         │
│  ┌──────────────┐         ┌──────────────┐         ┌──────────────┐                   │
│  │   users      │         │  languages   │         │   settings   │                   │
│  ├──────────────┤         ├──────────────┤         ├──────────────┤                   │
│  │ id (PK)      │         │ id (PK)      │         │ id (PK)       │                   │
│  │ role         │         │ key          │         │ key           │                   │
│  │ email        │         │ zh_cn        │         │ value         │                   │
│  │ password     │         │ en           │         │ type          │                   │
│  │ status       │         │ th           │         │ group         │                   │
│  │ prefer_lang  │         │ created_at   │         │ updated_at    │                   │
│  │ created_at   │         │ updated_at   │         └──────────────┘                   │
│  │ updated_at   │         └──────────────┘                                                │
│  └──────┬───────┘                                                                   │
│         │                                                                             │
│  ┌──────┴───────┐      ┌──────────────┐         ┌──────────────┐                   │
│  │students      │      │   pages       │         │   posts      │                   │
│  ├──────────────┤      ├──────────────┤         ├──────────────┤                   │
│  │ id (PK)      │      │ id (PK)      │         │ id (PK)      │                   │
│  │ user_id (FK) │◄────┐│ slug         │         │ page_id (FK) │◄────┐            │
│  │ name         │     │| title_zh_cn  │         │ title_zh_cn  │     │            │
│  │ nationality  │     ││ title_en     │         │ title_en     │     │            │
│  │ phone        │     ││ title_th     │         │ title_th     │     │            │
│  │ avatar       │     ││ content_zh_cn│         │ content_zh_cn│     │            │
│  │ birth_date   │     ││ content_en   │         │ content_en   │     │            │
│  │ gender       │     ││ content_th   │         │ content_th   │     │            │
│  │ address      │     ││ meta_zh_cn   │         │ meta_zh_cn   │     │            │
│  │ bio          │     ││ meta_en     │         │ meta_en     │     │            │
│  │ verified     │     ││ meta_th     │         │ meta_th     │     │            │
│  └──────┬───────┘     ││ status      │         │ category     │     │            │
│         │            ││ type        │         │ thumbnail    │     │            │
│         │            ││ order       │         │ status       │     │            │
│         │            ││ created_at  │         │ view_count   │     │            │
│  ┌──────┴───────┐     ││ updated_at  │         │ published_at │     │            │
│  │ enterprises │     │└──────────────┘         │ created_at   │     │            │
│  ├──────────────┤                            ││ updated_at   │     │            │
│  │ id (PK)      │                            │└──────────────┘     │            │
│  │ user_id (FK) │◄────┐                      └──────────────┘     │            │
│  │ company_name │     │                                               │            │
│  │ industry     │     │                                               │            │
│  │ logo         │     │                                               │            │
│  │ scale        │     │                                               │            │
│  │ description  │     │                                               │            │
│  │ website      │     │                                               │            │
│  │ address      │     │                                               │            │
│  │ verified     │     │                                               │            │
│  └──────┬───────┘     │                                               │            │
│         │            │                                               │            │
│         │            │                                               │            │
│  ┌──────┴───────┐     │                                               │            │
│  │  resumes     │     │                                               │            │
│  ├──────────────┤     │                                               │            │
│  │ id (PK)      │     │                                               │            │
│  │ student_id   │◄───┤│                                               │            │
│  │ file_path    │     │                                               │            │
│  │ file_type    │     │                                               │            │
│  │ visibility   │     │                                               │            │
│  │ status       │     │                                               │            │
│  │ created_at   │     │                                               │            │
│  │ updated_at   │     │                                               │            │
│  └──────┬───────┘     │                                               │            │
│         │            │                                               │            │
│  ┌──────┴───────┐     │                                               │            │
│  │talent_cards │     │                                               │            │
│  ├──────────────┤     │                                               │            │
│  │ id (PK)      │     │                                               │            │
│  │ student_id   │◄───┤│                                               │            │
│  │ display_name │     │                                               │            │
│  │ major        │     │                                               │            │
│  │ education    │     │                                               │            │
│  │ languages    │     │                                               │            │
│  │ skills       │     │                                               │            │
│  │ work_exp     │     │                                               │            │
│  │ job_intent   │     │                                               │            │
│  │ status       │     │                                               │            │
│  │ created_at   │     │                                               │            │
│  │ updated_at   │     │                                               │            │
│  └──────────────┘     │                                               │            │
│                       │                                               │            │
│  ┌──────────────┐      │                                               │            │
│  │    jobs      │      │                                               │            │
│  ├──────────────┤      │                                               │            │
│  │ id (PK)      │      │                                               │            │
│  │ enterprise_id│◄────┘│                                               │            │
│  │ title        │      │                                               │            │
│  │ description  │      │                                               │            │
│  │ requirements │      │                                               │            │
│  │ location     │      │                                               │            │
│  │ salary_range │      │                                               │            │
│  │ job_type     │      │                                               │            │
│  │ status       │      │                                               │            │
│  │ published_at │      │                                               │            │
│  │ expires_at   │      │                                               │            │
│  │ created_at   │      │                                               │            │
│  │ updated_at   │      │                                               │            │
│  └──────┬───────┘      │                                               │            │
│         │              │                                               │            │
│  ┌──────┴───────┐      │                                               │            │
│  │applications │       │                                               │            │
│  ├──────────────┤      │                                               │            │
│  │ id (PK)      │      │                                               │            │
│  │ job_id (FK)  │◄────┘│                                               │            │
│  │ student_id   │      │                                               │            │
│  │ resume_id    │      │                                               │            │
│  │ cover_letter │      │                                               │            │
│  │ status       │      │                                               │            │
│  │ applied_at   │      │                                               │            │
│  │ updated_at   │      │                                               │            │
│  └──────────────┘      │                                               │            │
│                        │                                               │            │
│  ┌──────────────┐      │                                               │            │
│  │ interviews  │      │                                               │            │
│  ├──────────────┤      │                                               │            │
│  │ id (PK)      │      │                                               │            │
│  │ creator_id   │      │                                               │            │
│  │ enterprise_id│     │                                               │            │
│  │ student_id   │      │                                               │            │
│  │ job_id (FK)  │      │                                               │            │
│  │ scheduled_at │      │                                               │            │
│  │ duration     │      │                                               │            │
│  │ room_id      │      │                                               │            │
│  │ status       │      │                                               │            │
│  │ created_at   │      │                                               │            │
│  │ updated_at   │      │                                               │            │
│  └──────┬───────┘      │                                               │            │
│         │              │                                               │            │
│  ┌──────┴───────┐      │                                               │            │
│  │interview_rec │      │                                               │            │
│  ├──────────────┤      │                                               │            │
│  │ id (PK)      │      │                                               │            │
│  │ interview_id│◄────┘│                                               │            │
│  │ participant  │      │                                               │            │
│  │ joined_at    │      │                                               │            │
│  │ left_at      │      │                                               │            │
│  │ duration_sec │      │                                               │            │
│  │ notes        │      │                                               │            │
│  │ result       │      │                                               │            │
│  │ recording_url│      │                                               │            │
│  │ created_at   │      │                                               │            │
│  └──────────────┘      │                                               │            │
│                        │                                               │            │
│  ┌──────────────┐      │                                               │            │
│  │  seminars    │      │                                               │            │
│  ├──────────────┤      │                                               │            │
│  │ id (PK)      │      │                                               │            │
│  │ title_zh_cn  │      │                                               │            │
│  │ title_en     │      │                                               │            │
│  │ title_th     │      │                                               │            │
│  │ desc_zh_cn   │      │                                               │            │
│  │ desc_en      │      │                                               │            │
│  │ desc_th      │      │                                               │            │
│  │ speaker_name │      │                                               │            │
│  │ speaker_bio  │      │                                               │            │
│  │ target       │      │                                               │            │
│  │ thumbnail    │      │                                               │            │
│  │ stream_url   │      │                                               │            │
│  │ starts_at    │      │                                               │            │
│  │ duration_min │      │                                               │            │
│  │ status       │      │                                               │            │
│  │ max_viewers  │      │                                               │            │
│  │ current_view │      │                                               │            │
│  │ permission   │      │                                               │            │
│  │ created_at   │      │                                               │            │
│  │ updated_at   │      │                                               │            │
│  └──────┬───────┘      │                                               │            │
│         │              │                                               │            │
│  ┌──────┴───────┐      │                                               │            │
│  │seminar_regs  │      │                                               │            │
│  ├──────────────┤      │                                               │            │
│  │ id (PK)      │      │                                               │            │
│  │ seminar_id   │◄────┘│                                               │            │
│  │ email        │      │                                               │            │
│  │ name         │      │                                               │            │
│  │ reminded_at  │      │                                               │            │
│  │ registered_at│      │                                               │            │
│  └──────┬───────┘      │                                               │            │
│         │              │                                               │            │
│  ┌──────┴───────┐      │                                               │            │
│  │seminar_recs  │      │                                               │            │
│  ├──────────────┤      │                                               │            │
│  │ id (PK)      │      │                                               │            │
│  │ seminar_id   │◄────┘│                                               │            │
│  │ title        │      │                                               │            │
│  │ video_url    │      │                                               │            │
│  │ duration_sec │      │                                               │            │
│  │ view_count   │      │                                               │            │
│  │ created_at   │      │                                               │            │
│  └──────────────┘      │                                               │            │
│                        │                                               │            │
│  ┌──────────────┐      │                                               │            │
│  │universities  │      │                                               │            │
│  ├──────────────┤      │                                               │            │
│  │ id (PK)      │      │                                               │            │
│  │ name_zh_cn   │      │                                               │            │
│  │ name_en      │      │                                               │            │
│  │ name_th      │      │                                               │            │
│  │ logo         │      │                                               │            │
│  │ location     │      │                                               │            │
│  │ website      │      │                                               │            │
│  │ description  │      │                                               │            │
│  │ majors       │      │                                               │            │
│  │ created_at   │      │                                               │            │
│  │ updated_at   │      │                                               │            │
│  └──────────────┘      │                                               │            │
│                        │                                               │            │
│  ┌──────────────┐      │                                               │            │
│  │ password_rese│      │                                               │            │
│  ├──────────────┤      │                                               │            │
│  │ id (PK)      │      │                                               │            │
│  │ user_id (FK) │◄─────┘│                                               │            │
│  │ token        │      │                                               │            │
│  │ expires_at   │      │                                               │            │
│  │ used_at      │      │                                               │            │
│  │ created_at   │      │                                               │            │
│  └──────────────┘      │                                               │            │
│                        │                                               │            │
│  ┌──────────────┐      │                                               │            │
│  │ email_confor │      │                                               │            │
│  ├──────────────┤      │                                               │            │
│  │ id (PK)      │      │                                               │            │
│  │ user_id (FK) │◄─────┘│                                               │            │
│  │ token        │      │                                               │            │
│  │ confirmed_at │      │                                               │            │
│  │ expires_at   │      │                                               │            │
│  │ created_at   │      │                                               │            │
│  └──────────────┘      │                                               │            │
│                        │                                               │            │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

### 1.2 Table Definitions

#### 1.2.1 Users Table
```sql
CREATE TABLE users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role            ENUM('admin', 'student', 'enterprise') NOT NULL DEFAULT 'student',
    email           VARCHAR(255) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    status          ENUM('pending', 'active', 'suspended', 'deleted') NOT NULL DEFAULT 'pending',
    enterprise_status ENUM('pending', 'enterprise_verified') NULL COMMENT 'Enterprise account verification status',
    prefer_lang     ENUM('zh_cn', 'en', 'th') NOT NULL DEFAULT 'en',
    email_verified  BOOLEAN NOT NULL DEFAULT FALSE,
    last_login_at   DATETIME NULL,
    last_login_ip   VARCHAR(45) NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role_status (role, status),
    INDEX idx_enterprise_status (enterprise_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTE: Enterprise accounts require admin activation via PUT /api/users/{id}/activate-enterprise
-- enterprise_status field: NULL for non-enterprise users, 'pending' when enterprise registers,
-- 'enterprise_verified' when admin activates enterprise status via admin API.
```

#### 1.2.2 Students Table
```sql
CREATE TABLE students (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL UNIQUE,
    name            VARCHAR(255) NOT NULL,
    nationality     VARCHAR(100) NULL,
    phone           VARCHAR(50) NULL,
    avatar          VARCHAR(500) NULL,
    birth_date      DATE NULL,
    gender          ENUM('male', 'female', 'other') NULL,
    address         TEXT NULL,
    bio             TEXT NULL,
    verified        BOOLEAN NOT NULL DEFAULT FALSE,
    prefer_lang     ENUM('zh_cn', 'en', 'th') NOT NULL DEFAULT 'en',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_nationality (nationality),
    INDEX idx_verified (verified),
    INDEX idx_prefer_lang (prefer_lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.3 Enterprises Table
```sql
CREATE TABLE enterprises (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL UNIQUE,
    company_name    VARCHAR(500) NOT NULL,
    industry        VARCHAR(200) NULL,
    logo            VARCHAR(500) NULL,
    scale           ENUM('small', 'medium', 'large', 'enterprise') NULL,
    description     TEXT NULL,
    website         VARCHAR(500) NULL,
    address         TEXT NULL,
    contact_name    VARCHAR(255) NULL,
    contact_phone   VARCHAR(50) NULL,
    verified        BOOLEAN NOT NULL DEFAULT FALSE,
    prefer_lang     ENUM('zh_cn', 'en', 'th') NOT NULL DEFAULT 'en',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_industry (industry),
    INDEX idx_verified (verified),
    INDEX idx_prefer_lang (prefer_lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.4 Admins Table
```sql
CREATE TABLE admins (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL UNIQUE,
    name            VARCHAR(255) NOT NULL,
    phone           VARCHAR(50) NULL,
    avatar          VARCHAR(500) NULL,
    prefer_lang     ENUM('zh_cn', 'en', 'th') NOT NULL DEFAULT 'en',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_prefer_lang (prefer_lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.5 Languages Table
```sql
CREATE TABLE languages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`           VARCHAR(100) NOT NULL UNIQUE,
    zh_cn           TEXT NULL,
    en              TEXT NULL,
    th              TEXT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default language keys
INSERT INTO languages (`key`, zh_cn, en, th) VALUES
('website_name', '湖北豪睿国际人才服务有限公司', 'HorizonHR International Talent Service', 'บริการบุคลากรระหว่างประเทศ HorizonHR'),
('home_banner_title', '连接东南亚青年与中国高校', 'Connect Southeast Asian Youth with Chinese Universities', 'เชื่อมต่อเยาวชนเอเชียตะวันออกเฉียงใต้กับมหาวิทยาลัยจีน'),
('study_in_china', '留学中国', 'Study in China', 'เรียนในประเทศจีน'),
('talent_pool', '人才库', 'Talent Pool', 'กลุ่มบุคลากร'),
('corporate', '企业合作', 'Corporate Cooperation', 'ความร่วมมือองค์กร'),
('seminars', '研讨会中心', 'Seminar Center', 'ศูนย์สัมมนา'),
('news', '新闻资讯', 'News & Insights', 'ข่าวสารและข้อมูลเชิงลึก'),
('contact', '联系我们', 'Contact Us', 'ติดต่อเรา'),
('about', '关于我们', 'About Us', 'เกี่ยวกับเรา');
```

#### 1.2.4.1 Language Settings Table (for FEAT-052)
```sql
CREATE TABLE language_settings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code`          VARCHAR(10) NOT NULL UNIQUE,
    name            VARCHAR(100) NOT NULL,
    native_name     VARCHAR(100) NOT NULL,
    `flag`          VARCHAR(10) DEFAULT '🌐',
    description     VARCHAR(255) NULL,
    is_active       BOOLEAN DEFAULT TRUE,
    `position`      INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_code (`code`),
    INDEX idx_position (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default language settings
INSERT INTO language_settings (`code`, name, native_name, flag, is_active, `position`) VALUES
('en', 'English', 'English', '🇬🇧', TRUE, 1),
('zh_cn', '中文简体', '简体中文', '🇨🇳', TRUE, 2),
('th', 'ภาษาไทย', 'ภาษาไทย', '🇹🇭', TRUE, 3);
```

#### 1.2.5 Settings Table
```sql
CREATE TABLE settings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`           VARCHAR(100) NOT NULL UNIQUE,
    value           TEXT NULL,
    type            ENUM('string', 'text', 'boolean', 'number', 'json') NOT NULL DEFAULT 'string',
    `group`         VARCHAR(50) NOT NULL DEFAULT 'general',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_key_group (`key`, `group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT INTO settings (`key`, value, type, `group`) VALUES
-- Website General
('site_name', 'HorizonHR', 'string', 'website'),
('site_name_zh_cn', '湖北豪睿国际人才服务有限公司', 'string', 'website'),
('site_name_en', 'HorizonHR International Talent Service', 'string', 'website'),
('site_name_th', 'บริการบุคลากรระหว่างประเทศ HorizonHR', 'string', 'website'),
('logo', '/assets/images/logo.png', 'string', 'website'),
('logo_secondary', '/assets/images/logo-white.png', 'string', 'website'),
('favicon', '/assets/images/favicon.ico', 'string', 'website'),
('default_language', 'en', 'string', 'website'),
('contact_email', 'contact@horizonhr.com', 'string', 'website'),
('contact_phone', '+86 27-8888-8888', 'string', 'website'),
('contact_address', '', 'text', 'website'),
('copyright', '© 2026 Hubei Horizon International. All Rights Reserved.', 'string', 'website'),

-- SEO
('seo_title', 'HorizonHR International Talent Service', 'string', 'seo'),
('seo_title_zh_cn', '湖北豪睿国际人才服务有限公司', 'string', 'seo'),
('seo_title_en', 'HorizonHR International Talent Service', 'string', 'seo'),
('seo_title_th', 'บริการบุคลากรระหว่างประเทศ HorizonHR', 'string', 'seo'),
('seo_description', '', 'text', 'seo'),
('seo_keywords', '', 'text', 'seo'),
('og_image', '/assets/images/og-image.jpg', 'string', 'seo'),

-- Social Media
('social_wechat', '', 'string', 'social'),
('social_whatsapp', '', 'string', 'social'),
('social_line', '', 'string', 'social'),
('social_facebook', '', 'string', 'social'),
('social_linkedin', '', 'string', 'social'),

-- SMTP
('smtp_driver', 'smtp', 'string', 'smtp'),
('smtp_host', '', 'string', 'smtp'),
('smtp_port', '587', 'number', 'smtp'),
('smtp_encryption', 'tls', 'string', 'smtp'),
('smtp_username', '', 'string', 'smtp'),
('smtp_password', '', 'string', 'smtp'),
('smtp_from_address', '', 'string', 'smtp'),
('smtp_from_name', 'HorizonHR', 'string', 'smtp'),

-- System
('maintenance_mode', 'false', 'boolean', 'system'),
('debug_mode', 'false', 'boolean', 'system'),
('log_activity', 'true', 'boolean', 'system'),
('session_timeout', '120', 'number', 'system'),
('require_2fa_admin', 'false', 'boolean', 'system'),
('allow_student_registration', 'true', 'boolean', 'system'),
('allow_enterprise_registration', 'true', 'boolean', 'system'),
('enable_video_interviews', 'true', 'boolean', 'system'),
('enable_resume_upload', 'true', 'boolean', 'system');
```

#### 1.2.6 Pages Table (CMS)
```sql
CREATE TABLE pages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    title_zh_cn     VARCHAR(500) NOT NULL,
    title_en        VARCHAR(500) NOT NULL,
    title_th        VARCHAR(500) NOT NULL,
    content_zh_cn   LONGTEXT NULL,
    content_en      LONGTEXT NULL,
    content_th      LONGTEXT NULL,
    meta_title_zh_cn VARCHAR(500) NULL,
    meta_title_en   VARCHAR(500) NULL,
    meta_title_th   VARCHAR(500) NULL,
    meta_desc_zh_cn TEXT NULL,
    meta_desc_en    TEXT NULL,
    meta_desc_th    TEXT NULL,
    status          ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    type            ENUM('page', 'announcement') NOT NULL DEFAULT 'page',
    order_num       INT NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.7 Posts Table (CMS)
```sql
CREATE TABLE posts (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id         BIGINT UNSIGNED NULL,
    title_zh_cn     VARCHAR(500) NOT NULL,
    title_en        VARCHAR(500) NOT NULL,
    title_th        VARCHAR(500) NOT NULL,
    content_zh_cn   LONGTEXT NULL,
    content_en      LONGTEXT NULL,
    content_th      LONGTEXT NULL,
    meta_title_zh_cn VARCHAR(500) NULL,
    meta_title_en   VARCHAR(500) NULL,
    meta_title_th   VARCHAR(500) NULL,
    meta_desc_zh_cn TEXT NULL,
    meta_desc_en    TEXT NULL,
    meta_desc_th    TEXT NULL,
    category        ENUM('company_news', 'industry_news', 'study_abroad', 'recruitment') NOT NULL,
    thumbnail       VARCHAR(500) NULL,
    status          ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    view_count      INT NOT NULL DEFAULT 0,
    published_at   DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.8 Resumes Table
```sql
CREATE TABLE resumes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id      BIGINT UNSIGNED NOT NULL,
    file_path       VARCHAR(500) NOT NULL,
    file_name       VARCHAR(255) NOT NULL,
    file_type       ENUM('pdf', 'doc', 'docx', 'jpg', 'png') NOT NULL,
    file_size       INT NOT NULL COMMENT 'Size in bytes',
    visibility      ENUM('admin_only', 'enterprise_visible', 'public') NOT NULL DEFAULT 'enterprise_visible',
    status          ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by     BIGINT UNSIGNED NULL,
    reviewed_at     DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student (student_id),
    INDEX idx_visibility (visibility),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.9 Talent Cards Table
```sql
CREATE TABLE talent_cards (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id      BIGINT UNSIGNED NOT NULL UNIQUE,
    display_name    VARCHAR(255) NOT NULL,
    major           VARCHAR(255) NULL,
    education       VARCHAR(100) NULL,
    university      VARCHAR(255) NULL,
    languages       JSON NULL COMMENT 'Array of language skills',
    skills          JSON NULL COMMENT 'Array of skills',
    work_experience JSON NULL COMMENT 'Array of work experiences',
    job_intention   VARCHAR(500) NULL,
    status          ENUM('hidden', 'visible', 'featured') NOT NULL DEFAULT 'hidden',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.10 Jobs Table
```sql
CREATE TABLE jobs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enterprise_id   BIGINT UNSIGNED NOT NULL,
    title           VARCHAR(500) NOT NULL,
    description     TEXT NOT NULL,
    requirements    TEXT NULL,
    location        VARCHAR(255) NULL,
    salary_min      INT NULL,
    salary_max      INT NULL,
    salary_currency VARCHAR(10) DEFAULT 'CNY',
    job_type        ENUM('full_time', 'part_time', 'contract', 'internship') NOT NULL,
    status          ENUM('draft', 'published', 'closed', 'expired') NOT NULL DEFAULT 'draft',
    published_at    DATETIME NULL,
    expires_at      DATETIME NULL,
    view_count      INT NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (enterprise_id) REFERENCES enterprises(id) ON DELETE CASCADE,
    INDEX idx_enterprise (enterprise_id),
    INDEX idx_status (status),
    INDEX idx_location (location),
    INDEX idx_job_type (job_type),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.11 Applications Table
```sql
CREATE TABLE applications (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id          BIGINT UNSIGNED NOT NULL,
    student_id      BIGINT UNSIGNED NOT NULL,
    resume_id       BIGINT UNSIGNED NULL,
    cover_letter    TEXT NULL,
    status          ENUM('pending', 'reviewed', 'interviewed', 'accepted', 'rejected', 'withdrawn') NOT NULL DEFAULT 'pending',
    notes           TEXT NULL,
    applied_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE SET NULL,
    UNIQUE KEY uk_job_student (job_id, student_id),
    INDEX idx_job (job_id),
    INDEX idx_student (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.12 Interviews Table
```sql
CREATE TABLE interviews (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creator_id      BIGINT UNSIGNED NOT NULL COMMENT 'User who created interview (admin or enterprise)',
    enterprise_id   BIGINT UNSIGNED NULL,
    student_id      BIGINT UNSIGNED NULL,
    job_id          BIGINT UNSIGNED NULL,
    title           VARCHAR(500) NOT NULL,
    scheduled_at    DATETIME NOT NULL,
    duration        INT NOT NULL DEFAULT 30 COMMENT 'Duration in minutes',
    room_id         VARCHAR(100) NULL COMMENT 'WebRTC room identifier',
    room_token      VARCHAR(500) NULL COMMENT 'Token for room access',
    status          ENUM('scheduled', 'in_progress', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'scheduled',
    reminder_sent   BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (enterprise_id) REFERENCES enterprises(id) ON DELETE SET NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL,
    INDEX idx_creator (creator_id),
    INDEX idx_student (student_id),
    INDEX idx_enterprise (enterprise_id),
    INDEX idx_status (status),
    INDEX idx_scheduled_at (scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.13 Interview Records Table
```sql
CREATE TABLE interview_records (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    interview_id    BIGINT UNSIGNED NOT NULL,
    participant_id  BIGINT UNSIGNED NOT NULL COMMENT 'User or student participant',
    participant_type ENUM('admin', 'enterprise', 'student') NOT NULL,
    joined_at       DATETIME NOT NULL,
    left_at         DATETIME NULL,
    duration_sec    INT NULL,
    connection_quality VARCHAR(50) NULL COMMENT 'good, medium, poor',
    notes           TEXT NULL,
    result          ENUM('pass', 'fail', 'pending', 'no_show') NULL,
    rating          INT NULL COMMENT '1-5 rating',
    recording_url   VARCHAR(500) NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (interview_id) REFERENCES interviews(id) ON DELETE CASCADE,
    INDEX idx_interview (interview_id),
    INDEX idx_participant (participant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.14 Seminars Table
```sql
CREATE TABLE seminars (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title_zh_cn     VARCHAR(500) NOT NULL,
    title_en        VARCHAR(500) NOT NULL,
    title_th        VARCHAR(500) NOT NULL,
    desc_zh_cn      TEXT NULL,
    desc_en         TEXT NULL,
    desc_th         TEXT NULL,
    speaker_name    VARCHAR(255) NOT NULL,
    speaker_title   VARCHAR(255) NULL,
    speaker_bio     TEXT NULL,
    speaker_avatar  VARCHAR(500) NULL,
    thumbnail       VARCHAR(500) NULL,
    stream_url      VARCHAR(500) NULL,
    stream_key      VARCHAR(255) NULL,
    target_audience ENUM('students', 'enterprises', 'both') NOT NULL DEFAULT 'both',
    status          ENUM('scheduled', 'live', 'ended', 'cancelled') NOT NULL DEFAULT 'scheduled',
    permission      ENUM('public', 'registered') NOT NULL DEFAULT 'registered',
    max_viewers     INT NULL,
    current_viewers INT NOT NULL DEFAULT 0,
    max_concurrent_viewers INT NOT NULL DEFAULT 10000 COMMENT 'System capacity: supports 10,000+ concurrent viewers',
    starts_at       DATETIME NOT NULL,
    duration_min    INT NOT NULL DEFAULT 60,
    ended_at        DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_target (target_audience),
    INDEX idx_starts_at (starts_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTE: The max_concurrent_viewers field specifies system capacity.
-- The system architecture supports 10,000+ concurrent viewers per seminar
-- through CDN distribution, load balancing, and horizontal scaling.
```

#### 1.2.15 Seminar Registrations Table
```sql
CREATE TABLE seminar_registrations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seminar_id      BIGINT UNSIGNED NOT NULL,
    email           VARCHAR(255) NOT NULL,
    name            VARCHAR(255) NOT NULL,
    phone           VARCHAR(50) NULL,
    user_id         BIGINT UNSIGNED NULL COMMENT 'Null if not logged in user',
    reminder_sent   BOOLEAN NOT NULL DEFAULT FALSE,
    reminder_sent_at DATETIME NULL,
    registered_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (seminar_id) REFERENCES seminars(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_seminar_email (seminar_id, email),
    INDEX idx_seminar (seminar_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.16 Seminar Recordings Table
```sql
CREATE TABLE seminar_recordings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seminar_id      BIGINT UNSIGNED NOT NULL UNIQUE,
    title           VARCHAR(500) NOT NULL,
    video_url       VARCHAR(500) NOT NULL,
    thumbnail_url   VARCHAR(500) NULL,
    duration_sec    INT NOT NULL,
    playback_speeds JSON NOT NULL DEFAULT '["0.5x", "0.75x", "1x", "1.25x", "1.5x", "1.75x", "2x"]' COMMENT 'Supported playback speeds',
    default_speed   VARCHAR(10) NOT NULL DEFAULT '1x' COMMENT 'Default playback speed',
    view_count      INT NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (seminar_id) REFERENCES seminars(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Playback speed control: Supported speeds range from 0.5x to 2x
-- Frontend player allows users to select playback speed
-- Backend tracks playback speed preference per user if needed
```

#### 1.2.16.1 Seminar Messages Table (Danmu/Bullet Comments)
```sql
CREATE TABLE seminar_messages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seminar_id      BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NULL COMMENT 'Null if anonymous user',
    user_name       VARCHAR(255) NOT NULL,
    content         VARCHAR(500) NOT NULL COMMENT 'Danmu message content (max 100 chars)',
    color           VARCHAR(20) NOT NULL DEFAULT '#FFFFFF' COMMENT 'Message color in hex',
    position        ENUM('scroll', 'top', 'bottom') NOT NULL DEFAULT 'scroll' COMMENT 'Danmu position',
    font_size       INT NOT NULL DEFAULT 18 COMMENT 'Font size in px',
    send_at         DATETIME NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (seminar_id) REFERENCES seminars(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_seminar (seminar_id),
    INDEX idx_send_at (send_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Danmu (Bullet Comments) System:
-- - Users can send danmu messages during live seminars
-- - Messages are displayed as scrolling text overlay on video
-- - Real-time broadcast via WebSocket for live delivery
-- - Messages are stored for playback replay (if enabled)
-- - Rate limiting: max 10 danmu per user per minute to prevent spam
```

#### 1.2.17 Universities Table
```sql
CREATE TABLE universities (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_zh_cn      VARCHAR(500) NOT NULL,
    name_en         VARCHAR(500) NOT NULL,
    name_th         VARCHAR(500) NULL,
    logo            VARCHAR(500) NULL,
    cover_image     VARCHAR(500) NULL,
    location        VARCHAR(255) NULL,
    location_city   VARCHAR(100) NULL,
    location_region VARCHAR(100) NULL,
    website         VARCHAR(500) NULL,
    description     TEXT NULL,
    majors          JSON NULL COMMENT 'Array of offered majors',
    program_types   JSON NULL COMMENT 'Array of program types: vocational, bachelor, master, language',
    established_year INT NULL,
    ranking         INT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_location (location),
    INDEX idx_program_types (program_types)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.18 Password Resets Table
```sql
CREATE TABLE password_resets (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    email           VARCHAR(255) NOT NULL,
    token           VARCHAR(255) NOT NULL,
    expires_at      DATETIME NOT NULL,
    used_at         DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.19 Email Confirmations Table
```sql
CREATE TABLE email_confirmations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    email           VARCHAR(255) NOT NULL,
    token           VARCHAR(255) NOT NULL,
    confirmed_at    DATETIME NULL,
    expires_at      DATETIME NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.20 Consent Logs Table
```sql
CREATE TABLE consent_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    consent_type    VARCHAR(100) NOT NULL,
    consented_at    DATETIME NOT NULL,
    ip_address      VARCHAR(45) NULL,
    user_agent      TEXT NULL,
    withdrawal_at   DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_consent_type (consent_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.21 Social Authentications Table
```sql
CREATE TABLE social_authentications (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    provider        ENUM('google', 'facebook', 'linkedin', 'wechat') NOT NULL,
    provider_id     VARCHAR(255) NOT NULL COMMENT 'User ID from the social provider',
    provider_email VARCHAR(255) NULL COMMENT 'Email from provider (may differ from account email)',
    provider_name   VARCHAR(255) NULL COMMENT 'Display name from provider',
    provider_avatar VARCHAR(500) NULL COMMENT 'Avatar URL from provider',
    access_token    TEXT NULL COMMENT 'Encrypted access token',
    refresh_token   TEXT NULL COMMENT 'Encrypted refresh token (if applicable)',
    token_expires_at DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_provider_id (provider, provider_id),
    UNIQUE KEY uk_user_provider (user_id, provider),
    INDEX idx_provider (provider),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2.22 Contacts Table
```sql
CREATE TABLE contacts (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) NOT NULL,
    phone           VARCHAR(50) NULL,
    subject         VARCHAR(500) NULL,
    message         TEXT NOT NULL,
    ip_address      VARCHAR(45) NULL,
    status          ENUM('unread', 'read', 'replied') NOT NULL DEFAULT 'unread',
    replied_at      DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Corresponds to contact form on the Contact Us page (REQ IV.A.8)
-- Submissions trigger notification to admin contact_email in settings
```

---

## 2. Module Architecture

### 2.1 System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                    HRINT System Architecture                            │
│                                                                                          │
│  ┌─────────────────────────────────────────────────────────────────────────────────┐   │
│  │                                 CLIENT LAYER                                      │   │
│  │  ┌─────────────────┐   ┌─────────────────┐   ┌─────────────────┐                │   │
│  │  │   Web Browser   │   │   Mobile Web    │   │   Tablet Web    │                │   │
│  │  │   (Chrome,      │   │   (Responsive)  │   │   (Responsive)  │                │   │
│  │  │    Firefox,     │   │                 │   │                 │                │   │
│  │  │    Edge)        │   │                 │   │                 │                │   │
│  │  └────────┬────────┘   └────────┬────────┘   └────────┬────────┘                │   │
│  │           │                     │                     │                           │   │
│  │           └─────────────────────┼─────────────────────┘                           │   │
│  │                                 │                                                 │   │
│  └─────────────────────────────────┼─────────────────────────────────────────────────┘   │
│                                    │                                                       │
│  ┌─────────────────────────────────┼─────────────────────────────────────────────────┐   │
│  │                                 ▼           FRONTEND LAYER                          │   │
│  │  ┌─────────────────────────────────────────────────────────────────────────────┐  │   │
│  │  │              Vue 3 SPA (Vite + Vue Router + Pinia + Element Plus)             │  │   │
│  │  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │  │   │
│  │  │  │   Public     │  │   Student    │  │   Enterprise │  │   Admin      │     │  │   │
│  │  │  │   Pages      │  │   Portal     │  │   Portal     │  │   Portal     │     │  │   │
│  │  │  │  (index,     │  │  (dashboard, │  │  (dashboard, │  │  (all pages) │     │  │   │
│  │  │  │   about,     │  │   profile,   │  │   jobs,      │  │              │     │  │   │
│  │  │  │   contact)   │  │   resume,    │  │   interviews,│  │              │     │  │   │
│  │  │  │              │  │   seminars)  │  │   talent)    │  │              │     │  │   │
│  │  │  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘     │  │   │
│  │  └─────────────────────────────────────────────────────────────────────────────┘  │   │
│  │                                    │                                               │   │
│  └────────────────────────────────────┼───────────────────────────────────────────────┘   │
│                                       │                                                      │
│  ┌────────────────────────────────────┼───────────────────────────────────────────────┐   │
│  │                                    ▼           API GATEWAY LAYER                    │   │
│  │  ┌─────────────────────────────────────────────────────────────────────────────┐  │   │
│  │  │                         API Router & Middleware                              │  │   │
│  │  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │  │   │
│  │  │  │   Auth       │  │   Rate       │  │   CORS      │  │   Session/   │     │  │   │
│  │  │  │   Middleware │  │   Limiter    │  │   Filter    │  │   JWT        │     │  │   │
│  │  │  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘     │  │   │
│  │  └─────────────────────────────────────────────────────────────────────────────┘  │   │
│  │                                    │                                               │   │
│  └────────────────────────────────────┼───────────────────────────────────────────────┘   │
│                                       │                                                      │
│  ┌────────────────────────────────────┼───────────────────────────────────────────────┐   │
│  │                                    ▼           BACKEND LAYER (PHP)                  │   │
│  │  ┌─────────────────────────────────────────────────────────────────────────────┐  │   │
│  │  │                           Application Core                                     │  │   │
│  │  │  ┌─────────────────────────────────────────────────────────────────────────┐   │  │   │
│  │  │  │                         Controllers                                     │   │  │   │
│  │  │  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐       │   │  │   │
│  │  │  │  │   Auth      │ │   User      │ │   Language  │ │   Setting   │       │   │  │   │
│  │  │  │  │ Controller  │ │ Controller  │ │ Controller  │ │ Controller  │       │   │  │   │
│  │  │  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘       │   │  │   │
│  │  │  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐       │   │  │   │
│  │  │  │  │   Page      │ │   Post      │ │   Resume    │ │   Job       │       │   │  │   │
│  │  │  │  │ Controller  │ │ Controller  │ │ Controller  │ │ Controller  │       │   │  │   │
│  │  │  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘       │   │  │   │
│  │  │  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐       │   │  │   │
│  │  │  │  │ Application │ │ Interview   │ │   Seminar   │ │ University  │       │   │  │   │
│  │  │  │  │ Controller  │ │ Controller  │ │ Controller  │ │ Controller  │       │   │  │   │
│  │  │  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘       │   │  │   │
│  │  │  └─────────────────────────────────────────────────────────────────────────┘   │  │   │
│  │  │  ┌─────────────────────────────────────────────────────────────────────────┐   │  │   │
│  │  │  │                         Services                                        │   │  │   │
│  │  │  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐       │   │  │   │
│  │  │  │  │ AuthService │ │ UserService  │ │ CMSService  │ │ JobService  │       │   │  │   │
│  │  │  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘       │   │  │   │
│  │  │  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐       │   │  │   │
│  │  │  │  │InterviewSvc │ │ SeminarSvc   │ │ ResumeSvc   │ │EmailService │       │   │  │   │
│  │  │  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘       │   │  │   │
│  │  │  └─────────────────────────────────────────────────────────────────────────┘   │  │   │
│  │  │  ┌─────────────────────────────────────────────────────────────────────────┐   │  │   │
│  │  │  │                         Models (Eloquent)                               │   │  │   │
│  │  │  │  User, Student, Enterprise, Resume, Job, Application, Interview,      │   │  │   │
│  │  │  │  Seminar, SeminarRegistration, SeminarRecording, Page, Post,          │   │  │   │
│  │  │  │  Language, Setting, University, TalentCard, InterviewRecord           │   │  │   │
│  │  │  └─────────────────────────────────────────────────────────────────────────┘   │  │   │
│  │  └─────────────────────────────────────────────────────────────────────────────┘  │   │
│  └─────────────────────────────────────────────────────────────────────────────────┘   │
│                                       │                                                      │
│  ┌────────────────────────────────────┼───────────────────────────────────────────────┐   │
│  │                                    ▼           DATA LAYER                         │   │
│  │  ┌─────────────────┐   ┌─────────────────┐   ┌─────────────────┐               │   │
│  │  │    MariaDB       │   │   File Storage  │   │   External APIs │               │   │
│  │  │   (Primary DB)   │   │   (Local/OSS)   │   │   (WebRTC,      │               │   │
│  │  │                  │   │                  │   │    Streaming)   │               │   │
│  │  └─────────────────┘   └─────────────────┘   └─────────────────┘               │   │
│  │                                                                                   │
│  │  ┌─────────────────┐   ┌─────────────────┐   ┌─────────────────┐               │   │
│  │  │  Redis/Session  │   │   SMTP/Email    │   │   Analytics     │               │   │
│  │  │  (Cache/Session) │   │   (Sendmail/    │   │   (Dashboard)   │               │   │
│  │  │                  │   │    Mailgun)     │   │                  │               │   │
│  │  └─────────────────┘   └─────────────────┘   └─────────────────┘               │   │
│  └─────────────────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Module Dependency Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                               Module Dependencies                                        │
│                                                                                          │
│                           ┌───────────────────────┐                                       │
│                           │   Authentication      │                                       │
│                           │       Module           │                                       │
│                           └───────────┬───────────┘                                       │
│                                       │                                                   │
│           ┌───────────────────────────┼───────────────────────────┐                       │
│           │                           │                           │                       │
│           ▼                           ▼                           ▼                       │
│    ┌──────────────┐          ┌──────────────┐          ┌──────────────┐               │
│    │  Student     │          │  Enterprise  │          │    Admin     │               │
│    │   Module     │          │   Module     │          │   Module     │               │
│    └──────┬───────┘          └──────┬───────┘          └──────┬───────┘               │
│           │                          │                          │                        │
│           │    ┌─────────────────────┼─────────────────────┐    │                        │
│           │    │                     │                     │    │                        │
│           ▼    ▼                     │                     │    ▼                        │
│    ┌──────────────┐          ┌──────────────┐           │    ┌──────────────┐         │
│    │   Resume     │          │     Job      │           │    │     CMS      │         │
│    │   Module     │          │   Module     │           │    │   Module     │         │
│    └──────┬───────┘          └──────┬───────┘           │    └──────────────┘         │
│           │                          │                    │                               │
│           │         ┌────────────────┼────────────────┐  │                               │
│           │         │                │                │  │                               │
│           ▼         │                ▼                ▼  │                               │
│    ┌──────────────┐││      ┌──────────────┐   ┌──────────────┐    │                    │
│    │Talent Card    │││      │  Application │   │   Settings   │    │                    │
│    │   Module      │││      │   Module     │   │   Module     │    │                    │
│    └───────────────┘││      └───────────────┘   └───────────────┘    │                    │
│                      ││                                                    │                    │
│                      ││      ┌──────────────────────────────────────────┘                  │
│                      ││      │                                                           │
│                      ││      ▼                                                           │
│                      ││ ┌──────────────┐                                                │
│                      ││ │  Interview   │                                                │
│                      ││ │   Module     │◄────── WebRTC Service                          │
│                      ││ └──────────────┘                                                │
│                      ││                                                                 │
│                      │└──────────────────────────────────────────────────────────────────┘
│                      │
│                      ▼
│               ┌──────────────┐
│               │   Seminar    │
│               │   Module     │
│               └──────────────┘
│                      │
│                      ▼
│               ┌──────────────┐
│               │   Language   │
│               │   Module     │
│               └──────────────┘
│
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

### 2.3 Module Details

#### 2.3.1 Authentication Module
```php
// Module: Auth
// Responsibility: User authentication, registration, password reset
// Files: AuthController, AuthService, AuthMiddleware

Classes:
- AuthController: Handles login, logout, register, forgot-password, reset-password
- AuthService: Business logic for authentication
- AuthMiddleware: Validates JWT/Session tokens

Dependencies:
- Users Model
- EmailConfirmations Model
- PasswordResets Model
- EmailService
```

#### 2.3.2 Social Authentication Module
```php
// Module: SocialAuth
// Responsibility: Social login integration (Google, Facebook, LinkedIn, WeChat)
// Files: SocialAuthController, SocialAuthService, SocialProviderAdapter

Classes:
- SocialAuthController: Handles social auth redirects and callbacks
- SocialAuthService: Business logic for social authentication
- SocialProviderAdapter: Interface for provider-specific implementations
- GoogleAdapter: Google OAuth2 implementation
- FacebookAdapter: Facebook OAuth2 implementation
- LinkedInAdapter: LinkedIn OAuth2 implementation
- WeChatAdapter: WeChat OAuth2 implementation (with China-specific handling)

Dependencies:
- Users Model
- SocialAuthentications Model
- AuthService

External Integration:
- Google OAuth2 API
- Facebook Graph API
- LinkedIn API
- WeChat Open Platform API

Notes:
- Admin accounts cannot use social login (email-only)
- Enterprise accounts via social may require additional verification
- WeChat requires ICP备案 and WeChat Open Platform registration
```

#### 2.3.3 CMS Module
```php
// Module: CMS
// Responsibility: Pages and Posts (news/announcements) management
// Files: PageController, PostController, CMSSService

Classes:
- PageController: CRUD for pages (About, Contact, etc.)
- PostController: CRUD for news posts
- CMSService: Translation handling, content management

Dependencies:
- Pages Model
- Posts Model
- Language Model
- Settings Model (for SEO)
```

#### 2.3.4 Student Module
```php
// Module: Student
// Responsibility: Student profile, resume, applications, seminars
// Files: StudentController, ResumeController, ApplicationController

Classes:
- StudentController: Profile management, dashboard data
- ResumeController: Resume upload, visibility control
- ApplicationController: Job applications
- TalentCardController: Talent card generation/display

Dependencies:
- Students Model
- Resumes Model
- TalentCards Model
- Applications Model
- Languages Model
```

#### 2.3.5 Enterprise Module
```php
// Module: Enterprise
// Responsibility: Company profile, job posting, talent search, interviews
// Files: EnterpriseController, JobController, TalentController

Classes:
- EnterpriseController: Company profile management
- JobController: Job CRUD, publishing
- TalentController: Search students, view talent cards
- InterviewController: Create/manage interviews

Dependencies:
- Enterprises Model
- Jobs Model
- Applications Model
- TalentCards Model
- Interviews Model
```

#### 2.3.6 Interview Module (WebRTC)
```php
// Module: Interview
// Responsibility: Video interview scheduling and WebRTC management
// Files: InterviewController, WebRTCService, InterviewMiddleware

Classes:
- InterviewController: CRUD for interviews, scheduling
- WebRTCService: Room creation, token generation, signaling
- InterviewMiddleware: Validates interview access

Dependencies:
- Interviews Model
- InterviewRecords Model
- Students Model
- Enterprises Model
- Jobs Model

External Integration:
- WebRTC (PeerJS or similar)
- TURN/STUN servers for NAT traversal
```

#### 2.3.7 Seminar Module
```php
// Module: Seminar
// Responsibility: Seminar management, live streaming, playback, danmu
// Files: SeminarController, StreamService, SeminarRegistrationController, DanmuService

Classes:
- SeminarController: CRUD for seminars
- StreamService: Integration with Tencent CSS / TRTC CDN Live (TRTCLiveService)
- SeminarRegistrationController: Handle registrations
- RecordingService: Auto-record and process playback
- DanmuService: Real-time danmu/bullet comment handling

Danmu (Bullet Comments) System:
- Real-time message broadcast via WebSocket
- WebSocket endpoint: wss://api.horizonhr.com/ws/seminar/{seminar_id}/danmu
- Message format: {user_id, user_name, content, color, position, font_size}
- Rate limiting: 10 messages per user per minute
- Display duration on screen: 5 seconds for scroll, 3 seconds for top/bottom
- For playback: danmu can be replayed synced to video timestamp

Dependencies:
- Seminars Model
- SeminarRegistrations Model
- SeminarRecordings Model
- SeminarMessages Model
- EmailService (for reminders)

External Integration:
- Tencent CSS (TRTC CDN Live) / Streaming CDN
- Recording storage
- WebSocket Server (for real-time danmu)

Playback Speed Control:
- Video player supports 0.5x, 0.75x, 1x, 1.25x, 1.5x, 1.75x, 2x speeds
- Speed preference can be persisted per user
- Backend stores supported speeds in seminar_recordings.playback_speeds

10,000+ Concurrent Viewers Capacity:
- System uses CDN-based streaming with horizontal scaling
- max_concurrent_viewers field in seminars table indicates capacity
- Load balancers distribute viewer load across multiple edge nodes
- Auto-scaling triggers when current_viewers exceeds 80% of capacity
```

#### 2.3.8 Settings Module
```php
// Module: Settings
// Responsibility: Website configuration, SMTP, system settings
// Files: SettingController, SettingService, ConfigManager

Classes:
- SettingController: CRUD for settings, SMTP test, file upload
- SettingService: Get/set configuration values
- ConfigManager: Environment config management

API Endpoints:
- GET /api/settings - Fetch all settings (grouped)
- PUT /api/settings - Batch update settings
- POST /api/settings/test-smtp - Test SMTP connection
- POST /api/settings/upload-logo - Upload site logo
- POST /api/settings/upload-favicon - Upload site favicon

Dependencies:
- Settings Model
- EmailService (for SMTP test)
```

#### 2.3.9 Language Module
```php
// Module: Language
// Responsibility: Multilingual content management
// Files: LanguageController, TranslationController, TranslationService, LocaleMiddleware

Classes:
- LanguageController: CRUD for language_settings (active languages list)
- TranslationController: CRUD for translation keys (languages table)
- TranslationService: Get translated content for active locale
- LocaleMiddleware: Set locale based on user preference or Accept-Language header

API Endpoints:
- GET/POST/PUT/DELETE /api/languages - Language settings (active language management)
- GET/POST/PUT/DELETE /api/translations - Translation key management
- GET /api/translations/export - Export translations to JSON
- POST /api/translations/import - Import translations from JSON

Dependencies:
- Languages Model (translation keys)
- LanguageSettings Model (active languages)
- Settings Model (default_language)
```

#### 2.3.10 Contact Module
```php
// Module: Contact
// Responsibility: Handle public contact/consultation form submissions
// Files: ContactController

Classes:
- ContactController: Accept form submission, validate, send admin notification

API Endpoints:
- POST /api/contact - Submit contact form (public, no auth required)

Dependencies:
- Contacts Model
- EmailService (admin notification)
- Settings Model (contact_email)
```

---

## 2.3.11 Dynamic Multi-Language Settings API

### Overview
Multi-language settings use a **dynamic key format** `{field}_{lang_code}` instead of fixed columns (e.g., `title_en`, `title_zh_cn`). This allows the system to support any number of languages without schema changes.

### Key Format Convention
```
{field}_{lang_code}
```

Examples:
- `site_name_en`, `site_name_zh_cn`, `site_name_th`
- `seo_title_en`, `seo_title_zh_cn`, `seo_title_th`
- `contact_address_en`, `contact_address_zh_cn`

### Backend Storage Schema

**Table: `settings`** (already defined in 1.2.5)

Multi-language values are stored as individual rows with composite keys:

```sql
-- Example: site_name in 3 languages
INSERT INTO settings (`key`, value, type, `group`) VALUES
('site_name_en', 'HorizonHR International Talent Service', 'string', 'website'),
('site_name_zh_cn', '湖北豪睿国际人才服务有限公司', 'string', 'website'),
('site_name_th', 'บริการบุคลากรระหว่างประเทศ HorizonHR', 'string', 'website');
```

**Table: `language_settings`** (already defined in 1.2.4.1)
- Contains the list of active languages with their metadata
- Frontend must query this first to know which languages are enabled

### API Response Format

#### GET /api/settings
Returns all settings grouped by `group`. Multi-language settings are returned as key arrays:

```json
{
  "website": {
    "site_name": "HorizonHR",
    "site_name_en": "HorizonHR International Talent Service",
    "site_name_zh_cn": "湖北豪睿国际人才服务有限公司",
    "site_name_th": "บริการบุคลากรระหว่างประเทศ HorizonHR",
    "logo": "/assets/images/logo.png",
    "default_language": "en"
  },
  "seo": {
    "seo_title": "HorizonHR International Talent Service",
    "seo_title_en": "HorizonHR International Talent Service",
    "seo_title_zh_cn": "湖北豪睿国际人才服务有限公司",
    "seo_title_th": "บริการบุคลากรระหว่างประเทศ HorizonHR"
  }
}
```

#### GET /api/languages
Returns active languages (used by frontend to render dynamic form fields):


```json
[
  { "code": "en", "name": "English", "native_name": "English", "flag": "🇬🇧", "is_active": true, "position": 1 },
  { "code": "zh_cn", "name": "中文简体", "native_name": "简体中文", "flag": "🇨🇳", "is_active": true, "position": 2 },
  { "code": "th", "name": "ภาษาไทย", "native_name": "ภิยาไทย", "flag": "🇹🇭", "is_active": true, "position": 3 }
]
```

#### PUT /api/settings
Batch update settings. For multi-language fields, send all language variants:

```json
{
  "site_name_en": "HorizonHR International Talent Service",
  "site_name_zh_cn": "湖北豪睿国际人才服务有限公司",
  "site_name_th": "บริการบุคลากรระหว่างประเทศ HorizonHR"
}
```

### Frontend Integration Pattern

**Required Flow:**
1. Fetch active languages: `GET /api/languages`
2. Use the `code` values to determine which fields to render
3. Fetch settings: `GET /api/settings`
4. For each active language `{lang_code}`, render a form field for `{field}_{lang_code}`


**Example Vue Component Logic:**
```javascript
// 1. Fetch languages first
const languages = await fetch('/api/languages').then(r => r.json())
  .then(langs => langs.filter(l => l.is_active).sort((a,b) => a.position - b.position));


// 2. Fetch settings
const settings = await fetch('/api/settings').then(r => r.json());

// 3. Render dynamic fields
const multiLangFields = ['site_name', 'seo_title', 'seo_description', 'contact_address'];
multiLangFields.forEach(field => {
  languages.forEach(lang => {
    const key = `${field}_${lang.code}`; // e.g., site_name_en
    const value = settings[key] || '';
    // render input field with label: `${field} (${lang.native_name})`
  });
});
```

### Key Design Decisions

| Decision | Rationale |
|----------|------------|
| Key format `{field}_{lang_code}` | Extensible — add new languages without schema changes |
| Separate rows per language | Simple storage, easy to query, no NULL handling |
| Fetch languages first | Frontend needs the active language list to know which fields to display |
| Language codes follow ISO standard | `en`, `zh_cn`, `th` match i18n conventions |

---

## 3. Traceability Matrix

### 3.1 Requirements to Components Mapping

| Requirement ID | Requirement Description | UI Module | Database Table | API Endpoint | Controller |
|--------------|------------------------|-----------|---------------|--------------|------------|
| **III.A.1** | Promote Company - Company Profile | About Us | pages, posts | /api/pages, /api/posts | PageController, PostController |
| **III.A.2** | Promote Students - Student Showcase | Talent Pool | students, talent_cards, resumes | /api/resumes, /api/talent | TalentCardController |
| **III.A.3** | Promote Corporate Clients - Partner Display | Corporate | enterprises, jobs | /api/enterprises, /api/jobs | EnterpriseController |
| **IV.B.1** | Admin Account - User Management | Admin Users | users, students, enterprises | /api/users | UserController |
| **IV.B.1** | Admin Account - Content Management | Admin Pages, News | pages, posts | /api/pages, /api/posts | PageController, PostController |
| **IV.B.1** | Admin Account - Resume Management | Admin Resumes | resumes, talent_cards | /api/resumes | ResumeController |
| **IV.B.1** | Admin Account - Interview Management | Admin Interviews | interviews, interview_records | /api/interviews | InterviewController |
| **IV.B.1** | Admin Account - Seminar Management | Admin Seminars | seminars, seminar_registrations | /api/seminars | SeminarController |
| **IV.B.2** | Student Account - Registration | Register Student | users, students | POST /api/auth/register | AuthController |
| **IV.B.2** | Student Account - Resume Management | Student Resume | resumes | /api/resumes | ResumeController |
| **IV.B.2** | Student Account - Seminars | Student Seminars | seminars, seminar_registrations | /api/seminars/{id}/register | SeminarController |
| **IV.B.2** | Student Account - Interview | Student Interviews | interviews | /api/interviews | InterviewController |
| **IV.B.2** | Student Account - Job Viewing | Student Applications | jobs, applications | /api/jobs, /api/applications | JobController |
| **IV.B.3** | Enterprise Account - Registration | Register Enterprise | users, enterprises | POST /api/auth/register | AuthController |
| **IV.B.3** | Enterprise Account - Job Management | Enterprise Jobs | jobs, applications | /api/jobs | JobController |
| **IV.B.3** | Enterprise Account - Talent Filtering | Enterprise Talent | talent_cards, resumes | /api/resumes | ResumeController |
| **IV.B.3** | Enterprise Account - Interview Management | Enterprise Interviews | interviews | /api/interviews | InterviewController |
| **IV.C.1** | Resume Upload - Formats | Student Resume | resumes | POST /api/resumes | ResumeController |
| **IV.C.1** | Resume Upload - Visibility Settings | Student Resume | resumes | PUT /api/resumes/{id}/visibility | ResumeController |
| **IV.C.2** | Video Interview - WebRTC | Interview Pages | interviews, interview_records | /api/interviews | InterviewController |
| **IV.C.3** | Online Seminar - Live + Playback | Seminar Center | seminars, seminar_recordings | /api/seminars | SeminarController |
| **IV.C.3** | Online Seminar - Reservation | Student Seminars | seminar_registrations | POST /api/seminars/{id}/register | SeminarController |
| **IV.C.3** | Online Seminar - Reminders | Backend Cron | seminar_registrations | Internal | SeminarService |
| **IV.C.3** | Registration Workflow - Email Confirm | Auth Flow | email_confirmations | POST /api/auth/confirm-email | AuthController |
| **IV.C.3** | Registration Workflow - Password Reset | Auth Flow | password_resets | POST /api/auth/forgot-password | AuthController |
| **CMS** | CMS - Page CRUD | Admin Pages | pages | /api/pages | PageController |
| **CMS** | CMS - Post CRUD | Admin News | posts | /api/posts | PostController |
| **Settings** | Settings - Website Config | Admin Settings | settings | GET/PUT /api/settings | SettingController |
| **Settings** | Settings - SMTP Config | Admin Settings SMTP | settings | GET/PUT /api/settings | SettingController |
| **Settings** | Settings - SMTP Test | Admin Settings SMTP | settings | POST /api/settings/test-smtp | SettingController |
| **Settings** | Settings - Logo Upload | Admin Settings | settings | POST /api/settings/upload-logo | SettingController |
| **Settings** | Settings - Favicon Upload | Admin Settings | settings | POST /api/settings/upload-favicon | SettingController |
| **Settings** | Settings - Language Settings | Admin Language | language_settings, languages | /api/languages, /api/translations | LanguageController, TranslationController |
| **Languages** | Multi-language Support | All Pages | languages, settings | /api/languages | LanguageController |
| **IV.A.8** | Contact Form Submission | Contact Us | contacts | POST /api/contact | ContactController |
| **IV.B.1** | Admin Account - Statistics | Admin Dashboard | All tables | GET /api/admin/stats | StatsController |

### 3.2 UI Page to Database Table Mapping

| UI Page | Database Tables | API Endpoints |
|---------|-----------------|---------------|
| Home (index) | pages, posts, settings, languages | /api/pages, /api/posts, /api/settings |
| About Us | pages | /api/pages |
| Study in China | pages, universities | /api/pages, /api/universities |
| Talent Pool | students, resumes, talent_cards | /api/resumes, /api/talent |
| Corporate Cooperation | enterprises, jobs | /api/enterprises, /api/jobs |
| Seminar Center | seminars, seminar_registrations, seminar_recordings | /api/seminars |
| News | posts | /api/posts |
| Contact Us | pages, settings | /api/pages, /api/settings |
| Login | users | /api/auth/* |
| Register Student | users, students | POST /api/auth/register |
| Register Enterprise | users, enterprises | POST /api/auth/register |
| Student Dashboard | applications, resumes, interviews, seminars | /api/applications, /api/interviews |
| Student Profile | students, resumes | /api/users/{id} |
| Student Resume | resumes | /api/resumes |
| Student Seminars | seminars, seminar_registrations | /api/seminars |
| Student Interviews | interviews | /api/interviews |
| Student Applications | applications, jobs | /api/applications |
| Enterprise Dashboard | jobs, applications, interviews | /api/jobs, /api/applications |
| Enterprise Profile | enterprises | /api/users/{id} |
| Enterprise Jobs | jobs, applications | /api/jobs, /api/applications |
| Enterprise Talent | talent_cards, resumes | /api/resumes |
| Enterprise Interviews | interviews | /api/interviews |
| Admin Dashboard | All tables | Various |
| Admin Users | users, students, enterprises | /api/users |
| Admin Resumes | resumes, students | /api/resumes |
| Admin Interviews | interviews, interview_records | /api/interviews |
| Admin Seminars | seminars, registrations, recordings | /api/seminars |
| Admin News | posts | /api/posts |
| Admin Pages | pages | /api/pages |
| Admin Settings | settings | /api/settings |
| Admin Language Settings | language_settings, languages | /api/languages, /api/translations |

### 3.3 Feature to API Endpoint Mapping

| Feature | Method | Endpoint | Description |
|---------|--------|----------|-------------|
| **Authentication** ||||
| User Register | POST | /api/auth/register | Register new user (student/enterprise) |
| User Login | POST | /api/auth/login | Login with email/password |
| User Logout | POST | /api/auth/logout | Logout current session |
| Forgot Password | POST | /api/auth/forgot-password | Send password reset email |
| Reset Password | POST | /api/auth/reset-password | Reset password with token |
| Confirm Email | POST | /api/auth/confirm-email | Confirm email address |
| **Users** ||||
| List Users | GET | /api/users | List all users (admin) |
| Get User | GET | /api/users/{id} | Get user details |
| Create User | POST | /api/users | Create user (admin) |
| Update User | PUT | /api/users/{id} | Update user |
| Delete User | DELETE | /api/users/{id} | Delete user (admin) |
| **Languages (Settings)** ||||
| List Language Settings | GET | /api/languages | Get all language settings (enabled languages) |
| Create Language | POST | /api/languages | Add new language |
| Update Language | PUT | /api/languages/{code} | Update language (name, native_name, is_active, position) |
| Delete Language | DELETE | /api/languages/{code} | Delete language (cannot delete 'en') |
| **Translations** ||||
| List Translations | GET | /api/translations | Get all translation keys |
| Create Translation Key | POST | /api/translations | Create new translation key |
| Update Translation | PUT | /api/translations/{key} | Update translation values |
| Delete Translation Key | DELETE | /api/translations/{key} | Delete translation key |
| Export Translations | GET | /api/translations/export | Export all translations to JSON |
| Import Translations | POST | /api/translations/import | Import translations from JSON |
| **Settings** ||||
| Get Settings | GET | /api/settings | Get all settings |
| Update Setting | PUT | /api/settings | Update setting value |
| **Pages** ||||
| List Pages | GET | /api/pages | List all pages |
| Get Page | GET | /api/pages/{slug} | Get page by slug |
| Create Page | POST | /api/pages | Create page |
| Update Page | PUT | /api/pages/{id} | Update page |
| Delete Page | DELETE | /api/pages/{id} | Delete page |
| **Posts** ||||
| List Posts | GET | /api/posts | List posts (with filters) |
| Get Post | GET | /api/posts/{id} | Get post by ID |
| Create Post | POST | /api/posts | Create post |
| Update Post | PUT | /api/posts/{id} | Update post |
| Delete Post | DELETE | /api/posts/{id} | Delete post |
| **Resumes** ||||
| List Resumes | GET | /api/resumes | List resumes (with filters) |
| Get Resume | GET | /api/resumes/{id} | Get resume details |
| Upload Resume | POST | /api/resumes | Upload new resume |
| Update Resume | PUT | /api/resumes/{id} | Update resume |
| Delete Resume | DELETE | /api/resumes/{id} | Delete resume |
| Update Visibility | PUT | /api/resumes/{id}/visibility | Update visibility |
| **Jobs** ||||
| List Jobs | GET | /api/jobs | List jobs (public/filtered) |
| Get Job | GET | /api/jobs/{id} | Get job details |
| Create Job | POST | /api/jobs | Create job (enterprise) |
| Update Job | PUT | /api/jobs/{id} | Update job |
| Delete Job | DELETE | /api/jobs/{id} | Delete job |
| **Applications** ||||
| List Applications | GET | /api/applications | List applications |
| Get Application | GET | /api/applications/{id} | Get application details |
| Create Application | POST | /api/applications | Submit application |
| Update Application | PUT | /api/applications/{id} | Update status |
| **Interviews** ||||
| List Interviews | GET | /api/interviews | List interviews |
| Get Interview | GET | /api/interviews/{id} | Get interview details |
| Create Interview | POST | /api/interviews | Schedule interview |
| Update Interview | PUT | /api/interviews/{id} | Update interview |
| Cancel Interview | PUT | /api/interviews/{id}/cancel | Cancel interview |
| Update Result | PUT | /api/interviews/{id}/result | Update interview result |
| Join Interview | POST | /api/interviews/{id}/join | Join interview room |
| **Seminars** ||||
| List Seminars | GET | /api/seminars | List seminars |
| Get Seminar | GET | /api/seminars/{id} | Get seminar details |
| Create Seminar | POST | /api/seminars | Create seminar |
| Update Seminar | PUT | /api/seminars/{id} | Update seminar |
| Delete Seminar | DELETE | /api/seminars/{id} | Delete seminar |
| Register Seminar | POST | /api/seminars/{id}/register | Register for seminar |
| Unregister Seminar | DELETE | /api/seminars/{id}/register | Cancel registration |
| **Universities** ||||
| List Universities | GET | /api/universities | List universities |
| Get University | GET | /api/universities/{id} | Get university details |
| **Contact** ||||
| Submit Contact Form | POST | /api/contact | Submit contact/consultation form |
| **Admin Statistics** ||||
| Get Stats | GET | /api/admin/stats | Dashboard statistics (admin only) |

---

## 4. Appendix

### 4.1 Data Types Reference

| Type | Description | Validation |
|------|-------------|------------|
| BIGINT UNSIGNED | 64-bit unsigned integer | Auto-increment primary key |
| VARCHAR(n) | Variable string, max n chars | Length validation |
| TEXT | Long text | No length limit |
| ENUM('a','b','c') | Single value from list | Value must be in list |
| JSON | JSON object/array | Valid JSON format |
| DATETIME | Date and time | Valid date format |
| BOOLEAN | True/False | 0 or 1 |
| INT | 32-bit integer | Number validation |
| BIGINT | 64-bit integer | Number validation |

### 4.2 Index Reference

Primary indexes are created automatically for PRIMARY KEY columns.
Additional indexes should be created for:
- Foreign keys (for join performance)
- Columns used in WHERE clauses (for filtering)
- Columns used in ORDER BY (for sorting)
- Unique constraints (for data integrity)

### 4.3 Foreign Key Constraints

All foreign keys use `ON DELETE CASCADE` where appropriate to ensure data integrity when parent records are deleted.

### 4.4 Character Set and Collation

All tables use:
- Character set: `utf8mb4` (supports full Unicode including emoji)
- Collation: `utf8mb4_unicode_ci` (case-insensitive comparison for Unicode)

---

**Document ID:** HRINT-DOC-002  
**Version:** 1.0  
**Created:** 2026-04-21  
**Author:** System Analyst (sa)  

---

---

## 5. PDPA Compliance & Security Controls

### 5.1 Overview

This section outlines the security controls and PDPA (Personal Data Protection Act) compliance measures implemented in the HRINT system. PDPA is particularly relevant for Thailand operations and serves as a best-practice framework for all user data handling.

**Key Principles:**
1. **Lawfulness** - Processing must have legal basis (consent, contract, legitimate interest)
2. **Purpose Limitation** - Data collected only for specified, explicit purposes
3. **Data Minimization** - Only necessary data is collected
4. **Accuracy** - Personal data kept accurate and up-to-date
5. **Storage Limitation** - Data retained only as long as necessary
6. **Integrity & Confidentiality** - Appropriate security measures in place
7. **Accountability** - Data controller responsible for compliance

### 5.2 Sensitive Data Classification

| Data Category | Examples | Risk Level | Protection Required |
|---------------|----------|------------|---------------------|
| **High Sensitivity** | National ID, passport, birth certificate | Critical | Encryption + Masking + Strict Access |
| **Medium Sensitivity** | Phone, email, address, resume content | High | Encryption + Access Control |
| **Low Sensitivity** | Name, education history | Medium | Standard Security |
| **Public** | Company name, job title | Low | Basic Protection |

### 5.3 Technical Security Controls

#### 5.3.1 Data Encryption
```php
// All sensitive personal data MUST be encrypted
- Passwords: bcrypt/Hash (already implemented ✓)
- Database: MySQL TDE or application-level encryption for sensitive fields
- File Storage: Encrypted storage for resumes, uploaded documents
- Network: TLS 1.2+ for all connections (already expected in production)
- API Responses: Sensitive fields masked or excluded from responses
```

#### 5.3.2 Data Masking Rules
```php
// Display rules for sensitive personal data:
National ID (บัตรประจำตัวประชาชน): Show only last 4 digits: ***-****-1234
Phone: Show only last 4 digits: ***-***-1234
Email: Show only first 3 chars + domain: joh***@example.com
Birth Date: Day/month only, year hidden: 01/01/1990 → **/**/****

// API responses MUST respect visibility settings
// Masking is applied based on VIEW CONTEXT, not just role

// VIEW CONTEXTS:
// 1. List View (table rows, cards): ALWAYS masked for ALL viewers
//    - Admin: masked in list view (***@***.com, ***-***-1234)
//    - Enterprise: masked (***@***.com, ***-***-1234)
//    - Student: own data masked in public cards
//
// 2. Detail View (single record, edit form, modal):
//    - Admin: FULL access (unmasked) - for legitimate work purposes
//    - Enterprise: masked (cannot view other users' personal data)
//    - Student: own data unmasked when viewing own profile

// Example API behavior:
// GET /api/admin/users → returns masked data (list view)
// GET /api/admin/users/{id} → returns unmasked data (detail view)
// POST /api/admin/users/{id} → admin action logged, full data accessible
```

#### 5.3.3 Input Validation & Sanitization
```php
// ALL user inputs MUST be validated and sanitized
// Use Laravel Form Request for validation:
- Email: valid email format
- Phone: E.164 format or country-specific validation
- Name: no HTML/script tags, max length 255
- National ID: format validation per country
- Uploaded files: type validation, size limits (≤20MB for resumes)

// Prevent:
- SQL Injection: Use Eloquent ORM or parameterized queries (already default in Laravel ✓)
- XSS: Escape output using {!! !!} vs {{ }} in Blade templates
- CSRF: CSRF tokens for all state-changing requests (already Laravel default ✓)
- File uploads: Validate MIME type, scan with ClamAV or similar antivirus before storage, enforce 20MB limit
```

#### 5.3.4 Rate Limiting
```php
// Implement rate limiting to prevent abuse:
- Authentication endpoints: 5 attempts per minute per IP
- API general: 60 requests per minute per authenticated user; 30 per minute per anonymous user
- File upload: 10 uploads per hour per user
- Danmu messages: 10 per minute per user (already in design ✓)
- Password reset: 3 per hour per email

// Use Laravel's built-in RateLimiter facade
// Log and alert on unusual rate patterns
```

#### 5.3.5 Access Control
```php
// Role-Based Access Control (RBAC):
Admin: Full access to all data (legitimate purpose required)
Student: Own profile, own resume, assigned interviews/seminars
Enterprise: View talent (masked), view own job applications, own interviews

// Data visibility rules by VIEW CONTEXT:
//
// LIST VIEW (table rows, overview cards, search results):
// - ALL roles see MASKED data
// - Admin sees masked in user list (***@***.com, ***-***-1234)
// - Enterprise sees masked in talent pool
//
// DETAIL VIEW (single record, edit modal, profile page):
// - Admin: UNMASKED for legitimate administrative work
// - Enterprise: still masked (cannot view personal data of candidates)
// - Student: own profile unmasked
//
// Personal data includes: email, phone, national_id, birth_date, address

// Additional rules:
- Resume: admin_only / enterprise_visible / public (already in design ✓)
- Talent cards: visibility based on student settings
- Interview records: only creator + participants
- Personal data: masked based on viewer role AND view context (list vs detail)

// PDPA Note: Even admin access is logged. Viewing full personal data
// without legitimate purpose may violate PDPA accountability principle.
```

### 5.4 Audit & Logging

#### 5.4.1 Activity Log
```php
// Log ALL access to personal data:
ActivityLog table fields:
- user_id (who performed action)
- action (view, create, update, delete, export, download)
- target_type (student, resume, interview, etc.)
- target_id (record ID)
- ip_address
- user_agent
- created_at

// Actions that MUST be logged:
- View/export resume
- View student profile details
- Create/edit interview
- Download candidate data
- Access admin panel
- Change user permissions
- Login/logout
```

#### 5.4.2 Data Breach Prevention
```php
// Monitor and prevent data leakage:
1. Export controls: Log all bulk data exports
2. Unusual access patterns: Alert on multiple record views in short time
3. Download limits: Cap downloads per session
4. Session management: Force re-login after inactivity
5. IP-based restrictions: Optional enterprise IP allowlist
```

### 5.5 Data Retention & Deletion

#### 5.5.1 Retention Policy
```php
// Data retention periods:
Active accounts: Retained while account is active
Inactive accounts: No login, no API activity, no profile update for 24 consecutive months
Deleted accounts: 90-day grace period before permanent deletion
Interview records: 2 years (for compliance)
Resume files: Until student deletes or account expires
Consent records: Retained while account exists + 2 years after deletion (for legal defensibility)
Activity logs: 1 year (GDPR/PDPA recommendation)
Email confirmations: 30 days
Backup retention: 30 days after deletion

// Automated cleanup:
- Scheduled job to purge expired tokens daily
- Scheduled job to anonymize inactive accounts after 2 years
- Scheduled job to delete activity logs after 1 year
- Upon account deletion, all resume files are purged from OSS/storage
  Downloaded copies retained by enterprise users are subject to enterprise's own PDPA obligations.
```

#### 5.5.2 Right to Deletion
```php
// Support for data subject requests:
// DELETE /api/users/{id} → anonymize personal data, retain transactional data
// GET /api/users/{id}/export → export all personal data as JSON
// Admin can generate full data export for data subject requests
// User can request account deletion via profile page
// Admin can process deletion requests from data subjects
// Backup retention: 30 days after deletion
```

### 5.6 Consent Management

#### 5.6.1 Required Consents
```php
// Consents required during registration:
1. Privacy Policy acceptance (required)
2. Terms of Service acceptance (required)
3. Personal data processing consent (required for PDPA compliance)
   - "I consent to HorizonHR collecting and processing my personal data for the purposes stated in the Privacy Policy"
4. Marketing communications (optional)
5. Data sharing with partners (optional)

// Consent stored in:
consent_logs table:
- user_id
- consent_type
- consented_at
- ip_address
- user_agent
- withdrawal_at (NULL if still active)

// Withdrawal process:
- User can withdraw consent anytime via settings
- Withdrawal logged, not retroactive
- Service may be limited if required consent withdrawn
```

### 5.7 API Security

#### 5.7.1 Authentication
```php
// JWT tokens (Sanctum) for API authentication:
- Access token expiry: 1 hour
- Refresh token: 7 days
- Token stored in httpOnly cookie (not localStorage)
- SameSite=Strict for CSRF protection

// Social auth:
- OAuth2 with state parameter to prevent CSRF
- PKCE for mobile clients
```

#### 5.7.2 CORS Configuration
```php
// Strict CORS policy:
Allowed origins: only production domain(s)
Allowed methods: GET, POST, PUT, DELETE, OPTIONS
Allowed headers: Content-Type, Authorization
Credentials: true (with strict origin check)
Max age: 86400 (1 day)

// No wildcard (*) in production
```

### 5.8 Frontend Security (Vue 3)

#### 5.8.1 XSS Prevention
```javascript
// Vue 3 auto-escapes by default
// Avoid v-html unless absolutely necessary
// Sanitize any user-generated HTML with DOMPurify

// Content Security Policy headers configured on server
```

#### 5.8.2 Secure Storage
```javascript
// NEVER store sensitive data in localStorage/sessionStorage
// Use httpOnly cookies for auth tokens (handled by Sanctum)
// Sensitive data in memory only, cleared on logout
```

### 5.9 Compliance Checklist

| Item | Status | Notes |
|------|--------|-------|
| Password hashing (bcrypt) | ✅ Done | AuthService.php |
| JWT authentication | ✅ Done | Sanctum middleware |
| Input validation | ⚠️ To Verify | Need to add Form Request validators |
| Data masking | ⚠️ To Implement | Add in API response transformers |
| Audit logging | ⚠️ Partial | ActivityLog table exists, need to wire up |
| Rate limiting | ⚠️ To Implement | Not visible in current code |
| Consent management | ⚠️ To Implement | No consent_logs table yet |
| Data retention policy | ⚠️ To Implement | Need scheduled jobs |
| Right to deletion | ⚠️ To Implement | Need user deletion flow |
| CORS configuration | ⚠️ To Verify | Check config/cors.php |
| File upload security | ⚠️ To Verify | Need malware scanning for uploads |

### 5.10 Security Review Request

> **Action Required:** sa (System Analyst) and u (Developer) to review this PDPA section and verify:
> 1. All data masking rules implemented correctly
> 2. Audit logging is active for all personal data access
> 3. Rate limiting configured appropriately
> 4. Consent management implemented for registration
> 5. Data retention scheduled jobs created

---

**Document ID:** HRINT-DOC-002  
**Section Added:** 5. PDPA Compliance & Security Controls  
**Version:** 1.1  
**Date:** 2026-04-24  
**Status:** Draft - Requires Review  

