# TASK-036: Frontend Admin Interviews & Seminars Management

**Phase:** 11 — Frontend: Admin Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-013, TASK-014, TASK-019  
**Priority:** HIGH  

---

## Objective

Implement admin management for interviews (overview/monitoring) and seminars (full CRUD with live streaming controls).

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/admin-interviews.html` — Interviews management mockup
2. `DOCUMENTS/DESIGNS/visual-mockups/admin-seminars.html` — Seminars management mockup
3. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.10 (Interviews admin), Section 3.11 (Seminars admin)
4. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.5 (Admin: Interviews), IV.B.6 (Admin: Seminars)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/admin/interviews` | List all interviews |
| PUT | `/api/admin/interviews/{id}/cancel` | Cancel interview (admin) |
| GET | `/api/admin/seminars` | List all seminars |
| POST | `/api/admin/seminars` | Create seminar |
| PUT | `/api/admin/seminars/{id}` | Update seminar |
| DELETE | `/api/admin/seminars/{id}` | Delete seminar |
| GET | `/api/admin/seminars/{id}/live-urls` | Get push/pull URLs |

---

## Deliverables

### Admin Interviews Page
**`frontend/src/views/admin/InterviewsPage.vue`**

Layout (matches `admin-interviews.html`):
1. **Filter Bar**: search, status filter, date range
2. **Interviews Table**:
   - Columns: Student, Enterprise, Job, Scheduled Time, Duration, Status, Actions
   - Status badges: Scheduled / Ongoing / Completed / Cancelled
   - Actions: "View Details", "Cancel" (for scheduled/ongoing)
3. **Interview Detail Modal**:
   - All interview info including TRTC room ID
   - Participants list
   - Result (if completed)

> Admin does NOT join interview rooms — admin only monitors and can cancel.

### Admin Seminars Page
**`frontend/src/views/admin/SeminarsPage.vue`**

Layout (matches `admin-seminars.html`):
1. **"Create Seminar" button**
2. **Status Tabs**: All / Scheduled / Live / Ended
3. **Seminars Table**:
   - Columns: Thumbnail, Title, Speaker, Scheduled Date, Status, Registered Count, Actions
   - Actions:
     - "Edit"
     - "Live Controls" (for scheduled/live)
     - "Delete" (for scheduled only)
4. **Create/Edit Seminar Form (`el-dialog`)**:
   - Title (multi-language tabs: EN/ZH_CN/TH)
   - Description (multi-language, textarea)
   - Speaker Name, Speaker Title, Speaker Bio, Speaker Photo (upload)
   - Thumbnail (upload, recommended 16:9)
   - Scheduled Date/Time
   - Duration (minutes)
   - Language (EN/ZH_CN/TH)
   - Max Registrations (0 = unlimited)
   - Save
5. **Live Controls Panel (drawer or modal)**:
   - Shows OBS push URL (copyable)
   - Shows HLS pull URL (copyable, for testing in player)
   - "Refresh URLs" button (regenerates signed URLs)
   - Current stream status (from seminar.status)
   - View Recording button (if recording available)
   - Copy instructions for OBS setup

---

## API Module Additions (to `admin.js`)
```js
export const adminApi = {
  // Interviews
  getInterviews(params),
  cancelInterview(id),
  
  // Seminars
  getSeminars(params),
  createSeminar(data),
  updateSeminar(id, data),
  deleteSeminar(id),
  getSeminarLiveUrls(id),
}
```

---

## i18n Keys to Add

```json
"adminInterviews": {
  "pageTitle": "Interview Management",
  "cancelInterview": "Cancel Interview",
  "cancelConfirm": "Cancel this interview? Participants will be notified."
},
"adminSeminars": {
  "pageTitle": "Seminar Management",
  "createSeminar": "Create Seminar",
  "liveControls": "Live Controls",
  "pushUrl": "OBS Push URL",
  "pullUrl": "HLS Pull URL (Viewer)",
  "copyUrl": "Copy URL",
  "urlCopied": "URL copied to clipboard",
  "obsInstructions": "Paste the Push URL into OBS Studio under Settings > Stream > Custom",
  "viewRecording": "View Recording",
  "scheduledAt": "Scheduled Date & Time",
  "maxRegistrations": "Max Registrations (0 = unlimited)"
}
```

---

## Acceptance Criteria

- [ ] Interviews list shows all interviews with search/filter working
- [ ] Admin can cancel interview from list
- [ ] Interview detail modal shows full info
- [ ] Seminars list shows all seminars with status tabs
- [ ] Create seminar form validates required fields
- [ ] Multi-language title/description tabs work correctly
- [ ] Speaker photo upload works
- [ ] Thumbnail upload works
- [ ] Seminar saved and appears in list
- [ ] "Live Controls" drawer shows push URL and pull URL
- [ ] URLs are copyable with one click
- [ ] "Refresh URLs" generates new signed URLs
- [ ] Recording URL shown when seminar ended with recording
- [ ] Delete seminar shows confirmation dialog
- [ ] All text via i18n

---

## Notes

- OBS push URL format: `rtmp://{push_domain}/live/{stream_key}?auth_key={signed_params}` — display exactly as returned by API
- Security: push URL contains signed auth — never show in browser logs; display in a password-type input with reveal toggle
- Seminar deletion: only allowed for `scheduled` seminars (not `live` or `ended`)
- Speaker photo upload: same as avatar upload — accept JPG/PNG, resize to 400x400, store in OSS
