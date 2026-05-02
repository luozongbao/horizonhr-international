# TASK-017: Email Service Integration + All Templates

**Phase:** 5 — External Service Integrations (Backend)  
**Status:** Pending  
**Depends On:** TASK-008  
**Priority:** HIGH  

---

## Objective

Implement the complete email sending infrastructure: a dynamic SMTP service that reads credentials from DB settings at runtime, and all 8 HTML email templates wired to their corresponding queued jobs. After this task, all email notifications (registration, password reset, interview invitations, etc.) should send real HTML emails.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.4 (Settings: SMTP)
2. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.C (Registration Workflow, email confirmation)
3. `DOCUMENTS/DESIGNS/email-templates/` — All 8 HTML email template files
4. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Section 2.3.8 (Settings Module with SMTP)

---

## Email Templates Available

| Template File | Used For | Job Class |
|---------------|----------|-----------|
| `student-verify-email.html` | Student email confirmation | `SendEmailConfirmationJob` |
| `password-reset.html` | Password reset link | `SendPasswordResetJob` |
| `interview-invitation.html` | Interview invite to student | `SendInterviewInvitationJob` |
| `interview-result.html` | Interview result notification | `SendInterviewResultJob` |
| `job-application-received.html` | Notify enterprise of new application | `SendApplicationReceivedJob` |
| `enterprise-pending-notify-admin.html` | Notify admin of new enterprise registration | `SendEnterprisePendingNotifyAdminJob` |
| `enterprise-activated.html` | Notify enterprise their account is approved | `SendEnterpriseActivatedJob` |
| `seminar-reminder.html` | Remind registrant 15 min before seminar | `SendSeminarReminderJob` |

---

## Deliverables

### Dynamic SMTP Service
- `app/Services/EmailService.php`
  ```php
  class EmailService {
      // Build Mailer using current DB settings
      public function getMailer(): Mailer
      
      // Send a Mailable using dynamic SMTP config
      public function send(Mailable $mailable, string $toEmail, string $toName): void
  }
  ```
  
  Logic in `getMailer()`:
  1. Read SMTP settings from DB: `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_username`, `smtp_password`, `smtp_from_address`, `smtp_from_name`
  2. Build a `Swift_SmtpTransport` (or Symfony Mailer) with these settings
  3. Return configured Mailer instance
  4. Cache the mailer config for the request lifecycle (not across requests — settings may change)

### Blade Email Templates
Convert each HTML template file to a Laravel Blade view:
- `resources/views/emails/student-verify-email.blade.php`
- `resources/views/emails/password-reset.blade.php`
- `resources/views/emails/interview-invitation.blade.php`
- `resources/views/emails/interview-result.blade.php`
- `resources/views/emails/job-application-received.blade.php`
- `resources/views/emails/enterprise-pending-notify-admin.blade.php`
- `resources/views/emails/enterprise-activated.blade.php`
- `resources/views/emails/seminar-reminder.blade.php`

Each template uses Blade variables for dynamic content. Preserve the HTML/CSS structure from the original template files in `DOCUMENTS/DESIGNS/email-templates/`.

### Mailable Classes
- `app/Mail/EmailConfirmationMail.php`
  - Variables: `$user` (name, email), `$confirmUrl` (full URL with token)
- `app/Mail/PasswordResetMail.php`
  - Variables: `$user` (name), `$resetUrl`, `$expiresIn` (24 hours)
- `app/Mail/InterviewInvitationMail.php`
  - Variables: `$interview` (title, scheduled_at, duration), `$student` (name), `$enterprise` (company_name), `$joinUrl`
- `app/Mail/InterviewResultMail.php`
  - Variables: `$interview` (title), `$result` (pass/fail/pending), `$notes`, `$student` (name)
- `app/Mail/ApplicationReceivedMail.php`
  - Variables: `$application`, `$student` (name, nationality, bio), `$job` (title), `$enterprise` (company_name, contact_email)
- `app/Mail/EnterprisePendingMail.php`
  - Variables: `$enterprise` (company_name, email), `$adminUrl` (link to admin user management)
- `app/Mail/EnterpriseActivatedMail.php`
  - Variables: `$enterprise` (company_name), `$loginUrl`
- `app/Mail/SeminarReminderMail.php`
  - Variables: `$seminar` (title by language, starts_at, speaker_name), `$registrant` (name, email), `$watchUrl`

### Update Existing Jobs
Update all job classes created in previous tasks (TASK-004, TASK-006, TASK-012, TASK-013, TASK-014) to use `EmailService::send()` with the proper Mailable class instead of plain text emails.

### Queue Config
- All email jobs extend `ShouldQueue` and implement `Queueable`
- Queue: `emails` (dedicated queue for email jobs)
- Max retries: 3, backoff: 30 seconds
- Failed job handling: log to `failed_jobs` table

---

## Email Template Variables

### Template Localization
Email language is determined by the recipient's `prefer_lang` setting:
- If student has `prefer_lang = 'th'`, send email in Thai
- Use multi-language content in Blade templates: `{{ $subject_en }}` or `{{ $subject_th }}` based on `$lang`
- Pass `$lang` variable to each Mailable based on recipient's `prefer_lang`

---

## SMTP Test (connect to TASK-008)

The `POST /api/admin/settings/test-smtp` endpoint (TASK-008) should call `EmailService::getMailer()` and attempt to connect, returning success/failure. Implement this in TASK-008 once `EmailService` is available here.

---

## Acceptance Criteria

- [ ] `EmailService::getMailer()` builds mailer from DB SMTP settings
- [ ] All 8 Blade email templates created with correct variables
- [ ] `SendEmailConfirmationJob` sends HTML email with confirmation link
- [ ] `SendPasswordResetJob` sends HTML email with reset link
- [ ] `SendInterviewInvitationJob` sends HTML email with join link
- [ ] `SendInterviewResultJob` sends result notification email
- [ ] `SendApplicationReceivedJob` sends application notification to enterprise
- [ ] `SendEnterprisePendingNotifyAdminJob` sends pending notification to admin
- [ ] `SendEnterpriseActivatedJob` sends approval email to enterprise
- [ ] `SendSeminarReminderJob` sends reminder email to registrant
- [ ] All jobs run on `emails` queue
- [ ] Failed jobs logged to `failed_jobs` table
- [ ] Email language matches recipient's `prefer_lang` setting
- [ ] SMTP test endpoint returns success with valid SMTP config
- [ ] Queue worker `php artisan queue:work --queue=emails` processes all email jobs

---

## Notes

- Dynamic SMTP: since settings come from DB, the standard Laravel `config/mail.php` approach won't work for runtime config changes. Use `Config::set('mail.*', ...)` before sending, or instantiate a fresh Mailer manually.
- Test all emails with a local SMTP server (Mailpit/MailHog in Docker) before integrating real SMTP
- Add `mailpit` service to `docker-compose.yml`: `mailpit/mailpit:latest` on port 8025 (web UI) and 1025 (SMTP)
- Template HTML: copy structure from `DOCUMENTS/DESIGNS/email-templates/*.html` — these are the approved designs
