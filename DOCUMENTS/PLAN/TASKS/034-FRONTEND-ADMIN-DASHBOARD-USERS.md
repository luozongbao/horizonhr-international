# TASK-034: Frontend Admin Dashboard & User Management

**Phase:** 11 — Frontend: Admin Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-006, TASK-015  
**Priority:** HIGH  

---

## Objective

Implement the admin portal's Dashboard (statistics overview with charts) and User Management page (list/approve/suspend/delete users across all roles).

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/admin-dashboard.html` — Dashboard mockup
2. `DOCUMENTS/DESIGNS/visual-mockups/admin-users.html` — User management mockup
3. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.6 (Admin Users), Section 3.13 (Statistics)
4. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.1 (Admin: Data Statistics), IV.B.3 (User Management)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/admin/stats?period=30d` | Dashboard statistics |
| GET | `/api/admin/users` | List all users |
| PUT | `/api/admin/users/{id}/status` | Activate / suspend user |
| PUT | `/api/admin/users/{id}/approve-enterprise` | Approve enterprise |
| DELETE | `/api/admin/users/{id}` | Delete user |
| POST | `/api/admin/users` | Create admin user |

---

## Deliverables

### Admin Dashboard
**`frontend/src/views/admin/DashboardPage.vue`**

Layout (matches `admin-dashboard.html`):
1. **Period Selector** — tabs/dropdown: 7d / 30d / 90d / 1y / all
2. **Stats Cards** (2x4 grid):
   - Total Users (with role breakdown)
   - New Users in Period
   - Total Resumes / Pending Review
   - Total Applications
   - Total Interviews
   - Active Job Postings
   - Total Seminars
   - Contact Inquiries
3. **User Growth Chart** — line chart (`daily_trend.new_users`)
4. **Resume/Application Activity Chart** — bar chart (`daily_trend.new_resumes` + `daily_trend.new_interviews`)
5. **Status Breakdown Pie Charts**:
   - Resume status (pending/approved/rejected)
   - Application status
   - Interview status

**Chart Library:** Use `Chart.js` with `vue-chartjs` wrapper:
- `npm install chart.js vue-chartjs`
- Line chart for trends
- Bar chart for activity
- Doughnut/Pie for status breakdowns

Reference: `visual-mockups/admin-dashboard.html`

### Admin User Management
**`frontend/src/views/admin/UsersPage.vue`**

Layout (matches `admin-users.html`):
1. **Filter Bar**:
   - Search (name or email)
   - Role filter: All / Student / Enterprise / Admin
   - Status filter: All / Active / Pending / Suspended
2. **Users Table**:
   - Columns: Avatar, Name, Email, Role Badge, Status Badge, Registered Date, Actions
   - Role badges: Student (blue), Enterprise (orange), Admin (red)
   - Status badges: Active (green), Pending (yellow), Suspended (gray)
   - Actions:
     - For enterprise with status=pending: "Approve" button
     - For active users: "Suspend" button
     - For suspended users: "Activate" button
     - "Delete" button (with confirmation)
     - "View Profile" button
3. **Create Admin User Dialog**:
   - Name, Email, Password
   - Role: admin (only)
   - Submit → `POST /api/admin/users`

### User Detail Drawer
On "View Profile" click:
- Side drawer panel showing full user info
- For students: profile, resume status, application count
- For enterprises: company info, job posting count, approval status
- Action buttons: Approve / Suspend / Activate / Delete

---

## API Module
**`frontend/src/api/admin.js`**
```js
export const adminApi = {
  getStats(period),
  getUsers(params),
  updateUserStatus(id, status),
  approveEnterprise(id),
  deleteUser(id),
  createAdmin(data),
}
```

---

## i18n Keys to Add

```json
"admin": {
  "dashboard": {
    "title": "Admin Dashboard",
    "period": {
      "7d": "Last 7 days",
      "30d": "Last 30 days",
      "90d": "Last 90 days",
      "1y": "Last year",
      "all": "All time"
    },
    "stats": {
      "totalUsers": "Total Users",
      "newUsers": "New Users",
      "pendingResumes": "Pending Resumes",
      "totalApplications": "Applications",
      "interviews": "Interviews",
      "activeJobs": "Active Jobs",
      "seminars": "Seminars",
      "contacts": "Inquiries"
    }
  },
  "users": {
    "pageTitle": "User Management",
    "createAdmin": "Create Admin",
    "approve": "Approve",
    "suspend": "Suspend",
    "activate": "Activate",
    "deleteUser": "Delete User",
    "deleteConfirm": "Are you sure you want to delete this user? This cannot be undone.",
    "approveConfirm": "Approve this enterprise account?"
  }
}
```

---

## Acceptance Criteria

- [ ] Dashboard fetches stats and displays all 8 stat cards correctly
- [ ] Period selector updates all charts and cards on change
- [ ] Line chart renders user growth trend
- [ ] Bar chart renders activity trend
- [ ] Pie/doughnut charts show resume/interview/application status breakdowns
- [ ] User list loads with search and filter working
- [ ] Enterprise pending users show "Approve" button
- [ ] Approve action calls API and updates status in table
- [ ] Suspend/Activate toggles user status correctly
- [ ] Delete user shows confirmation dialog, calls API, removes from table
- [ ] "Create Admin" dialog creates new admin user
- [ ] All text via i18n

---

## Notes

- `chart.js` + `vue-chartjs`: `npm install chart.js vue-chartjs`
- Charts should show empty/zero state when data is insufficient (no errors)
- User deletion: admin cannot delete themselves — check `authStore.user.id !== targetUser.id` before showing delete button
- Chart colors: use brand colors (`#003366`, `#E6F0FF`) as primary chart palette
