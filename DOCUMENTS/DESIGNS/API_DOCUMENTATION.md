# HRINT API Documentation
## HorizonHR International Talent Service

**Document ID:** HRINT-DOC-003  
**Project:** HorizonHR International Talent Service (HRINT)  
**Date:** 2026-04-21  
**Status:** Complete

---

## Table of Contents
1. [Overview](#1-overview)
2. [Authentication](#2-authentication)
3. [API Endpoints](#3-api-endpoints)
4. [Error Codes](#4-error-codes)
5. [Common Patterns](#5-common-patterns)

---

## 1. Overview

### Base URL
```
Production: https://api.horizonhr.com/api
Development: http://localhost:8000/api
```

### Content Type
All requests and responses use `application/json` unless otherwise specified.

### Authentication
Most endpoints require authentication via Bearer token (JWT) in the Authorization header:
```
Authorization: Bearer {token}
```

### Response Format
All responses follow this structure:

**Success Response:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation completed successfully"
}
```

**Error Response:**
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable error message",
    "details": { ... }
  }
}
```

**Paginated Response:**
```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5
  }
}
```

### Rate Limiting
- Default: 60 requests per minute per user
- Auth endpoints: 5 requests per minute per IP
- File uploads: 10 requests per minute per user

---

## 2. Authentication

### 2.1 Register
**Register a new user (student or enterprise)**

```
POST /api/auth/register
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "SecureP@ss123",
  "password_confirmation": "SecureP@ss123",
  "role": "student",
  "name": "Somchai Smith",
  "nationality": "Thailand",
  "phone": "+66 81 234 5678"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| email | string | Yes | Valid email address |
| password | string | Yes | Min 8 chars, mixed case, number |
| password_confirmation | string | Yes | Must match password |
| role | string | Yes | "student" or "enterprise" |
| name | string | Yes | Full name |
| nationality | string | No | For students |
| phone | string | No | Contact number |
| company_name | string | Conditional | Required if role="enterprise" |
| industry | string | No | For enterprises |

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "role": "student",
      "status": "pending",
      "email_verified": false
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  },
  "message": "Registration successful. Please check your email to verify your account."
}
```

**Error Response (422):**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email has already been taken."]
    }
  }
}
```

---

### 2.2 Login
**Authenticate user and receive access token**

```
POST /api/auth/login
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "SecureP@ss123"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| email | string | Yes | Registered email |
| password | string | Yes | Account password |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "role": "student",
      "name": "Somchai Smith",
      "status": "active",
      "email_verified": true,
      "prefer_lang": "en"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  },
  "message": "Login successful."
}
```

**Error Response (401):**
```json
{
  "success": false,
  "error": {
    "code": "INVALID_CREDENTIALS",
    "message": "Invalid email or password."
  }
}
```

---

### 2.3 Logout
**Invalidate current session/token**

```
POST /api/auth/logout
```

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Logged out successfully."
}
```

---

### 2.4 Forgot Password
**Send password reset email**

```
POST /api/auth/forgot-password
```

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Password reset instructions have been sent to your email."
}
```

**Note:** Always returns success to prevent email enumeration attacks.

---

### 2.5 Reset Password
**Reset password using token**

```
POST /api/auth/reset-password
```

**Request Body:**
```json
{
  "token": "abc123def456...",
  "email": "user@example.com",
  "password": "NewSecureP@ss123",
  "password_confirmation": "NewSecureP@ss123"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| token | string | Yes | Token from reset email |
| email | string | Yes | Associated email |
| password | string | Yes | New password |
| password_confirmation | string | Yes | Must match password |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com"
    }
  },
  "message": "Password has been reset successfully."
}
```

**Error Response (422):**
```json
{
  "success": false,
  "error": {
    "code": "INVALID_TOKEN",
    "message": "This password reset token is invalid or has expired."
  }
}
```

---

### 2.6 Social Authentication
**Supported Providers:** `google`, `facebook`, `linkedin`, `wechat`

#### 2.6.1 Initiate Social Login
**Redirect user to social provider for authentication**

```
GET /api/auth/social/{provider}
```

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| provider | string | Yes | One of: google, facebook, linkedin, wechat |

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| role | string | No | "student" or "enterprise" (defaults to "student") |
| redirect_uri | string | No | URL to redirect after successful auth (relative to callback) |

**Success Response (302):**
Redirects to provider's OAuth authorization page.

**Error Response (400):**
```json
{
  "success": false,
  "error": {
    "code": "INVALID_PROVIDER",
    "message": "Unsupported social authentication provider."
  }
}
```

---

#### 2.6.2 Social Login Callback
**Handle callback from social provider**

```
GET /api/auth/social/{provider}/callback
```

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| provider | string | Yes | One of: google, facebook, linkedin, wechat |

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| code | string | Yes | Authorization code from provider |
| state | string | Yes | State token for CSRF protection |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "role": "student",
      "name": "Somchai Smith",
      "status": "active",
      "email_verified": true,
      "prefer_lang": "en"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "is_new_user": false
  },
  "message": "Social login successful."
}
```

**For new users (201):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 15,
      "email": "user@example.com",
      "role": "student",
      "name": "Somchai Smith",
      "status": "pending",
      "email_verified": true,
      "prefer_lang": "en"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "is_new_user": true
  },
  "message": "Account created successfully via social login."
}
```

**Error Responses:**
```json
{
  "success": false,
  "error": {
    "code": "AUTHENTICATION_FAILED",
    "message": "Failed to authenticate with the social provider."
  }
}
```

**WeChat-specific note:** WeChat may require additional configuration for China-based deployments (ICP备案, referrer whitelist).

---

#### 2.6.3 Register via Social (Enterprise)
**Register new enterprise account via social login**

```
POST /api/auth/social/{provider}/register
```

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| provider | string | Yes | One of: google, facebook, linkedin, wechat |

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "role": "enterprise",
  "company_name": "Example Company Ltd.",
  "industry": "Technology",
  "contact_name": "John Doe",
  "contact_phone": "+86 27 1234 5678"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| role | string | Yes | Must be "enterprise" |
| company_name | string | Yes | Legal company name |
| industry | string | No | Company industry |
| contact_name | string | No | Contact person name |
| contact_phone | string | No | Contact phone number |

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 20,
      "email": "user@example.com",
      "role": "enterprise",
      "status": "pending",
      "email_verified": true
    },
    "enterprise": {
      "id": 5,
      "company_name": "Example Company Ltd.",
      "verified": false
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  },
  "message": "Enterprise account created. Your account requires verification before full access."
}
```

**Note:** Enterprise accounts created via social login require additional verification by admin before gaining full access to job posting and talent viewing features.

---

### 2.7 Confirm Email
**Confirm user's email address after registration**

```
POST /api/auth/confirm-email
```

**Request Body:**
```json
{
  "token": "abc123def456...",
  "email": "user@example.com"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| token | string | Yes | Token from confirmation email |
| email | string | Yes | Associated email address |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "email_verified": true,
      "status": "active"
    }
  },
  "message": "Email confirmed successfully. Your account is now active."
}
```

**Note (Enterprise):** Enterprise accounts remain in `pending` status after email confirmation. Admin must separately activate the enterprise account via `PUT /api/users/{id}/activate-enterprise`.

**Error Response (422):**
```json
{
  "success": false,
  "error": {
    "code": "INVALID_TOKEN",
    "message": "This email confirmation token is invalid or has expired."
  }
}
```

---

## 3. API Endpoints

### 3.1 Users

#### GET /api/users
**List all users (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 20 | Items per page (max 100) |
| role | string | null | Filter by role: admin, student, enterprise |
| status | string | null | Filter by status: pending, active, suspended |
| search | string | null | Search by name or email |
| sort_by | string | created_at | Sort field |
| sort_order | string | desc | Sort order: asc, desc |

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "email": "student@example.com",
      "role": "student",
      "status": "active",
      "email_verified": true,
      "prefer_lang": "en",
      "student": {
        "id": 1,
        "name": "Somchai Smith",
        "nationality": "Thailand"
      },
      "created_at": "2026-04-01T10:00:00Z"
    },
    {
      "id": 2,
      "email": "enterprise@example.com",
      "role": "enterprise",
      "status": "pending",
      "email_verified": false,
      "prefer_lang": "zh_cn",
      "enterprise": {
        "id": 1,
        "company_name": "Alibaba Group"
      },
      "created_at": "2026-04-05T14:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "total_pages": 8
  }
}
```

---

#### POST /api/users
**Create a new user (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "email": "newuser@example.com",
  "password": "SecureP@ss123",
  "role": "student",
  "name": "New User",
  "nationality": "Vietnam",
  "phone": "+84 123 456 789",
  "prefer_lang": "en"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "email": "newuser@example.com",
    "role": "student",
    "status": "active",
    "email_verified": false,
    "prefer_lang": "en",
    "created_at": "2026-04-21T10:00:00Z"
  },
  "message": "User created successfully."
}
```

---

#### GET /api/users/{id}
**Get user details**

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "student@example.com",
    "role": "student",
    "status": "active",
    "email_verified": true,
    "prefer_lang": "en",
    "last_login_at": "2026-04-21T09:00:00Z",
    "created_at": "2026-04-01T10:00:00Z",
    "student": {
      "id": 1,
      "name": "Somchai Smith",
      "nationality": "Thailand",
      "phone": "+66 81 234 5678",
      "avatar": "/uploads/avatars/photo.jpg",
      "birth_date": "1999-05-15",
      "gender": "male",
      "address": "Bangkok, Thailand",
      "verified": true
    }
  }
}
```

---

#### PUT /api/users/{id}
**Update user**

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "Somchai Updated",
  "phone": "+66 81 987 6543",
  "prefer_lang": "th"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "student@example.com",
    "name": "Somchai Updated",
    "prefer_lang": "th",
    "updated_at": "2026-04-21T10:30:00Z"
  },
  "message": "User updated successfully."
}
```

---

#### PUT /api/users/{id}/activate-enterprise
**Activate enterprise account (Admin only)**

Enterprise users must be activated by an admin before they can access enterprise features (post jobs, view talent cards, etc.). This requirement is stated in the requirements: "Enterprise User need to contact Office to activate account".

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "enterprise_status": "enterprise_verified"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| enterprise_status | string | Yes | Must be "enterprise_verified" to activate enterprise status |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "email": "enterprise@example.com",
    "role": "enterprise",
    "status": "active",
    "enterprise_status": "enterprise_verified",
    "updated_at": "2026-04-21T10:30:00Z"
  },
  "message": "Enterprise account has been activated successfully."
}
```

**Error Response (403):**
```json
{
  "success": false,
  "error": {
    "code": "INSUFFICIENT_PERMISSIONS",
    "message": "Only admin users can activate enterprise accounts."
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid enterprise status value."
  }
}
```

---


#### DELETE /api/users/{id}
**Delete user (Admin only, soft delete)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "User deleted successfully."
}
```

---

### 3.2 Language Settings

Manages the list of active site languages (`language_settings` table). Frontend reads this first to determine which language fields to render.

#### GET /api/languages
**Get all active language settings**

**Headers:**
```
Authorization: Bearer {token} (optional)
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    { "code": "en", "name": "English", "native_name": "English", "flag": "🇬🇧", "is_active": true, "position": 1 },
    { "code": "zh_cn", "name": "中文简体", "native_name": "简体中文", "flag": "🇨🇳", "is_active": true, "position": 2 },
    { "code": "th", "name": "ภาษาไทย", "native_name": "ภาษาไทย", "flag": "🇹🇭", "is_active": true, "position": 3 }
  ]
}
```

---

#### POST /api/languages
**Add new language (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "code": "ja",
  "name": "Japanese",
  "native_name": "日本語",
  "flag": "🇯🇵",
  "is_active": true,
  "position": 4
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| code | string | Yes | ISO language code (e.g., `ja`, `ko`, `vi`) |
| name | string | Yes | English name of the language |
| native_name | string | Yes | Language name in its own script |
| flag | string | No | Emoji flag character |
| is_active | boolean | No | Default true |
| position | int | No | Display order |

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "code": "ja",
    "name": "Japanese",
    "native_name": "日本語",
    "flag": "🇯🇵",
    "is_active": true,
    "position": 4
  },
  "message": "Language added successfully."
}
```

---

#### PUT /api/languages/{code}
**Update language settings (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "name": "English (US)",
  "is_active": true,
  "position": 1
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "code": "en",
    "name": "English (US)",
    "is_active": true,
    "position": 1
  },
  "message": "Language updated successfully."
}
```

---

#### DELETE /api/languages/{code}
**Delete a language (Admin only)**

**Note:** The default language (`en`) cannot be deleted. Deleting a language also removes associated translation entries.

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Language deleted successfully."
}
```

**Error Response (422):**
```json
{
  "success": false,
  "error": {
    "code": "CANNOT_DELETE_DEFAULT",
    "message": "Cannot delete the default language."
  }
}
```

---

### 3.3 Translations

Manages translation key-value pairs (`languages` table). Each key maps to translated text in each active language.

#### GET /api/translations
**Get all translation keys**

**Headers:**
```
Authorization: Bearer {token} (optional for public keys)
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| group | string | null | Filter by group |
| search | string | null | Search by key |

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "key": "home_banner_title",
      "zh_cn": "连接东南亚青年与中国高校",
      "en": "Connect Southeast Asian Youth with Chinese Universities",
      "th": "เชื่อมต่อเยาวชนเอเชียตะวันออกเฉียงใต้กับมหาวิทยาลัยจีน",
      "created_at": "2026-04-01T00:00:00Z"
    },
    {
      "id": 2,
      "key": "talent_pool",
      "zh_cn": "人才库",
      "en": "Talent Pool",
      "th": "กลุ่มบุคลากร",
      "created_at": "2026-04-01T00:00:00Z"
    }
  ]
}
```

---

#### GET /api/translations/{key}
**Get specific translation by key**

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "key": "about_us",
    "zh_cn": "关于我们",
    "en": "About Us",
    "th": "เกี่ยวกับเรา"
  }
}
```

---

#### POST /api/translations
**Create new translation key (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "key": "new_feature_title",
  "zh_cn": "新功能标题",
  "en": "New Feature Title",
  "th": "ชื่อคุณลักษณะใหม่"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 50,
    "key": "new_feature_title",
    "zh_cn": "新功能标题",
    "en": "New Feature Title",
    "th": "ชื่อคุณลักษณะใหม่"
  },
  "message": "Translation key created successfully."
}
```

---

#### PUT /api/translations/{key}
**Update translation values (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "zh_cn": "更新后的中文",
  "en": "Updated English",
  "th": "อัปเดตภาษาไทย"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "key": "home_banner_title",
    "zh_cn": "更新后的中文",
    "en": "Updated English",
    "th": "อัปเดตภาษาไทย"
  },
  "message": "Translation updated successfully."
}
```

---

#### DELETE /api/translations/{key}
**Delete translation key (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Translation key deleted successfully."
}
```

---

#### GET /api/translations/export
**Export all translations as JSON (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "exported_at": "2026-04-21T10:00:00Z",
    "total_keys": 150,
    "languages": {
      "zh_cn": { ... },
      "en": { ... },
      "th": { ... }
    }
  }
}
```

File download with `Content-Disposition: attachment; filename="translations-export.json"`.

---

#### POST /api/translations/import
**Import translations from JSON (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
Content-Type: multipart/form-data
```

**Form Data:**
| Field | Type | Description |
|-------|------|-------------|
| file | file | JSON file to import |
| mode | string | "merge" or "replace" |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "imported": 50,
    "updated": 100,
    "total": 150
  },
  "message": "Translations imported successfully."
}
```

---

### 3.4 Settings

#### GET /api/settings
**Get all settings**

**Headers:**
```
Authorization: Bearer {token} (optional for public settings)
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| group | string | Filter by group: website, seo, social, smtp, system |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "website": {
      "site_name": "HorizonHR",
      "site_name_zh_cn": "湖北豪睿国际人才服务有限公司",
      "site_name_en": "HorizonHR International Talent Service",
      "site_name_th": "บริการบุคลากรระหว่างประเทศ HorizonHR",
      "logo": "/assets/images/logo.png",
      "logo_secondary": "/assets/images/logo-white.png",
      "favicon": "/assets/images/favicon.ico",
      "default_language": "en",
      "contact_email": "contact@horizonhr.com",
      "contact_phone": "+86 27-8888-8888",
      "copyright": "© 2026 Hubei Horizon International. All Rights Reserved."
    },
    "seo": {
      "seo_title": "HorizonHR International Talent Service",
      "seo_title_zh_cn": "湖北豪睿国际人才服务有限公司",
      "seo_description": "",
      "og_image": "/assets/images/og-image.jpg"
    },
    "social": {
      "social_wechat": "horizonhr",
      "social_whatsapp": "+8613800138000",
      "social_line": "@horizonhr"
    }
  }
}
```

---

#### PUT /api/settings
**Update settings (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "site_name": "HorizonHR International",
  "contact_email": "newemail@horizonhr.com",
  "smtp_host": "smtp.mailgun.org",
  "smtp_port": 587,
  "smtp_username": "postmaster@horizonhr.com",
  "smtp_password": "secretpassword"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "site_name": "HorizonHR International",
    "contact_email": "newemail@horizonhr.com",
    "smtp_host": "smtp.mailgun.org"
  },
  "message": "Settings updated successfully."
}
```

---

#### POST /api/settings/test-smtp
**Test SMTP email connection (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "recipient_email": "test@example.com"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "status": "success",
    "message": "Test email sent successfully to test@example.com"
  },
  "message": "SMTP connection test passed."
}
```

**Error Response (500):**
```json
{
  "success": false,
  "error": {
    "code": "SMTP_CONNECTION_FAILED",
    "message": "Could not connect to SMTP server. Check host and credentials."
  }
}
```

---

#### POST /api/settings/upload-logo
**Upload site logo (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
Content-Type: multipart/form-data
```

**Form Data:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| logo | file | Yes | PNG/JPG/SVG, max 2MB, recommended 200×60px |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "logo": "/uploads/settings/logo_abc123.png"
  },
  "message": "Logo uploaded successfully."
}
```

---

#### POST /api/settings/upload-favicon
**Upload site favicon (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
Content-Type: multipart/form-data
```

**Form Data:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| favicon | file | Yes | ICO/PNG, max 512KB, recommended 32×32px |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "favicon": "/uploads/settings/favicon_def456.ico"
  },
  "message": "Favicon uploaded successfully."
}
```

---

### 3.5 Pages

#### GET /api/pages
**List all pages**

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "slug": "about-us",
      "title_zh_cn": "关于我们",
      "title_en": "About Us",
      "title_th": "เกี่ยวกับเรา",
      "status": "published",
      "type": "page",
      "order_num": 1
    },
    {
      "id": 2,
      "slug": "contact-us",
      "title_zh_cn": "联系我们",
      "title_en": "Contact Us",
      "title_th": "ติดต่อเรา",
      "status": "published",
      "type": "page",
      "order_num": 2
    }
  ]
}
```

---

#### GET /api/pages/{slug}
**Get page by slug**

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "slug": "about-us",
    "title_zh_cn": "关于我们",
    "title_en": "About Us",
    "title_th": "เกี่ยวกับเรา",
    "content_zh_cn": "<p>Company content...</p>",
    "content_en": "<p>Company content...</p>",
    "content_th": "<p>Company content...</p>",
    "meta_title_zh_cn": "关于我们 - HorizonHR",
    "meta_title_en": "About Us - HorizonHR",
    "status": "published",
    "type": "page",
    "created_at": "2026-04-01T00:00:00Z"
  }
}
```

---

#### POST /api/pages
**Create new page (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "slug": "new-page",
  "title_zh_cn": "新页面",
  "title_en": "New Page",
  "title_th": "หน้าใหม่",
  "content_zh_cn": "<p>Content here</p>",
  "content_en": "<p>Content here</p>",
  "content_th": "<p>Content here</p>",
  "status": "draft",
  "type": "page",
  "order_num": 10
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "slug": "new-page",
    "status": "draft"
  },
  "message": "Page created successfully."
}
```

---

#### PUT /api/pages/{id}
**Update page (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "title_en": "Updated Title",
  "content_en": "<p>Updated content</p>",
  "status": "published"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "slug": "new-page",
    "title_en": "Updated Title",
    "status": "published",
    "updated_at": "2026-04-21T10:30:00Z"
  },
  "message": "Page updated successfully."
}
```

---

#### DELETE /api/pages/{id}
**Delete page (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Page deleted successfully."
}
```

---

### 3.6 Posts

#### GET /api/posts
**List all posts**

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 20 | Items per page |
| category | string | null | Filter: company_news, industry_news, study_abroad, recruitment |
| status | string | published | Filter by status |
| search | string | null | Search in title |
| language | string | en | Language for content |

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "New Partnership Announcement",
      "category": "company_news",
      "thumbnail": "/uploads/posts/thumb1.jpg",
      "status": "published",
      "view_count": 1250,
      "published_at": "2026-04-15T08:00:00Z",
      "language": "en"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 50,
    "total_pages": 3
  }
}
```

---

#### GET /api/posts/{id}
**Get post by ID**

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title_zh_cn": "新合作公告",
    "title_en": "New Partnership Announcement",
    "title_th": "ประกาศความร่วมมือใหม่",
    "content_zh_cn": "<p>Full article content...</p>",
    "content_en": "<p>Full article content...</p>",
    "content_th": "<p>Full article content...</p>",
    "category": "company_news",
    "thumbnail": "/uploads/posts/thumb1.jpg",
    "status": "published",
    "view_count": 1251,
    "published_at": "2026-04-15T08:00:00Z",
    "created_at": "2026-04-15T07:00:00Z"
  }
}
```

---

#### POST /api/posts
**Create new post (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "title_zh_cn": "文章标题",
  "title_en": "Article Title",
  "title_th": "ชื่อบทความ",
  "content_zh_cn": "<p>Content</p>",
  "content_en": "<p>Content</p>",
  "content_th": "<p>Content</p>",
  "category": "company_news",
  "thumbnail": "base64_encoded_image_or_url",
  "status": "draft",
  "published_at": "2026-04-22T00:00:00Z"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "title_en": "Article Title",
    "status": "draft"
  },
  "message": "Post created successfully."
}
```

---

#### PUT /api/posts/{id}
**Update post (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "title_en": "Updated Title",
  "content_en": "<p>Updated content</p>",
  "status": "published",
  "published_at": "2026-04-21T12:00:00Z"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "status": "published",
    "updated_at": "2026-04-21T10:30:00Z"
  },
  "message": "Post updated successfully."
}
```

---

#### DELETE /api/posts/{id}
**Delete post (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Post deleted successfully."
}
```

---

### 3.7 Resumes

#### GET /api/resumes
**List resumes**

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 20 | Items per page |
| student_id | int | null | Filter by student |
| visibility | string | null | Filter: admin_only, enterprise_visible, public |
| status | string | null | Filter: pending, approved, rejected |
| nationality | string | null | Filter by nationality |
| major | string | null | Filter by major |
| language | string | null | Filter by language skill |
| education | string | null | Filter by education level |
| search | string | null | Search student name |
| sort_by | string | created_at | Sort field |
| sort_order | string | desc | Sort order |

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "student_id": 1,
      "student": {
        "id": 1,
        "name": "Somchai Smith",
        "nationality": "Thailand",
        "avatar": "/uploads/avatars/photo.jpg"
      },
      "file_name": "Somchai_Smith_Resume.pdf",
      "file_type": "pdf",
      "file_size": 1024000,
      "visibility": "enterprise_visible",
      "status": "approved",
      "created_at": "2026-04-10T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 45,
    "total_pages": 3
  }
}
```

---

#### GET /api/resumes/{id}
**Get resume details**

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "student_id": 1,
    "student": {
      "id": 1,
      "name": "Somchai Smith",
      "nationality": "Thailand",
      "phone": "+66 81 234 5678",
      "avatar": "/uploads/avatars/photo.jpg",
      "birth_date": "1999-05-15",
      "gender": "male",
      "verified": true
    },
    "file_path": "/uploads/resumes/file_abc123.pdf",
    "file_name": "Somchai_Smith_Resume.pdf",
    "file_type": "pdf",
    "file_size": 1024000,
    "visibility": "enterprise_visible",
    "status": "approved",
    "created_at": "2026-04-10T10:00:00Z"
  }
}
```

---

#### POST /api/resumes
**Upload new resume**

**Headers:**
```
Authorization: Bearer {token}
Role: student, admin
Content-Type: multipart/form-data
```

**Form Data:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| file | file | Yes | PDF, DOC, DOCX, JPG, PNG (max 20MB) |
| visibility | string | No | admin_only, enterprise_visible, public (default: enterprise_visible) |

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "file_name": "resume.pdf",
    "file_type": "pdf",
    "file_size": 1048576,
    "visibility": "enterprise_visible",
    "status": "pending",
    "created_at": "2026-04-21T10:30:00Z"
  },
  "message": "Resume uploaded successfully."
}
```

**Error Response (422):**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid file type or size exceeds 20MB.",
    "details": {
      "file": ["The file must be a file of type: pdf, doc, docx, jpg, png."]
    }
  }
}
```

---

#### PUT /api/resumes/{id}
**Update resume metadata**

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "visibility": "public"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "visibility": "public",
    "updated_at": "2026-04-21T11:00:00Z"
  },
  "message": "Resume updated successfully."
}
```

---

#### PUT /api/resumes/{id}/visibility
**Update resume visibility**

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "visibility": "public"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "visibility": "public"
  },
  "message": "Visibility updated successfully."
}
```

---

#### DELETE /api/resumes/{id}
**Delete resume**

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Resume deleted successfully."
}
```

---

### 3.8 Jobs

#### GET /api/jobs
**List jobs**

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 20 | Items per page |
| enterprise_id | int | null | Filter by enterprise |
| location | string | null | Filter by location |
| job_type | string | null | Filter: full_time, part_time, contract, internship |
| status | string | published | Filter by status |
| salary_min | int | null | Minimum salary |
| salary_max | int | null | Maximum salary |
| search | string | null | Search in title/description |
| sort_by | string | published_at | Sort field |
| sort_order | string | desc | Sort order |

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "enterprise_id": 1,
      "enterprise": {
        "id": 1,
        "company_name": "Alibaba Group",
        "logo": "/uploads/logos/alibaba.png"
      },
      "title": "Marketing Manager",
      "location": "Shanghai, China",
      "salary_min": 20000,
      "salary_max": 40000,
      "salary_currency": "CNY",
      "job_type": "full_time",
      "status": "published",
      "published_at": "2026-04-15T00:00:00Z",
      "expires_at": "2026-05-15T00:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 35,
    "total_pages": 2
  }
}
```

---

#### GET /api/jobs/{id}
**Get job details**

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "enterprise_id": 1,
    "enterprise": {
      "id": 1,
      "company_name": "Alibaba Group",
      "logo": "/uploads/logos/alibaba.png",
      "industry": "E-commerce",
      "website": "https://www.alibaba.com"
    },
    "title": "Marketing Manager",
    "description": "<p>Full job description...</p>",
    "requirements": "<ul><li>5+ years experience</li></ul>",
    "location": "Shanghai, China",
    "salary_min": 20000,
    "salary_max": 40000,
    "salary_currency": "CNY",
    "job_type": "full_time",
    "status": "published",
    "view_count": 350,
    "published_at": "2026-04-15T00:00:00Z",
    "expires_at": "2026-05-15T00:00:00Z",
    "created_at": "2026-04-10T10:00:00Z"
  }
}
```

---

#### POST /api/jobs
**Create new job (Enterprise only)**

**Headers:**
```
Authorization: Bearer {token}
Role: enterprise
```

**Request Body:**
```json
{
  "title": "Marketing Manager",
  "description": "<p>We are looking for...</p>",
  "requirements": "<ul><li>5+ years marketing experience</li></ul>",
  "location": "Shanghai, China",
  "salary_min": 20000,
  "salary_max": 40000,
  "salary_currency": "CNY",
  "job_type": "full_time",
  "status": "draft",
  "expires_at": "2026-05-15T00:00:00Z"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 20,
    "title": "Marketing Manager",
    "status": "draft"
  },
  "message": "Job created successfully."
}
```

---

#### PUT /api/jobs/{id}
**Update job (Enterprise only)**

**Headers:**
```
Authorization: Bearer {token}
Role: enterprise
```

**Request Body:**
```json
{
  "title": "Senior Marketing Manager",
  "status": "published",
  "salary_max": 50000
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 20,
    "title": "Senior Marketing Manager",
    "status": "published",
    "updated_at": "2026-04-21T10:30:00Z"
  },
  "message": "Job updated successfully."
}
```

---

#### DELETE /api/jobs/{id}
**Delete job (Enterprise only)**

**Headers:**
```
Authorization: Bearer {token}
Role: enterprise
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Job deleted successfully."
}
```

---

### 3.9 Applications

#### GET /api/applications
**List applications**

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 20 | Items per page |
| job_id | int | null | Filter by job |
| student_id | int | null | Filter by student |
| status | string | null | Filter: pending, reviewed, interviewed, accepted, rejected, withdrawn |
| enterprise_id | int | null | Filter by enterprise (for enterprise users) |

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "job_id": 1,
      "job": {
        "id": 1,
        "title": "Marketing Manager"
      },
      "student_id": 1,
      "student": {
        "id": 1,
        "name": "Somchai Smith",
        "nationality": "Thailand",
        "avatar": "/uploads/avatars/photo.jpg"
      },
      "resume_id": 1,
      "cover_letter": "<p>I am interested in this position...</p>",
      "status": "pending",
      "applied_at": "2026-04-18T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 15,
    "total_pages": 1
  }
}
```

---

#### GET /api/applications/{id}
**Get application details**

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "job_id": 1,
    "job": {
      "id": 1,
      "title": "Marketing Manager",
      "enterprise": {
        "company_name": "Alibaba Group"
      }
    },
    "student_id": 1,
    "student": {
      "id": 1,
      "name": "Somchai Smith",
      "nationality": "Thailand",
      "phone": "+66 81 234 5678",
      "email": "somchai@example.com"
    },
    "resume_id": 1,
    "cover_letter": "<p>I am interested in this position...</p>",
    "status": "pending",
    "notes": null,
    "applied_at": "2026-04-18T10:00:00Z",
    "updated_at": "2026-04-18T10:00:00Z"
  }
}
```

---

#### POST /api/applications
**Submit application (Student only)**

**Headers:**
```
Authorization: Bearer {token}
Role: student
```

**Request Body:**
```json
{
  "job_id": 1,
  "resume_id": 1,
  "cover_letter": "<p>I am excited to apply for this position...</p>"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 25,
    "job_id": 1,
    "status": "pending",
    "applied_at": "2026-04-21T10:30:00Z"
  },
  "message": "Application submitted successfully."
}
```

**Error Response (409):**
```json
{
  "success": false,
  "error": {
    "code": "DUPLICATE_APPLICATION",
    "message": "You have already applied to this job."
  }
}
```

---

#### PUT /api/applications/{id}
**Update application status**

**Headers:**
```
Authorization: Bearer {token}
Role: admin, enterprise
```

**Request Body:**
```json
{
  "status": "interviewed",
  "notes": "Strong candidate, scheduled for interview."
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 25,
    "status": "interviewed",
    "updated_at": "2026-04-21T11:00:00Z"
  },
  "message": "Application updated successfully."
}
```

---

### 3.10 Interviews

#### GET /api/interviews
**List interviews**

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 20 | Items per page |
| type | string | all | Filter: all, scheduled, completed, cancelled |
| role | string | all | Filter by role: admin, enterprise, student |

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "creator_id": 2,
      "enterprise_id": 1,
      "enterprise": {
        "id": 1,
        "company_name": "Alibaba Group"
      },
      "student_id": 1,
      "student": {
        "id": 1,
        "name": "Somchai Smith"
      },
      "job_id": 1,
      "job": {
        "id": 1,
        "title": "Marketing Manager"
      },
      "title": "Marketing Manager Interview",
      "scheduled_at": "2026-04-25T14:00:00Z",
      "duration": 30,
      "room_id": "room_abc123",
      "status": "scheduled",
      "created_at": "2026-04-21T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 8,
    "total_pages": 1
  }
}
```

---

#### GET /api/interviews/{id}
**Get interview details**

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "creator_id": 2,
    "enterprise_id": 1,
    "enterprise": {
      "id": 1,
      "company_name": "Alibaba Group"
    },
    "student_id": 1,
    "student": {
      "id": 1,
      "name": "Somchai Smith",
      "email": "somchai@example.com"
    },
    "job_id": 1,
    "job": {
      "id": 1,
      "title": "Marketing Manager"
    },
    "title": "Marketing Manager Interview",
    "scheduled_at": "2026-04-25T14:00:00Z",
    "duration": 30,
    "room_id": "room_abc123",
    "room_token": "temp_token_xyz",
    "status": "scheduled",
    "reminder_sent": true,
    "created_at": "2026-04-21T10:00:00Z"
  }
}
```

---

#### POST /api/interviews
**Create interview (Admin or Enterprise)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin, enterprise
```

**Request Body:**
```json
{
  "student_id": 1,
  "job_id": 1,
  "title": "Marketing Manager Interview",
  "scheduled_at": "2026-04-25T14:00:00Z",
  "duration": 30
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "title": "Marketing Manager Interview",
    "scheduled_at": "2026-04-25T14:00:00Z",
    "duration": 30,
    "room_id": "room_new456",
    "status": "scheduled"
  },
  "message": "Interview scheduled successfully. Invitation will be sent to the student."
}
```

---

#### PUT /api/interviews/{id}
**Update interview**

**Headers:**
```
Authorization: Bearer {token}
Role: admin, enterprise
```

**Request Body:**
```json
{
  "scheduled_at": "2026-04-26T15:00:00Z",
  "duration": 45
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "scheduled_at": "2026-04-26T15:00:00Z",
    "duration": 45,
    "updated_at": "2026-04-21T11:00:00Z"
  },
  "message": "Interview updated successfully."
}
```

---

#### PUT /api/interviews/{id}/result
**Update interview result (Admin or Enterprise)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin, enterprise
```

**Request Body:**
```json
{
  "result": "pass",
  "notes": "Excellent communication skills. Recommended for next round."
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "status": "completed",
    "result": "pass"
  },
  "message": "Interview result recorded."
}
```

---

#### PUT /api/interviews/{id}/cancel
**Cancel an interview (Admin or Enterprise)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin, enterprise
```

**Request Body:**
```json
{
  "reason": "Candidate withdrew application."
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| reason | string | No | Cancellation reason (sent to student via email) |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "status": "cancelled"
  },
  "message": "Interview cancelled successfully."
}
```

---

#### POST /api/interviews/{id}/join
**Join interview room (any authenticated participant or student via link)**

A student may join without login using a one-time room token embedded in the invitation link.

**Headers:**
```
Authorization: Bearer {token}   (authenticated users)
```
or
```
X-Room-Token: {room_token}      (unauthenticated student via invitation link)
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "room_id": "room_abc123",
    "room_token": "temp_token_xyz",
    "webrtc_config": {
      "ice_servers": [
        { "urls": "stun:stun.horizonhr.com:3478" },
        { "urls": "turn:turn.horizonhr.com:3478", "username": "user", "credential": "pass" }
      ]
    },
    "websocket_url": "wss://api.horizonhr.com/ws/interview/room_abc123"
  },
  "message": "Joined interview room successfully."
}
```

**Error Response (403):**
```json
{
  "success": false,
  "error": {
    "code": "INTERVIEW_NOT_JOINABLE",
    "message": "This interview is not currently open for joining."
  }
}
```

---

### 3.11 Seminars

#### GET /api/seminars
**List seminars**

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 20 | Items per page |
| type | string | upcoming | Filter: upcoming, live, past, all |
| target | string | null | Filter: students, enterprises, both |
| status | string | null | Filter: scheduled, live, ended, cancelled |
| search | string | null | Search in title |

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title_en": "How to Succeed in Chinese Universities",
      "speaker_name": "Dr. Zhang Wei",
      "speaker_title": "Professor, Peking University",
      "thumbnail": "/uploads/seminars/thumb1.jpg",
      "target_audience": "students",
      "status": "scheduled",
      "permission": "registered",
      "starts_at": "2026-04-25T10:00:00Z",
      "duration_min": 90,
      "current_viewers": 0,
      "is_registered": false
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 12,
    "total_pages": 1
  }
}
```

---

#### GET /api/seminars/{id}
**Get seminar details**

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title_zh_cn": "如何在中国大学取得成功",
    "title_en": "How to Succeed in Chinese Universities",
    "title_th": "วิธีประสบความสำเร็จในมหาวิทยาลัยจีน",
    "desc_zh_cn": "<p>研讨会描述...</p>",
    "desc_en": "<p>Seminar description...</p>",
    "desc_th": "<p>คำอธิบายสัมมนา...</p>",
    "speaker_name": "Dr. Zhang Wei",
    "speaker_title": "Professor, Peking University",
    "speaker_bio": "Biography of the speaker...",
    "speaker_avatar": "/uploads/speakers/avatar1.jpg",
    "thumbnail": "/uploads/seminars/thumb1.jpg",
    "stream_url": "https://live.horizonhr.com/stream1",
    "target_audience": "students",
    "status": "scheduled",
    "permission": "registered",
    "max_viewers": 10000,
    "current_viewers": 0,
    "starts_at": "2026-04-25T10:00:00Z",
    "duration_min": 90,
    "is_registered": false,
    "recording": null,
    "created_at": "2026-04-01T00:00:00Z"
  }
}
```

---

#### POST /api/seminars
**Create seminar (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "title_en": "How to Succeed in Chinese Universities",
  "title_zh_cn": "如何在中国大学取得成功",
  "title_th": "วิธีประสบความสำเร็จในมหาวิทยาลัยจีน",
  "desc_en": "<p>Learn how to...</p>",
  "desc_zh_cn": "<p>学习如何...</p>",
  "desc_th": "<p>เรียนรู้วิธี...</p>",
  "speaker_name": "Dr. Zhang Wei",
  "speaker_title": "Professor",
  "speaker_bio": "Biography...",
  "thumbnail": "url_or_base64",
  "target_audience": "students",
  "permission": "registered",
  "max_viewers": 10000,
  "starts_at": "2026-04-25T10:00:00Z",
  "duration_min": 90
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "title_en": "How to Succeed in Chinese Universities",
    "status": "scheduled"
  },
  "message": "Seminar created successfully."
}
```

---

#### PUT /api/seminars/{id}
**Update seminar (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Request Body:**
```json
{
  "title_en": "Updated Title",
  "status": "published",
  "starts_at": "2026-04-26T10:00:00Z"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "title_en": "Updated Title",
    "status": "published",
    "updated_at": "2026-04-21T11:00:00Z"
  },
  "message": "Seminar updated successfully."
}
```

---

#### DELETE /api/seminars/{id}
**Delete seminar (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Seminar deleted successfully."
}
```

---

#### POST /api/seminars/{id}/register
**Register for seminar**

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "Somchai Smith",
  "email": "somchai@example.com",
  "phone": "+66 81 234 5678"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "seminar_id": 1,
    "name": "Somchai Smith",
    "email": "somchai@example.com",
    "registered_at": "2026-04-21T10:30:00Z"
  },
  "message": "Successfully registered for the seminar. You will receive a reminder 15 minutes before it starts."
}
```

---

#### DELETE /api/seminars/{id}/register
**Cancel seminar registration**

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Registration cancelled successfully."
}
```

---

#### GET /api/seminars/{id}/recording
**Get seminar recording details for playback**

**Headers:**
```
Authorization: Bearer {token} (required if seminar permission is "registered")
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "seminar_id": 1,
    "title": "How to Succeed in Chinese Universities",
    "video_url": "https://cdn.horizonhr.com/recordings/video1.m3u8",
    "thumbnail_url": "/uploads/seminars/thumb1.jpg",
    "duration_sec": 5400,
    "playback_speeds": ["0.5x", "0.75x", "1x", "1.25x", "1.5x", "1.75x", "2x"],
    "default_speed": "1x",
    "view_count": 1520,
    "created_at": "2026-04-25T12:00:00Z"
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "error": {
    "code": "RESOURCE_NOT_FOUND",
    "message": "No recording available for this seminar."
  }
}
```

---

### 3.12 Universities

#### GET /api/universities
**List universities**

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 20 | Items per page |
| location | string | null | Filter by location |
| program_type | string | null | Filter: vocational, bachelor, master, language |
| search | string | null | Search by name |

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name_en": "Peking University",
      "name_zh_cn": "北京大学",
      "name_th": "มหาวิทยาลัยปีกิง",
      "logo": "/uploads/universities/peking_logo.png",
      "cover_image": "/uploads/universities/peking_cover.jpg",
      "location": "Beijing, China",
      "website": "https://www.pku.edu.cn",
      "program_types": ["bachelor", "master", "phd"],
      "ranking": 15
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 50,
    "total_pages": 3
  }
}
```

---

### 3.13 Admin Statistics

#### GET /api/admin/stats
**Get platform statistics dashboard data (Admin only)**

**Headers:**
```
Authorization: Bearer {token}
Role: admin
```

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| period | string | 30d | Time range: 7d, 30d, 90d, 1y, all |

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "period": "30d",
    "users": {
      "total": 350,
      "students": 280,
      "enterprises": 65,
      "new_this_period": 42
    },
    "resumes": {
      "total": 210,
      "pending": 18,
      "approved": 185,
      "rejected": 7,
      "uploaded_this_period": 34
    },
    "interviews": {
      "total": 95,
      "scheduled": 12,
      "completed": 78,
      "cancelled": 5,
      "this_period": 22
    },
    "seminars": {
      "total": 15,
      "upcoming": 3,
      "live": 0,
      "ended": 12,
      "total_views": 8540,
      "this_period_views": 1230
    },
    "jobs": {
      "total": 48,
      "published": 35,
      "applications_this_period": 67
    }
  }
}
```

---

### 3.14 Contact

#### POST /api/contact
**Submit a contact/consultation form (public)**

No authentication required.

**Request Body:**
```json
{
  "name": "Somchai Smith",
  "email": "somchai@example.com",
  "phone": "+66 81 234 5678",
  "subject": "Study in China Inquiry",
  "message": "I would like to know more about bachelor programs..."
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Sender's full name |
| email | string | Yes | Sender's email |
| phone | string | No | Contact phone number |
| subject | string | No | Message subject |
| message | string | Yes | Message body (min 10 chars) |

**Success Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 42,
    "submitted_at": "2026-04-21T10:30:00Z"
  },
  "message": "Your message has been sent. We will contact you shortly."
}
```

**Note:** Successful submissions trigger an email notification to the admin contact email configured in Settings (`contact_email`).

---

## 4. Error Codes

### 4.1 HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request succeeded |
| 201 | Created - Resource created |
| 204 | No Content - Request succeeded with no response body |
| 400 | Bad Request - Invalid request format |
| 401 | Unauthorized - Authentication required or failed |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource does not exist |
| 409 | Conflict - Duplicate resource |
| 422 | Unprocessable Entity - Validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error |

### 4.2 Application Error Codes

| Code | Description |
|------|-------------|
| VALIDATION_ERROR | Request validation failed |
| AUTH_REQUIRED | Authentication required |
| INVALID_CREDENTIALS | Email or password incorrect |
| TOKEN_EXPIRED | JWT token has expired |
| TOKEN_INVALID | JWT token is invalid |
| INSUFFICIENT_PERMISSIONS | User role does not have permission |
| RESOURCE_NOT_FOUND | Requested resource not found |
| DUPLICATE_RESOURCE | Resource already exists |
| DUPLICATE_APPLICATION | Student already applied to job |
| FILE_TOO_LARGE | Uploaded file exceeds size limit |
| INVALID_FILE_TYPE | File type not allowed |
| RATE_LIMIT_EXCEEDED | Too many requests |
| ACCOUNT_PENDING | Account awaiting email verification or admin approval |
| ACCOUNT_SUSPENDED | Account has been suspended |
| MAINTENANCE_MODE | Site is in maintenance mode |
| INVALID_PROVIDER | Unsupported social authentication provider |
| SMTP_CONNECTION_FAILED | Cannot connect to configured SMTP server |
| CANNOT_DELETE_DEFAULT | Cannot delete the default language |
| INTERVIEW_NOT_JOINABLE | Interview is not currently open for joining |

---

## 5. Common Patterns

### 5.1 File Upload
```javascript
// Using FormData
const formData = new FormData();
formData.append('file', fileInput.files[0]);
formData.append('visibility', 'enterprise_visible');

fetch('/api/resumes', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  },
  body: formData
});
```

### 5.2 Pagination
```javascript
// Request next page
GET /api/resumes?page=2&per_page=20

// Response includes meta
{
  "meta": {
    "current_page": 2,
    "per_page": 20,
    "total": 45,
    "total_pages": 3
  }
}
```

### 5.3 Filtering
```javascript
// Multiple filters
GET /api/resumes?nationality=Thailand&education=bachelor&status=approved

// Search
GET /api/jobs?search=marketing&location=Shanghai
```

### 5.4 Sorting
```javascript
// Sort by multiple fields
GET /api/resumes?sort_by=created_at&sort_order=desc

// Available sort fields vary by endpoint
```

### 5.5 Language Selection
```javascript
// Set Accept-Language header
fetch('/api/posts', {
  headers: {
    'Accept-Language': 'zh_cn'
  }
});

// Or use query parameter
GET /api/posts?language=zh_cn
```

### 5.6 WebSocket for Real-time (Interviews)
```
Connection: wss://api.horizonhr.com/ws/interview/{room_id}?token={room_token}

Events:
- user_joined
- user_left
- offer (WebRTC)
- answer (WebRTC)
- ice_candidate
- chat_message
```

### 5.7 WebSocket for Danmu (Seminar Bullet Comments)
```
Connection: wss://api.horizonhr.com/ws/seminar/{seminar_id}/danmu?token={token}

Events (Client → Server):
- danmu_send: {content: string, color: string, position: string}

Events (Server → Client):
- danmu_received: {id, user_id, user_name, content, color, position, font_size, send_at}
- danmu_history: [{danmu messages...}] // Recent danmu for replay

Rate Limiting:
- Max 10 danmu per user per minute
- Server responds with RATE_LIMIT_EXCEEDED if exceeded

Display Duration:
- Scroll position: 5 seconds
- Top position: 3 seconds  
- Bottom position: 3 seconds
```

### 5.8 Playback Speed Control (Seminar Recordings)
```javascript
// Available playback speeds for seminar recordings
const playbackSpeeds = ['0.5x', '0.75x', '1x', '1.25x', '1.5x', '1.75x', '2x'];

// Example: Get recording with playback speed info
GET /api/seminars/{id}/recording

// Response includes:
{
  "id": 1,
  "title": "How to Succeed in Chinese Universities",
  "video_url": "https://cdn.horizonhr.com/recordings/video1.m3u8",
  "duration_sec": 5400,
  "playback_speeds": ["0.5x", "0.75x", "1x", "1.25x", "1.5x", "1.75x", "2x"],
  "default_speed": "1x",
  "view_count": 1520
}

// Player UI provides speed selector (0.5x to 2x)
// User selection persists in browser localStorage
```

---

**Document ID:** HRINT-DOC-003  
**Version:** 1.1  
**Updated:** 2026-04-21  
**Author:** System Analyst (sa)  

---
