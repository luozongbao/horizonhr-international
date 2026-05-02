# TASK-003: Database Schema & All Migrations

**Phase:** 1 — Infrastructure  
**Status:** Pending  
**Depends On:** TASK-002  
**Priority:** HIGH  

---

## Objective

Create all Laravel database migrations for the 22 tables defined in `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` Section 1.2. Include a DatabaseSeeder with essential seed data (default settings, language settings, language translations, and a default admin account). After this task, `php artisan migrate --seed` should produce a fully functional schema ready for the backend API tasks.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.1 (ER Diagram) and 1.2 (all table SQL definitions)
2. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B (user roles), Section VI (tech standards)

---

## Migrations to Create

Create in `backend/database/migrations/` with timestamps in the order listed (respect foreign key dependencies):

| # | Migration File | Table(s) |
|---|----------------|----------|
| 01 | `..._create_users_table.php` | `users` |
| 02 | `..._create_students_table.php` | `students` |
| 03 | `..._create_enterprises_table.php` | `enterprises` |
| 04 | `..._create_admins_table.php` | `admins` |
| 05 | `..._create_languages_table.php` | `languages` (translation keys) |
| 06 | `..._create_language_settings_table.php` | `language_settings` |
| 07 | `..._create_settings_table.php` | `settings` |
| 08 | `..._create_pages_table.php` | `pages` |
| 09 | `..._create_posts_table.php` | `posts` |
| 10 | `..._create_resumes_table.php` | `resumes` |
| 11 | `..._create_talent_cards_table.php` | `talent_cards` |
| 12 | `..._create_jobs_table.php` | `jobs` |
| 13 | `..._create_applications_table.php` | `applications` |
| 14 | `..._create_interviews_table.php` | `interviews` |
| 15 | `..._create_interview_records_table.php` | `interview_records` |
| 16 | `..._create_seminars_table.php` | `seminars` |
| 17 | `..._create_seminar_registrations_table.php` | `seminar_registrations` |
| 18 | `..._create_seminar_recordings_table.php` | `seminar_recordings` |
| 19 | `..._create_seminar_messages_table.php` | `seminar_messages` |
| 20 | `..._create_universities_table.php` | `universities` |
| 21 | `..._create_password_resets_table.php` | `password_resets` |
| 22 | `..._create_email_confirmations_table.php` | `email_confirmations` |
| 23 | `..._create_consent_logs_table.php` | `consent_logs` |
| 24 | `..._create_social_authentications_table.php` | `social_authentications` |
| 25 | `..._create_contacts_table.php` | `contacts` |

> Use the exact SQL column definitions from SYSTEM_DESIGN.md Section 1.2. Translate to Laravel migration `Schema::create()` syntax. Preserve all ENUMs, indexes, and foreign keys exactly as specified.

---

## Eloquent Models to Create

Create in `backend/app/Models/`:

- `User.php` — fillable, hidden (password), casts (email_verified as bool)
- `Student.php` — fillable, belongsTo User
- `Enterprise.php` — fillable, belongsTo User
- `Admin.php` — fillable, belongsTo User
- `Language.php` — fillable (translation key model)
- `LanguageSetting.php` — fillable (active language config)
- `Setting.php` — fillable; add static `get($key, $default)` and `set($key, $value)` helpers
- `Page.php` — fillable
- `Post.php` — fillable, belongsTo Page
- `Resume.php` — fillable, belongsTo Student
- `TalentCard.php` — fillable, belongsTo Student; casts JSON fields
- `Job.php` — fillable, belongsTo Enterprise
- `Application.php` — fillable, belongsTo Job, belongsTo Student
- `Interview.php` — fillable, belongsTo Student, belongsTo Enterprise
- `InterviewRecord.php` — fillable, belongsTo Interview
- `Seminar.php` — fillable
- `SeminarRegistration.php` — fillable, belongsTo Seminar
- `SeminarRecording.php` — fillable, belongsTo Seminar; cast `playback_speeds` as array
- `SeminarMessage.php` — fillable, belongsTo Seminar
- `University.php` — fillable; cast `majors` and `program_types` as array
- `PasswordReset.php` — fillable
- `EmailConfirmation.php` — fillable
- `ConsentLog.php` — fillable
- `SocialAuthentication.php` — fillable, belongsTo User
- `Contact.php` — fillable

---

## Seeders to Create

### `DatabaseSeeder.php`
Calls all sub-seeders in order:
1. `LanguageSettingSeeder`
2. `LanguageSeeder`
3. `SettingSeeder`
4. `AdminSeeder`

### `LanguageSettingSeeder.php`
Insert 3 rows per SYSTEM_DESIGN.md Section 1.2.4.1:
- `en` / English / English / 🇬🇧 / position 1
- `zh_cn` / 中文简体 / 简体中文 / 🇨🇳 / position 2
- `th` / ภาษาไทย / ภาษาไทย / 🇹🇭 / position 3

### `LanguageSeeder.php`
Insert default translation keys per SYSTEM_DESIGN.md Section 1.2.5 INSERT statements.

### `SettingSeeder.php`
Insert all default settings per SYSTEM_DESIGN.md Section 1.2.5 (settings table INSERT statements). Groups: `website`, `seo`, `social`, `smtp`, `system`.

### `AdminSeeder.php`
Create a default admin account:
- email: `admin@horizonhr.com`
- password: `Admin@12345` (hashed with `bcrypt`)
- role: `admin`
- status: `active`
- email_verified: `true`
- Also create corresponding `admins` row with name: `System Admin`

> **Security note:** Print a warning in seeder output that the default admin password must be changed immediately after first login.

---

## Acceptance Criteria

- [ ] `php artisan migrate` runs all 25 migrations without errors
- [ ] `php artisan migrate:rollback --step=25` rolls back all cleanly
- [ ] `php artisan migrate --seed` runs all seeders without errors
- [ ] Database has all 25 tables after migration
- [ ] `language_settings` table has 3 rows (en, zh_cn, th)
- [ ] `settings` table has all default setting rows (at least 40+)
- [ ] `users` table has 1 admin user (`admin@horizonhr.com`)
- [ ] All foreign keys are properly created (verify with `SHOW CREATE TABLE`)
- [ ] All ENUM types match SYSTEM_DESIGN.md exactly
- [ ] All JSON fields cast properly in models
- [ ] `Setting::get('site_name')` returns 'HorizonHR'
- [ ] All models can be instantiated without errors

---

## Notes

- Laravel migrations use `$table->unsignedBigInteger()` for FK columns, not `$table->foreignId()` — match SYSTEM_DESIGN.md column types exactly
- For ENUM columns: `$table->enum('status', ['pending', 'active', ...])`
- The `languages` table (translation keys) conflicts with Laravel's convention — name the model `Language` but the table is `languages` (already correct)
- Password in AdminSeeder must NOT be stored in plain text — use `bcrypt()` or `Hash::make()`
- Remove Laravel's default `users` migration and `User` model if they conflict — replace entirely
