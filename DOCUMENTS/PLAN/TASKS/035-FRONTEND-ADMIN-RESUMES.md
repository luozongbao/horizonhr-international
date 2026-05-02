# TASK-035: Frontend Admin Resume Management

**Phase:** 11 — Frontend: Admin Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-010  
**Priority:** HIGH  

---

## Objective

Implement the admin's resume review and management page: view all submitted resumes, approve or reject with reason, view resume files.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/admin-resumes.html` — Resume management mockup
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.5 (Resumes: admin endpoints)
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.4 (Admin: Resume Management)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/admin/resumes` | List all resumes |
| GET | `/api/admin/resumes/{id}` | Resume detail + download URL |
| PUT | `/api/admin/resumes/{id}/approve` | Approve resume |
| PUT | `/api/admin/resumes/{id}/reject` | Reject resume with reason |

---

## Deliverables

### Admin Resumes Page
**`frontend/src/views/admin/ResumesPage.vue`**

Layout (matches `admin-resumes.html`):
1. **Filter Bar**:
   - Search by student name or email
   - Status filter: All / Pending / Approved / Rejected
   - Date range picker (submitted between)
2. **Resumes Table**:
   - Columns: Student Avatar + Name, Nationality, Education Level, Submission Date, Status Badge, Actions
   - Status badges: Pending (yellow), Approved (green), Rejected (red)
   - Pagination
3. **Resume Actions**:
   - "View" → open resume detail drawer
   - "Approve" button (for pending/rejected resumes)
   - "Reject" button (for pending/approved resumes) → opens reject dialog
4. **Reject Dialog**:
   - Reason textarea (required)
   - Submit → `PUT /api/admin/resumes/{id}/reject`
5. **Resume Detail Drawer**:
   - Student info: avatar, name, nationality, email
   - Resume file: "Open Resume" button → presigned URL in new tab
   - Resume metadata: file name, file size, upload date, current status
   - Quick action buttons: Approve / Reject (in drawer)

---

## i18n Keys to Add

```json
"adminResumes": {
  "pageTitle": "Resume Management",
  "submittedDate": "Submitted",
  "approve": "Approve",
  "reject": "Reject",
  "rejectReason": "Rejection Reason",
  "rejectReasonPlaceholder": "Please provide a clear reason...",
  "approveConfirm": "Approve this resume?",
  "openResume": "Open Resume",
  "studentInfo": "Student Information"
}
```

---

## Acceptance Criteria

- [ ] Resume list fetches from API with pagination
- [ ] Status and date range filters work
- [ ] Search by student name/email works
- [ ] "View" opens detail drawer with student info and resume file link
- [ ] "Approve" calls API and updates status badge in table
- [ ] "Reject" opens dialog requiring reason, then calls API with reason
- [ ] After approve/reject, table row updates without full page reload
- [ ] "Open Resume" opens presigned URL in new tab
- [ ] All text via i18n

---

## Notes

- Admin can approve OR reject from any status (e.g., can re-approve a rejected resume)
- After status update, refetch that single row or update the local array item
- Presigned URL comes from `GET /api/admin/resumes/{id}` response — call this when opening the drawer
