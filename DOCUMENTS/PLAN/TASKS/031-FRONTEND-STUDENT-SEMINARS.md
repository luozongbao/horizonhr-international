# TASK-031: Frontend Student Seminars Portal

**Phase:** 9 — Frontend: Student Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-027  
**Priority:** MEDIUM  

---

## Objective

Implement the student's seminar portal section: browsing available seminars, registering/unregistering, viewing registered seminars, and accessing live/recorded streams. This task reuses the public seminar components from TASK-027 within the student portal layout.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/student-seminars.html` — Student seminars mockup
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.9 (Seminars)
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.C.4 (Student: Attend Seminars)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/seminars` | Browse all seminars |
| POST | `/api/seminars/{id}/register` | Register for seminar |
| DELETE | `/api/seminars/{id}/unregister` | Cancel registration |
| GET | `/api/seminar-registrations/my` | My registered seminars |
| GET | `/api/seminars/{id}/watch` | Get stream URL |

---

## Deliverables

### Student Seminars Page
**`frontend/src/views/student/SeminarsPage.vue`**

Two tabs:
1. **Browse Seminars** — reuse `SeminarListPage` logic within student layout
   - Shows all seminars with register/watch buttons
   - "Registered" badge on cards the student has registered for
2. **My Registrations** — list of seminars student registered for
   - Columns: title, date/time, status, actions (Watch / Cancel)

Reference: `visual-mockups/student-seminars.html`

### Watch Page (Student)
- The same `SeminarWatchPage.vue` from TASK-027 is reused
- Student portal navigates to `/seminars/{id}/watch` which is the public route
- No duplicate implementation needed

---

## i18n Keys to Add

```json
"studentSeminars": {
  "browse": "Browse Seminars",
  "myRegistrations": "My Registrations",
  "noRegistrations": "You haven't registered for any seminars yet."
}
```

---

## Acceptance Criteria

- [ ] Browse tab shows seminar list with correct registration status per seminar
- [ ] "Register" button registers student and updates badge to "Registered"
- [ ] "My Registrations" tab shows only registered seminars
- [ ] "Watch" button navigates to seminar watch page
- [ ] "Cancel" button unregisters student (with confirmation)
- [ ] All text via i18n

---

## Notes

- Minimal new code — this task mostly composes existing components from TASK-027 into the student layout
- Registration state: track in component state; update optimistically on register/unregister clicks
